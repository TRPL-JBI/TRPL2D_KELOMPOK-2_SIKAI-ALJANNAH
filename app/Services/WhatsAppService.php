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
     * Kirim pesan teks lewat sesi web sidecar.
     *
     * @return array<string, mixed>
     */
    public function send(string $to, string $text): array
    {
        try {
            $result = WhatsApp::web(self::SESSION_ID)->messages()->sendText($to, $text);

            return is_array($result) ? $result : ['success' => true];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
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
     * @return array{installed: bool, running: bool, reachable: bool, pid: ?int, endpoint: string, log_file: string}
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
        ];
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