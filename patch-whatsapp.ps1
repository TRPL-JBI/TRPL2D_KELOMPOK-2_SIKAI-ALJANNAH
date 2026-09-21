<#
.SYNOPSIS
    Re-apply custom patches to kstmostofa/laravel-whatsapp sidecar after composer/npm install.
.DESCRIPTION
    Patches:
    1. sidecar/index.js - webVersion pin + local cache + crash-loop protection
    2. whatsapp-web.js Utils.js - getContactModel try-catch wrapper
    3. Download pinned WA Web HTML to local cache
.NOTES
    Run after: composer install && cd vendor/kstmostofa/laravel-whatsapp/sidecar && npm install
#>

$ErrorActionPreference = "Stop"

$SIDECAR_DIR = "D:\Project\Al-Jannah\vendor\kstmostofa\laravel-whatsapp\sidecar"
$SIDECAR_INDEX = "$SIDECAR_DIR\index.js"
$UTILS_JS = "$SIDECAR_DIR\node_modules\whatsapp-web.js\src\util\Injected\Utils.js"
$CACHE_DIR = "$SIDECAR_DIR\sessions\.wwebjs_cache"
$PINNED_VERSION = "2.3000.1045340097-alpha"
$CACHE_URL = "https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/$PINNED_VERSION.html"

$patched = 0
$skipped = 0

# ─── Patch 1: sidecar/index.js ───────────────────────────────────────────────
Write-Host "`n[1/3] Patching sidecar/index.js ..." -ForegroundColor Cyan

$content = Get-Content $SIDECAR_INDEX -Raw
$patchedBeforeIndex = $patched

# Check if already patched (marker: CRASH_LOOP_PROTECTION_MARKER)
if ($content -match "CRASH_LOOP_PROTECTION_MARKER") {
    Write-Host "  Already patched (crash-loop protection present)." -ForegroundColor DarkGray
    $skipped++
} else {
    # Insert crash-loop protection before bootSession function
    $crashLoopBlock = @'
/**
 * Crash-loop protection: track failures per session.
 * CRASH_LOOP_PROTECTION_MARKER
 */
const bootFailures = {};
const MAX_BOOT_FAILURES = 2;

function destroySessionFiles(sessionId) {
  const authDir = path.join(SESSION_DIR, `session-${sessionId}`);
  if (fs.existsSync(authDir)) {
    try {
      fs.rmSync(authDir, { recursive: true, force: true });
      console.log(`[laravel-wa-sidecar] destroyed corrupted session files for ${sessionId}`);
    } catch (e) {
      console.error(`[laravel-wa-sidecar] failed to destroy session files for ${sessionId}: ${e.message}`);
    }
  }
}

'@

    $content = $content -replace '(?s)(async function bootSession\(sessionId\) \{.*?if \(existing\) return existing;)', "$crashLoopBlock`$1"
    Write-Host "  + Crash-loop protection added." -ForegroundColor Green
    $patched++
}

# Check if webVersion is already set
if ($content -match "webVersion: '$PINNED_VERSION'") {
    Write-Host "  Already patched (webVersion pin present)." -ForegroundColor DarkGray
    $skipped++
} else {
    # Replace the Client constructor to add webVersion + webVersionCache
    $oldClient = @'
  const client = new Client({
    authStrategy: new LocalAuth({ clientId: sessionId, dataPath: SESSION_DIR }),
    puppeteer: {
      headless: true,
      executablePath,
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
    },
  });
'@

    $newClient = @"
  const client = new Client({
    authStrategy: new LocalAuth({ clientId: sessionId, dataPath: SESSION_DIR }),
    webVersion: '$PINNED_VERSION',
    webVersionCache: {
      type: 'local',
      path: path.join(__dirname, 'sessions', '.wwebjs_cache'),
      strict: true,
    },
    puppeteer: {
      headless: true,
      executablePath,
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
    },
  });
"@

    if ($content -match [regex]::Escape($oldClient)) {
        $content = $content.Replace($oldClient, $newClient)
        Write-Host "  + webVersion pin + local cache added." -ForegroundColor Green
        $patched++
    } else {
        Write-Host "  ! Could not find original Client constructor. Manual check needed." -ForegroundColor Yellow
    }
}

# Add crash-loop handler to initialize().catch if not present
if ($content -match "CRASH_LOOP_INIT_MARKER") {
    Write-Host "  Already patched (crash-loop init handler present)." -ForegroundColor DarkGray
    $skipped++
} else {
    $oldCatch = @'
  client.initialize().catch((e) => {
    session.status = 'error';
    broadcast(sessionId, 'error', { message: e.message });
  });
'@

    $newCatch = @'
  client.initialize().catch((e) => {
    session.status = 'error';
    broadcast(sessionId, 'error', { message: e.message });
    // CRASH_LOOP_INIT_MARKER
    bootFailures[sessionId] = (bootFailures[sessionId] || 0) + 1;
    console.error(`[laravel-wa-sidecar] session ${sessionId} boot failed (${bootFailures[sessionId]}/${MAX_BOOT_FAILURES}): ${e.message}`);
    if (bootFailures[sessionId] >= MAX_BOOT_FAILURES) {
      console.error(`[laravel-wa-sidecar] session ${sessionId} exceeded max boot failures — destroying session files`);
      try { client.destroy(); } catch (_) {}
      sessions.delete(sessionId);
      destroySessionFiles(sessionId);
      bootFailures[sessionId] = 0;
    }
  });
'@

    if ($content -match [regex]::Escape($oldCatch)) {
        $content = $content.Replace($oldCatch, $newCatch)
        Write-Host "  + Crash-loop handler added to initialize()." -ForegroundColor Green
        $patched++
    } else {
        Write-Host "  ! Could not find initialize().catch block. Manual check needed." -ForegroundColor Yellow
    }
}

# Patch autoStartPersistedSessions to skip failing sessions
if ($content -match "CRASH_LOOP_AUTOSTART_MARKER") {
    Write-Host "  Already patched (crash-loop autostart present)." -ForegroundColor DarkGray
    $skipped++
} else {
    $oldAutoStart = @'
async function autoStartPersistedSessions() {
  if (!AUTO_START_SESSIONS) return;

  for (const sessionId of discoverPersistedSessions(SESSION_DIR)) {
    try {
      await bootSession(sessionId);
      console.log(`[laravel-wa-sidecar] auto-started persisted session ${sessionId}`);
    } catch (e) {
      console.error(`[laravel-wa-sidecar] failed to auto-start persisted session ${sessionId}: ${e.message}`);
    }
  }
}
'@

    $newAutoStart = @'
async function autoStartPersistedSessions() {
  if (!AUTO_START_SESSIONS) return;
  // CRASH_LOOP_AUTOSTART_MARKER
  for (const sessionId of discoverPersistedSessions(SESSION_DIR)) {
    if ((bootFailures[sessionId] || 0) >= MAX_BOOT_FAILURES) {
      console.log(`[laravel-wa-sidecar] skipping session ${sessionId} (too many boot failures, destroying)`);
      destroySessionFiles(sessionId);
      bootFailures[sessionId] = 0;
      continue;
    }
    try {
      await bootSession(sessionId);
      console.log(`[laravel-wa-sidecar] auto-started persisted session ${sessionId}`);
    } catch (e) {
      console.error(`[laravel-wa-sidecar] failed to auto-start persisted session ${sessionId}: ${e.message}`);
    }
  }
}
'@

    if ($content -match [regex]::Escape($oldAutoStart)) {
        $content = $content.Replace($oldAutoStart, $newAutoStart)
        Write-Host "  + Crash-loop autostart guard added." -ForegroundColor Green
        $patched++
    } else {
        Write-Host "  ! Could not find autoStartPersistedSessions. Manual check needed." -ForegroundColor Yellow
    }
}

# Write patched content only if something changed
if ($patched -gt $patchedBeforeIndex) {
    Set-Content -Path $SIDECAR_INDEX -Value $content -NoNewline
    Write-Host "  Saved $SIDECAR_INDEX" -ForegroundColor DarkGreen
} else {
    Write-Host "  No changes needed for $SIDECAR_INDEX" -ForegroundColor DarkGray
}


# ─── Patch 2: whatsapp-web.js Utils.js ──────────────────────────────────────
Write-Host "`n[2/3] Patching whatsapp-web.js Utils.js ..." -ForegroundColor Cyan

$utilsContent = Get-Content $UTILS_JS -Raw

if ($utilsContent -match "CONTACT_MODEL_PATCH_MARKER") {
    Write-Host "  Already patched." -ForegroundColor DarkGray
    $skipped++
} else {
    # Find and replace the getContactModel function
    $oldGetContactModel = @'
    window.WWebJS.getContactModel = (contact) => {
        let res = contact.serialize();

        const wid = window
            .require('WAWebWidFactory')
            .createWidFromWidLike(contact.id);
        if (wid.isLid() && contact.phoneNumber) {
            res.id = contact.phoneNumber;
        }

        res.isBusiness =
            contact.isBusiness === undefined ? false : contact.isBusiness;

        if (contact.businessProfile) {
            res.businessProfile = contact.businessProfile.serialize();
        }

        res.isBlocked = contact.isContactBlocked;
        if (!res.isBlocked) {
            const alt = window
                .require('WAWebApiContact')
                .getAlternateUserWid(wid);
            if (alt) {
                res.isBlocked = !!window
                    .require('WAWebCollections')
                    .Blocklist.get(alt);
            }
        }

        const ContactMethods = window.require('WAWebContactGetters');
        res.isMe = ContactMethods.getIsMe(contact);
        res.isUser = ContactMethods.getIsUser(contact);
        res.isGroup = ContactMethods.getIsGroup(contact);
        res.isWAContact = ContactMethods.getIsWAContact(contact);
        res.userid = ContactMethods.getUserid(contact);
        res.verifiedName = ContactMethods.getVerifiedName(contact);
        res.verifiedLevel = ContactMethods.getVerifiedLevel(contact);
        res.statusMute = ContactMethods.getStatusMute(contact);
        res.name = ContactMethods.getName(contact);
        res.shortName = ContactMethods.getShortName(contact);
        res.pushname = ContactMethods.getPushname(contact);

        const { getIsMyContact } = window.require(
            'WAWebFrontendContactGetters',
        );
        res.isMyContact = getIsMyContact(contact);
        res.isEnterprise = ContactMethods.getIsEnterprise(contact);

        return res;
    };
'@

    $newGetContactModel = @'
    window.WWebJS.getContactModel = (contact) => {
        // CONTACT_MODEL_PATCH_MARKER
        let res = contact.serialize();

        // Patched: guard against undefined contact.id
        let wid = null;
        try {
            wid = window
                .require('WAWebWidFactory')
                .createWidFromWidLike(contact.id);
            if (wid.isLid() && contact.phoneNumber) {
                res.id = contact.phoneNumber;
            }
        } catch (_) {
            if (contact.phoneNumber) {
                res.id = contact.phoneNumber;
            }
        }

        res.isBusiness =
            contact.isBusiness === undefined ? false : contact.isBusiness;

        if (contact.businessProfile) {
            try { res.businessProfile = contact.businessProfile.serialize(); } catch (_) {}
        }

        res.isBlocked = contact.isContactBlocked;
        if (!res.isBlocked && wid) {
            try {
                const alt = window
                    .require('WAWebApiContact')
                    .getAlternateUserWid(wid);
                if (alt) {
                    res.isBlocked = !!window
                        .require('WAWebCollections')
                        .Blocklist.get(alt);
                }
            } catch (_) {}
        }

        // Patched: wrap ContactMethods getters in try-catch
        try {
            const ContactMethods = window.require('WAWebContactGetters');
            res.isMe = ContactMethods.getIsMe(contact);
            res.isUser = ContactMethods.getIsUser(contact);
            res.isGroup = ContactMethods.getIsGroup(contact);
            res.isWAContact = ContactMethods.getIsWAContact(contact);
            res.userid = ContactMethods.getUserid(contact);
            res.verifiedName = ContactMethods.getVerifiedName(contact);
            res.verifiedLevel = ContactMethods.getVerifiedLevel(contact);
            res.statusMute = ContactMethods.getStatusMute(contact);
            res.name = ContactMethods.getName(contact);
            res.shortName = ContactMethods.getShortName(contact);
            res.pushname = ContactMethods.getPushname(contact);

            const { getIsMyContact } = window.require(
                'WAWebFrontendContactGetters',
            );
            res.isMyContact = getIsMyContact(contact);
            res.isEnterprise = ContactMethods.getIsEnterprise(contact);
        } catch (_) {
            res.isMe = false;
            res.isUser = true;
            res.isGroup = false;
            res.isWAContact = true;
            res.isMyContact = false;
            res.isEnterprise = false;
        }

        return res;
    };
'@

    if ($utilsContent -match [regex]::Escape($oldGetContactModel)) {
        $utilsContent = $utilsContent.Replace($oldGetContactModel, $newGetContactModel)
        Set-Content -Path $UTILS_JS -Value $utilsContent -NoNewline
        Write-Host "  + getContactModel patched with try-catch wrapper." -ForegroundColor Green
        $patched++
    } else {
        Write-Host "  ! Could not find original getContactModel. May already be patched or code changed." -ForegroundColor Yellow
    }
}


# ─── Patch 3: Download pinned WA Web HTML ────────────────────────────────────
Write-Host "`n[3/3] Downloading pinned WA Web HTML ($PINNED_VERSION) ..." -ForegroundColor Cyan

if (-not (Test-Path $CACHE_DIR)) {
    New-Item -ItemType Directory -Path $CACHE_DIR -Force | Out-Null
}

$htmlFile = "$CACHE_DIR\$PINNED_VERSION.html"

if (Test-Path $htmlFile) {
    $size = (Get-Item $htmlFile).Length
    if ($size -gt 10000) {
        Write-Host "  Already downloaded ($size bytes)." -ForegroundColor DarkGray
        $skipped++
    } else {
        Write-Host "  File too small ($size bytes), re-downloading..." -ForegroundColor Yellow
        $download = $true
    }
} else {
    $download = $true
}

if ($download) {
    try {
        $response = Invoke-WebRequest -Uri $CACHE_URL -UseBasicParsing -TimeoutSec 30
        if ($response.StatusCode -eq 200) {
            Set-Content -Path $htmlFile -Value $response.Content -NoNewline
            Write-Host "  + Downloaded ($($response.Content.Length) bytes)." -ForegroundColor Green
            $patched++
        } else {
            Write-Host "  ! Download failed: HTTP $($response.StatusCode)" -ForegroundColor Red
        }
    } catch {
        Write-Host "  ! Download failed: $($_.Exception.Message)" -ForegroundColor Red
    }
}


# ─── Summary ─────────────────────────────────────────────────────────────────
Write-Host "`n══════════════════════════════════════" -ForegroundColor White
Write-Host "  Patched: $patched  |  Skipped: $skipped" -ForegroundColor $(if ($patched -gt 0) { "Green" } else { "DarkGray" })
Write-Host "══════════════════════════════════════`n" -ForegroundColor White

if ($patched -gt 0) {
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "  1. Kill sidecar:  taskkill /F /IM node.exe /T" -ForegroundColor White
    Write-Host "  2. Start sidecar: php artisan whatsapp:sidecar:start" -ForegroundColor White
    Write-Host "  3. Connect WA:    php artisan whatsapp:web:listen" -ForegroundColor White
}
