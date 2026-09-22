<?php

namespace App\Listeners;

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;

class BackupNotificationListener
{
    public function __construct(
        protected WhatsAppService $whatsapp,
    ) {}

    /**
     * Handle successful backup event.
     */
    public function handleBackupWasSuccessful(BackupWasSuccessful $event): void
    {
        $this->notify(
            "✅ Backup *Al-Jannah* berhasil!\n"
            . "Waktu: " . now()->format('d M Y H:i') . "\n"
            . "Aplikasi: {$event->backupName}"
        );
    }

    /**
     * Handle failed backup event.
     */
    public function handleBackupHasFailed(BackupHasFailed $event): void
    {
        $error = $event->exception->getMessage() ?? 'Unknown error';

        $this->notify(
            "❌ Backup *Al-Jannah* GAGAL!\n"
            . "Waktu: " . now()->format('d M Y H:i') . "\n"
            . "Aplikasi: {$event->backupName}\n"
            . "Error: {$error}"
        );
    }

    /**
     * Send WhatsApp notification to Super Admin.
     */
    protected function notify(string $message): void
    {
        $phone = config('backup.whatsapp_admin_phone');

        if (empty($phone)) {
            Log::warning('Backup notification skipped: BACKUP_WHATSAPP_ADMIN_PHONE not set');
            return;
        }

        try {
            $result = $this->whatsapp->send($phone, $message);

            if (isset($result['error'])) {
                Log::error('Backup WhatsApp notification failed', ['error' => $result['error']]);
            }
        } catch (\Throwable $e) {
            Log::error('Backup WhatsApp notification exception', ['message' => $e->getMessage()]);
        }
    }
}
