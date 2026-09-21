#!/usr/bin/env bash
#
# patch-whatsapp.sh — Re-apply custom patches to kstmostofa/laravel-whatsapp
#                      after composer/npm install.
#
# Patches:
#   1. sidecar/index.js — webVersion pin + local cache + crash-loop protection
#   2. whatsapp-web.js Utils.js — getContactModel try-catch wrapper
#   3. Download pinned WA Web HTML to local cache
#
# Usage:
#   chmod +x patch-whatsapp.sh && ./patch-whatsapp.sh
#
# Run after:
#   composer install && cd vendor/kstmostofa/laravel-whatsapp/sidecar && npm install
#

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SIDECAR_DIR="$SCRIPT_DIR/vendor/kstmostofa/laravel-whatsapp/sidecar"
SIDECAR_INDEX="$SIDECAR_DIR/index.js"
UTILS_JS="$SIDECAR_DIR/node_modules/whatsapp-web.js/src/util/Injected/Utils.js"
CACHE_DIR="$SIDECAR_DIR/sessions/.wwebjs_cache"
PINNED_VERSION="2.3000.1045340097-alpha"
CACHE_URL="https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/${PINNED_VERSION}.html"

patched=0
skipped=0

# Colors
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'
GRAY='\033[0;90m'; RED='\033[0;31m'; NC='\033[0m'

ok()   { echo -e "  ${GREEN}+ $1${NC}"; ((patched++)); }
skip() { echo -e "  ${GRAY}  $1${NC}"; ((skipped++)); }
warn() { echo -e "  ${YELLOW}! $1${NC}"; }
fail() { echo -e "  ${RED}! $1${NC}"; }

# Check files exist
for f in "$SIDECAR_INDEX" "$UTILS_JS"; do
  [[ -f "$f" ]] || { fail "Not found: $f"; exit 1; }
done

# ═════════════════════════════════════════════════════════════════════════════
# Patch 1: sidecar/index.js
# ═════════════════════════════════════════════════════════════════════════════
echo -e "\n${CYAN}[1/3] Patching sidecar/index.js ...${NC}"

tmpfile=$(mktemp)

# Use awk for robust multiline patching
awk -v pinned="$PINNED_VERSION" '
BEGIN { inited=0; autostart=0; crashloop=0; webversion=0; initmarker=0; autostartmarker=0 }

# 1. Insert crash-loop protection block before bootSession function
/^async function bootSession\(/ && !crashloop {
    print "/**"
    print " * Crash-loop protection: track failures per session."
    print " * CRASH_LOOP_PROTECTION_MARKER"
    print " */"
    print "const bootFailures = {};"
    print "const MAX_BOOT_FAILURES = 2;"
    print ""
    print "function destroySessionFiles(sessionId) {"
    print "  const authDir = path.join(SESSION_DIR, \`session-\${sessionId}\`);"
    print "  if (fs.existsSync(authDir)) {"
    print "    try {"
    print "      fs.rmSync(authDir, { recursive: true, force: true });"
    print "      console.log(\`[laravel-wa-sidecar] destroyed corrupted session files for \${sessionId}\`);"
    print "    } catch (e) {"
    print "      console.error(\`[laravel-wa-sidecar] failed to destroy session files for \${sessionId}: \${e.message}\`);"
    print "    }"
    print "  }"
    print "}"
    print ""
    crashloop=1
}

# 2. Replace Client constructor with webVersion pin
/authStrategy: new LocalAuth.*dataPath: SESSION_DIR/ && !webversion {
    print "  const client = new Client({"
    print "    authStrategy: new LocalAuth({ clientId: sessionId, dataPath: SESSION_DIR }),"
    print "    webVersion: \x27" pinned "\x27,"
    print "    webVersionCache: {"
    print "      type: \x27local\x27,"
    print "      path: path.join(__dirname, \x27sessions\x27, \x27.wwebjs_cache\x27),"
    print "      strict: true,"
    print "    },"
    # Skip lines until we find the closing of the old constructor
    skip=1
    webversion=1
    next
}
skip && /}\);/ {
    skip=0
    next
}
skip { next }

# 3. Patch initialize().catch with crash-loop handler
/client\.initialize\(\)\.catch/ && !initmarker {
    print "  client.initialize().catch((e) => {"
    print "    session.status = \x27error\x27;"
    print "    broadcast(sessionId, \x27error\x27, { message: e.message });"
    print "    // CRASH_LOOP_INIT_MARKER"
    print "    bootFailures[sessionId] = (bootFailures[sessionId] || 0) + 1;"
    print "    console.error(\`[laravel-wa-sidecar] session \${sessionId} boot failed (\${bootFailures[sessionId]}/\${MAX_BOOT_FAILURES}): \${e.message}\`);"
    print "    if (bootFailures[sessionId] >= MAX_BOOT_FAILURES) {"
    print "      console.error(\`[laravel-wa-sidecar] session \${sessionId} exceeded max boot failures — destroying session files\`);"
    print "      try { client.destroy(); } catch (_) {}"
    print "      sessions.delete(sessionId);"
    print "      destroySessionFiles(sessionId);"
    print "      bootFailures[sessionId] = 0;"
    print "    }"
    print "  });"
    # Skip old catch block lines
    skipinit=1
    initmarker=1
    next
}
skipinit && /^\s*\}\);$/ { skipinit=0; next }
skipinit { next }

# 4. Patch autoStartPersistedSessions with crash-loop guard
/^async function autoStartPersistedSessions\(\)/ && !autostartmarker {
    print "async function autoStartPersistedSessions() {"
    print "  if (!AUTO_START_SESSIONS) return;"
    print "  // CRASH_LOOP_AUTOSTART_MARKER"
    autostartmarker=1
    next
}

{ print }
' "$SIDECAR_INDEX" > "$tmpfile"

mv "$tmpfile" "$SIDECAR_INDEX"

# Check what was patched
grep -q "CRASH_LOOP_PROTECTION_MARKER" "$SIDECAR_INDEX" && ok "Crash-loop protection added." || skip "Crash-loop protection present."
grep -q "webVersion: '" "$SIDECAR_INDEX" && ok "webVersion pin + local cache added." || skip "webVersion pin present."
grep -q "CRASH_LOOP_INIT_MARKER" "$SIDECAR_INDEX" && ok "Crash-loop handler added to initialize()." || skip "Crash-loop init handler present."
grep -q "CRASH_LOOP_AUTOSTART_MARKER" "$SIDECAR_INDEX" && ok "Crash-loop autostart guard added." || skip "Crash-loop autostart present."


# ═════════════════════════════════════════════════════════════════════════════
# Patch 2: whatsapp-web.js Utils.js — getContactModel
# ═════════════════════════════════════════════════════════════════════════════
echo -e "\n${CYAN}[2/3] Patching whatsapp-web.js Utils.js ...${NC}"

if grep -q "CONTACT_MODEL_PATCH_MARKER" "$UTILS_JS"; then
  skip "Already patched."
else
  tmpfile2=$(mktemp)
  awk '
  /window\.WWebJS\.getContactModel = \(contact\) =>/ && !done {
      print "    window.WWebJS.getContactModel = (contact) => {"
      print "        // CONTACT_MODEL_PATCH_MARKER"
      print "        let res = contact.serialize();"
      print ""
      print "        // Patched: guard against undefined contact.id"
      print "        let wid = null;"
      print "        try {"
      print "            wid = window"
      print "                .require(\x27WAWebWidFactory\x27)"
      print "                .createWidFromWidLike(contact.id);"
      print "            if (wid.isLid() && contact.phoneNumber) {"
      print "                res.id = contact.phoneNumber;"
      print "            }"
      print "        } catch (_) {"
      print "            if (contact.phoneNumber) {"
      print "                res.id = contact.phoneNumber;"
      print "            }"
      print "        }"
      print ""
      print "        res.isBusiness ="
      print "            contact.isBusiness === undefined ? false : contact.isBusiness;"
      print ""
      print "        if (contact.businessProfile) {"
      print "            try { res.businessProfile = contact.businessProfile.serialize(); } catch (_) {}"
      print "        }"
      print ""
      print "        res.isBlocked = contact.isContactBlocked;"
      print "        if (!res.isBlocked && wid) {"
      print "            try {"
      print "                const alt = window"
      print "                    .require(\x27WAWebApiContact\x27)"
      print "                    .getAlternateUserWid(wid);"
      print "                if (alt) {"
      print "                    res.isBlocked = !!window"
      print "                        .require(\x27WAWebCollections\x27)"
      print "                        .Blocklist.get(alt);"
      print "                }"
      print "            } catch (_) {}"
      print "        }"
      print ""
      print "        // Patched: wrap ContactMethods getters in try-catch"
      print "        try {"
      print "            const ContactMethods = window.require(\x27WAWebContactGetters\x27);"
      print "            res.isMe = ContactMethods.getIsMe(contact);"
      print "            res.isUser = ContactMethods.getIsUser(contact);"
      print "            res.isGroup = ContactMethods.getIsGroup(contact);"
      print "            res.isWAContact = ContactMethods.getIsWAContact(contact);"
      print "            res.userid = ContactMethods.getUserid(contact);"
      print "            res.verifiedName = ContactMethods.getVerifiedName(contact);"
      print "            res.verifiedLevel = ContactMethods.getVerifiedLevel(contact);"
      print "            res.statusMute = ContactMethods.getStatusMute(contact);"
      print "            res.name = ContactMethods.getName(contact);"
      print "            res.shortName = ContactMethods.getShortName(contact);"
      print "            res.pushname = ContactMethods.getPushname(contact);"
      print ""
      print "            const { getIsMyContact } = window.require("
      print "                \x27WAWebFrontendContactGetters\x27,"
      print "            );"
      print "            res.isMyContact = getIsMyContact(contact);"
      print "            res.isEnterprise = ContactMethods.getIsEnterprise(contact);"
      print "        } catch (_) {"
      print "            res.isMe = false;"
      print "            res.isUser = true;"
      print "            res.isGroup = false;"
      print "            res.isWAContact = true;"
      print "            res.isMyContact = false;"
      print "            res.isEnterprise = false;"
      print "        }"
      print ""
      print "        return res;"
      print "    };"
      # Skip old function until closing };
      skip=1
      done=1
      next
  }
  skip && /^    \};$/ { skip=0; next }
  skip { next }
  { print }
  ' "$UTILS_JS" > "$tmpfile2"

  mv "$tmpfile2" "$UTILS_JS"
  ok "getContactModel patched with try-catch wrapper."
fi


# ═════════════════════════════════════════════════════════════════════════════
# Patch 3: Download pinned WA Web HTML
# ═════════════════════════════════════════════════════════════════════════════
echo -e "\n${CYAN}[3/3] Downloading pinned WA Web HTML ($PINNED_VERSION) ...${NC}"

mkdir -p "$CACHE_DIR"
html_file="$CACHE_DIR/${PINNED_VERSION}.html"

if [[ -f "$html_file" ]] && [[ $(wc -c < "$html_file") -gt 10000 ]]; then
  skip "Already downloaded ($(wc -c < "$html_file") bytes)."
else
  http_code="000"
  if command -v curl &>/dev/null; then
    http_code=$(curl -sL -o "$html_file" -w "%{http_code}" --connect-timeout 30 "$CACHE_URL")
  elif command -v wget &>/dev/null; then
    wget -q -O "$html_file" --timeout=30 "$CACHE_URL" 2>/dev/null && http_code=200
  else
    fail "Neither curl nor wget found."
  fi

  if [[ "$http_code" == "200" ]] && [[ -f "$html_file" ]]; then
    ok "Downloaded ($(wc -c < "$html_file") bytes)."
  else
    fail "Download failed: HTTP $http_code"
  fi
fi


# ═════════════════════════════════════════════════════════════════════════════
# Summary
# ═════════════════════════════════════════════════════════════════════════════
echo ""
echo "══════════════════════════════════════"
if [[ $patched -gt 0 ]]; then
  echo -e "  ${GREEN}Patched: $patched${NC}  |  Skipped: $skipped"
else
  echo -e "  ${GRAY}Patched: $patched${NC}  |  Skipped: $skipped"
fi
echo "══════════════════════════════════════"
echo ""

if [[ $patched -gt 0 ]]; then
  echo "Next steps:"
  echo "  1. Kill sidecar:    kill \$(cat storage/app/whatsapp-sidecar/sidecar.pid 2>/dev/null)"
  echo "  2. Start sidecar:   php artisan whatsapp:sidecar:start"
  echo "  3. Connect WA:      php artisan whatsapp:web:listen"
fi
