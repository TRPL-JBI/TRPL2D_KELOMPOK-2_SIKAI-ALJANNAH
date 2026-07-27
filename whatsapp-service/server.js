import { makeWASocket, useMultiFileAuthState, DisconnectReason, makeCacheableSignalKeyStore } from '@whiskeysockets/baileys';
import { Boom } from '@hapi/boom';
import express from 'express';
import pino from 'pino';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import fs from 'fs';
import QR from 'qrcode';

const __dirname = dirname(fileURLToPath(import.meta.url));
const AUTH_DIR = join(__dirname, 'auth');
const PORT = process.env.PORT || 3001;
const API_TOKEN = process.env.API_TOKEN || 'aljannah-wa-secret';

if (!fs.existsSync(AUTH_DIR)) {
  fs.mkdirSync(AUTH_DIR, { recursive: true });
}

const logger = pino({
  level: process.env.LOG_LEVEL || 'info',
  transport: {
    target: 'pino-pretty',
    options: { colorize: true },
  },
});

let sock = null;
let connectionState = 'disconnected';
let pairingCode = null;
let qrDataUrl = null;
let reconnectTimeout = null;
let intentionalStop = false;

const app = express();
app.use(express.json());

function authMiddleware(req, res, next) {
  const token = req.headers['authorization'];
  if (!token || token !== `Bearer ${API_TOKEN}`) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
}

async function stopSocket() {
  intentionalStop = true;
  if (reconnectTimeout) {
    clearTimeout(reconnectTimeout);
    reconnectTimeout = null;
  }
  if (sock) {
    try { await sock.end(new Boom('Manual stop')); } catch (e) {}
    sock = null;
  }
  connectionState = 'disconnected';
  pairingCode = null;
  qrDataUrl = null;
}

async function startSocket() {
  if (sock) {
    try { await sock.end(new Boom('Restarting')); } catch (e) {}
    sock = null;
  }

  pairingCode = null;
  qrDataUrl = null;
  connectionState = 'connecting';

  const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
  const signalKeyStore = makeCacheableSignalKeyStore(state.keys, logger);

  sock = makeWASocket({
    auth: {
      creds: state.creds,
      keys: signalKeyStore,
    },
    printQRInTerminal: false,
    logger: pino({ level: 'silent' }),
    browser: ['Al-Jannah WA', 'Chrome', '10.0'],
    syncFullHistory: false,
    markOnlineOnConnect: false,
  });

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      try {
        qrDataUrl = await QR.toDataURL(qr, { scale: 8, margin: 2 });
      } catch (e) {
        logger.error({ err: e }, 'Failed to generate QR image');
      }
      pairingCode = null;
      connectionState = 'qr_ready';
      logger.info('QR code generated');
    }

    if (connection === 'open') {
      connectionState = 'connected';
      pairingCode = null;
      qrDataUrl = null;
      logger.info('WhatsApp connected');
    }

    if (connection === 'close') {
      const shouldReconnect = lastDisconnect?.error instanceof Boom
        ? lastDisconnect.error.output?.statusCode !== DisconnectReason.loggedOut
        : true;

      connectionState = 'disconnected';
      pairingCode = null;
      qrDataUrl = null;
      logger.info(`WhatsApp disconnected, reconnect: ${shouldReconnect}`);

      if (reconnectTimeout) clearTimeout(reconnectTimeout);

      if (shouldReconnect && !intentionalStop) {
        logger.info('Reconnecting in 3s...');
        reconnectTimeout = setTimeout(startSocket, 3000);
      }
    }
  });

  if (state.creds?.me?.id) {
    connectionState = 'connecting';
  }
}

app.get('/api/status', authMiddleware, (req, res) => {
  const phone = sock?.user?.id?.split(':')[0]?.replace(/[^0-9]/g, '') || null;
  const phoneFormatted = phone ? `+${phone}` : null;

  res.json({
    status: connectionState,
    phone: phoneFormatted,
    name: sock?.user?.name || null,
    hasSession: !!sock?.user?.id,
    qr: qrDataUrl,
    pairingCode: pairingCode,
  });
});

app.post('/api/connect', authMiddleware, async (req, res) => {
  const { phone } = req.body;

  if (!phone) {
    return res.status(400).json({ error: 'Nomor telepon diperlukan' });
  }

  const cleanPhone = phone.replace(/[^0-9]/g, '');

  if (connectionState === 'connected') {
    return res.json({ message: 'Already connected', status: 'connected' });
  }

  try {
    if (!sock) {
      await startSocket();
      await new Promise((r) => setTimeout(r, 2000));
    }

    qrDataUrl = null;
    const code = await sock.requestPairingCode(cleanPhone);
    pairingCode = code;
    connectionState = 'pairing';

    logger.info(`Pairing code generated for ${cleanPhone}: ${code}`);

    res.json({ pairingCode: code, status: 'pairing' });
  } catch (err) {
    logger.error({ err }, 'Failed to generate pairing code');
    res.status(500).json({ error: err.message || 'Gagal generate pairing code' });
  }
});

app.post('/api/connect-qr', authMiddleware, async (req, res) => {
  try {
    // Kill reconnection loop first
    intentionalStop = true;
    if (reconnectTimeout) {
      clearTimeout(reconnectTimeout);
      reconnectTimeout = null;
    }

    // Wipe auth to force fresh QR generation
    if (fs.existsSync(AUTH_DIR)) {
      fs.rmSync(AUTH_DIR, { recursive: true, force: true });
    }
    fs.mkdirSync(AUTH_DIR, { recursive: true });

    // End existing socket
    if (sock) {
      try { await sock.end(new Boom('Switching to QR mode')); } catch (e) {}
      sock = null;
    }

    // Reset state
    pairingCode = null;
    qrDataUrl = null;
    connectionState = 'connecting';
    intentionalStop = false;

    // Start fresh socket — Baileys will emit QR automatically
    startSocket();

    res.json({ status: 'qr_started', message: 'QR generation started' });
  } catch (err) {
    logger.error({ err }, 'Failed to start QR mode');
    res.status(500).json({ error: err.message || 'Gagal memulai QR' });
  }
});

app.post('/api/send', authMiddleware, async (req, res) => {
  const { to, text } = req.body;

  if (!to || !text) {
    return res.status(400).json({ error: 'Recipient (to) and message text required' });
  }

  if (connectionState !== 'connected') {
    return res.status(503).json({ error: 'WhatsApp not connected', status: connectionState });
  }

  try {
    const cleanNumber = to.replace(/[^0-9]/g, '');
    const jid = `${cleanNumber}@s.whatsapp.net`;

    await sock.sendMessage(jid, { text });
    logger.info(`Message sent to ${cleanNumber}`);

    res.json({ success: true, to: cleanNumber });
  } catch (err) {
    logger.error({ err }, 'Failed to send message');
    res.status(500).json({ error: err.message || 'Failed to send message' });
  }
});

app.post('/api/logout', authMiddleware, async (req, res) => {
  try {
    if (sock) {
      await sock.logout('User initiated logout');
    }

    await stopSocket();

    if (fs.existsSync(AUTH_DIR)) {
      fs.rmSync(AUTH_DIR, { recursive: true, force: true });
      fs.mkdirSync(AUTH_DIR, { recursive: true });
    }

    res.json({ success: true, message: 'Logged out' });
  } catch (err) {
    logger.error({ err }, 'Failed to logout');
    res.status(500).json({ error: err.message || 'Failed to logout' });
  }
});

app.get('/health', (req, res) => {
  res.json({ ok: true, status: connectionState, hasQR: !!qrDataUrl });
});

async function main() {
  await startSocket();
  app.listen(PORT, () => {
    logger.info(`WhatsApp service running on port ${PORT}`);
  });
}

main().catch((err) => {
  logger.error({ err }, 'Fatal error');
  process.exit(1);
});
