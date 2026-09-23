<?php

namespace App\Services;

use Kstmostofa\LaravelWhatsApp\Exceptions\SidecarException;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;
use Kstmostofa\LaravelWhatsApp\Web\SidecarManager;

class WhatsAppService
{
    protected const SESSION_ID = 'main';

    public function __construct(protected SidecarManager $sidecar)
    {
    }

    /**
     * Apakah service sidecar (whatsapp-web.js) sedang berjalan & bisa dijangkau.
     */
    public function health(): bool
    {
        try {
            return WhatsApp::webClient()->ping();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Status sesi WhatsApp saat ini.
     *
     * @return array{status: string, phone?: ?string, name?: ?string, qr?: ?string, error?: string}
     */
    public function status(): array
    {
        try {
            $session = WhatsApp::web(self::SESSION_ID);
            $state = $session->state();

            $status = $state['status'] ?? 'unknown';
            $result = ['status' => $status];

            if ($status === 'ready') {
                $result = array_merge($result, $this->info());
            } elseif ($status === 'qr') {
                try {
                    $qr = $session->qr();
                    $result['qr'] = $qr['qr'] ?? null;
                } catch (\Throwable) {
                }
            }

            return $result;
        } catch (SidecarException $e) {
            if ($e->getCode() === 404) {
                return ['status' => 'stopped'];
            }

            return ['status' => 'error', 'error' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    /**
     * Ambil informasi sesi (nomor & nama) saat sudah ready.
     *
     * @return array{phone: ?string, name: ?string}
     */
    protected function info(): array
    {
        try {
            $info = WhatsApp::web(self::SESSION_ID)->info();
            $info = $info['info'] ?? [];

            $phone = $info['wid']['user'] ?? null;
            $name = $info['pushname'] ?? null;

            return [
                'phone' => $phone ? '+'.$phone : null,
                'name' => $name ?: null,
            ];
        } catch (\Throwable) {
            return ['phone' => null, 'name' => null];
        }
    }

    /**
     * Mulai sesi (menghasilkan QR bila belum tertaut).
     *
     * @return array<string, mixed>
     */
    public function connectQr(): array
    {
        try {
            return WhatsApp::web(self::SESSION_ID)->start();
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Apakah sesi WhatsApp sudah tertaut & siap (status "ready").
     */
    public function isReady(): bool
    {
        return ($this->status()['status'] ?? '') === 'ready';
    }

    /**
     * Kirim pesan teks lewat sesi web sidecar.
     *
     * @return array<string, mixed>
     */
    public function send(string $to, string $text, ?int $delayMs = null): array
    {
        // Terapkan jeda jika di-specifikasikan (ms)
        if ($delayMs !== null && $delayMs > 0) {
            \Illuminate\Support\Facades\Log::info("WA send delay ${delayMs}ms sebelum mengirim ke {$to}");
            \Illuminate\Support\Facades\Log::info("WA send delay: menunggu ${delayMs}ms sebelum mengirim pesan");
            \Illuminate\Support\Facades\Log::info("WA send delay: catatan - jeda ini membantu mitigasi blokir WhatsApp");
            sleep($delayMs / 1000);
        }

        try {
            $result = WhatsApp::web(self::SESSION_ID)->messages()->sendText($this->normalizeNumber($to), $text);

            return is_array($result) ? $result : ['success' => true];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Terapkan spintax (variasi teks) ke pesan.
     * Format spintax: {pilihan1|pilihan2|pilihan3}
     *
     * @param string $text Teks yang mungkin berisi spintax
     * @return string Teks dengan variasi acak
     */
    public function applySpintaxPublic(string $text): string
    {
        return $this->applySpintax($text);
    }

    /**
     * Terapkan spintax (variasi teks) ke pesan.
     * Format spintax: {pilihan1|pilihan2|pilihan3}
     *
     * @param string $text Teks yang mungkin berisi spintax
     * @return string Teks dengan variasi acak
     */
    protected function applySpintax(string $text): string
    {
        // Cari pola spintax {pilihan1|pilihan2|pilihan3}
        if (!preg_match('/\{([^}]+)\}/', $text)) {
            return $text;
        }

        // Ganti setiap blok spintax dengan pilihan acak
        return preg_replace_callback(
            '/\{([^}]+)\}/',
            function ($matches) {
                $options = explode('|', $matches[1]);
                return $options[array_rand($options)];
            },
            $text
        );
    }

    /**
     * Kirim pesan ke banyak penerima dengan jeda antar pesan (mitigasi blokir).
     *
     * @param array $recipients Daftar nomor telepon (dalam format internasional tanpa +)
     * @param string $text Teks pesan (boleh berisi spintax {pilihan1|pilihan2})
     * @param int $delayMs Jeda antar pesan dalam milidetik (default: 15000 = 15 detik)
     * @param bool $randomDelay Jeda acak antara min/max (ms)
     * @return array['terkirim' => int, 'gagal' => int, 'detail' => array]
     */
    public function sendBulk(array $recipients, string $text, int $delayMs = 15000, bool $randomDelay = true): array
    {
        $terkirim = 0;
        $gagal = 0;
        $detail = [];

        // Terapkan variasi spintax jika ada
        $textVaried = $this->applySpintax($text);

        foreach ($recipients as $index => $to) {
            // Hitung jeda: dasar + acak jika diaktifkan
            $delay = $delayMs;
            if ($randomDelay) {
                // Jeda acak 10-30 detik (10000-30000ms)
                $randomFactor = 10000 + mt_rand(0, 20000);
                $delay = $randomFactor;
            }

            // Kecilkan delay untuk penerima pertama (no delay)
            if ($index > 0) {
                $recipient = $to;
                \Illuminate\Support\Facades\Log::info("WA bulk send: ke-".($index+1)."/".count($recipients)." - jeda ${delay}ms ke {$recipient}");
                sleep($delay / 1000);
            }

            $result = $this->send($to, $textVaried);

            if (isset($result['error'])) {
                $gagal++;
                $detail[] = [
                    'nomor' => $to,
                    'status' => 'gagal',
                    'error' => $result['error'],
                ];
            } else {
                $terkirim++;
                $detail[] = [
                    'nomor' => $to,
                    'status' => 'terkirim',
                    'waktu' => now()->format('H:i:s'),
                ];
            }

            // Log progres setiap 10 pesan
            if (($index + 1) % 10 === 0) {
                \Illuminate\Support\Facades\Log::info("WA bulk send progres: {$terkirim} terkirim, {$gagal} gagal setelah ".($index+1)." pesan");
            }
        }

        return [
            'terkirim' => $terkirim,
            'gagal' => $gagal,
            'total' => count($recipients),
            'detail' => $detail,
            'pesan' => $textVaried,
        ];
    }

    /**
     * Kirim dokumen (mis. PDF kwitansi) sebagai lampiran lewat sesi web sidecar.
     *
     * @return array<string, mixed>
     */
    public function sendDocument(string $to, string $base64, string $filename, string $mimeType, ?string $caption = null): array
    {
        $normalizedTo = $this->normalizeNumber($to);
        $b64Len = strlen($base64);

        // Pre-flight: pastikan base64 tidak kosong
        if ($base64 === '') {
            \Illuminate\Support\Facades\Log::error('WA sendDocument DITOLAK: base64 kosong', [
                'to' => $normalizedTo,
                'filename' => $filename,
            ]);

            return ['error' => 'base64 kosong'];
        }

        try {
            \Illuminate\Support\Facades\Log::info('WA sendDocument dimulai', [
                'to' => $normalizedTo,
                'filename' => $filename,
                'mimeType' => $mimeType,
                'b64_length' => $b64Len,
                'caption_length' => strlen($caption ?? ''),
            ]);

            $result = WhatsApp::web(self::SESSION_ID)->messages()->sendDocument($normalizedTo, array_filter([
                'base64' => $base64,
                'mimeType' => $mimeType,
                'filename' => $filename,
                'caption' => $caption,
            ]));

            \Illuminate\Support\Facades\Log::info('WA sendDocument response', [
                'result' => $result,
            ]);

            return is_array($result) ? $result : ['success' => true];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('WA sendDocument ERROR: ' . $e->getMessage(), [
                'to' => $normalizedTo,
                'filename' => $filename,
                'b64_length' => $b64Len,
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Normalisasi nomor ke format WA ID (kode negara Indonesia + tanpa "0" awal).
     *
     * 081234567890  → 6281234567890
     * +6281234567890 → 6281234567890
     * 6281234567890  → 6281234567890
     * 62812...@c.us  → dibiarkan (sudah WA ID)
     */
    protected function normalizeNumber(string $to): string
    {
        $to = trim($to);

        if ($to === '' || str_contains($to, '@')) {
            return $to;
        }

        $digits = preg_replace('/\D+/', '', $to);

        if ($digits === '') {
            return $to;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Putuskan koneksi & hapus autentikasi tersimpan (QR baru pada koneksi berikutnya).
     *
     * @return array<string, mixed>
     */
    public function logout(): array
    {
        try {
            WhatsApp::web(self::SESSION_ID)->destroy();

            return ['success' => true];
        } catch (SidecarException $e) {
            return $e->getCode() === 404
                ? ['success' => true]
                : ['error' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Status server sidecar: installed, running, dan dapat dijangkau.
     *
     * @return array{installed: bool, running: bool, reachable: bool, pid: ?int, endpoint: string, log_file: string, log_tail: string}
     */
    public function serverStatus(): array
    {
        return [
            'installed' => $this->sidecar->isInstalled(),
            'running' => $this->sidecar->isRunning(),
            'reachable' => $this->health(),
            'pid' => $this->sidecar->pid(),
            'endpoint' => sprintf('http://%s:%d', $this->sidecar->host(), $this->sidecar->port()),
            'log_file' => $this->sidecar->logFile(),
            'log_tail' => $this->serverLogTail(),
        ];
    }

    /**
     * Ambil beberapa baris terakhir dari log error sidecar (untuk diagnosa di UI).
     */
    protected function serverLogTail(int $lines = 15): string
    {
        $file = $this->sidecar->errFile();

        if (! is_file($file)) {
            return '';
        }

        $content = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        return trim(implode("\n", array_slice($content, -$lines)));
    }

    /**
     * Mulai server sidecar sebagai proses terpisah.
     *
     * @return array<string, mixed>
     */
    public function startServer(): array
    {
        try {
            if (! $this->sidecar->isInstalled()) {
                return ['error' => 'Sidecar belum di-install. Jalankan `php artisan whatsapp:sidecar:install` di server.'];
            }

            if ($this->sidecar->isRunning()) {
                return ['error' => 'Server WhatsApp sudah berjalan (PID '.$this->sidecar->pid().').'];
            }

            $pid = $this->sidecar->start();

            return ['success' => true, 'pid' => $pid];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Hentikan server sidecar.
     *
     * @return array<string, mixed>
     */
    public function stopServer(): array
    {
        try {
            if (! $this->sidecar->isRunning()) {
                return ['error' => 'Server WhatsApp tidak sedang berjalan.'];
            }

            $this->sidecar->stop();

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}