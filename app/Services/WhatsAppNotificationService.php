<?php

namespace App\Services;

use App\Models\CalonAnggota;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    public function __construct(
        protected WhatsAppService $whatsapp,
    ) {}

    // ----------------------------------------------------------------
    //  1. Saat calon anggota mendaftar → notif ke Ketua & Sekretaris
    // ----------------------------------------------------------------

    /**
     * Kirim notifikasi ke ketua & sekretaris bahwa ada pendaftar baru.
     */
    public function notifyPengurusBaruDaftar(CalonAnggota $calon): array
    {
        if (! $this->whatsapp->isReady()) {
            Log::warning('WhatsApp not ready, skipping pengurus notification for: ' . $calon->nama);

            return ['success' => false, 'reason' => 'whatsapp_not_ready'];
        }

        $recipients = User::whereIn('role', ['ketua', 'sekretaris'])
            ->where('status', 'aktif')
            ->whereNotNull('no_whatsapp')
            ->where('no_whatsapp', '!=', '')
            ->get();

        return $this->sendToMany($recipients, function (User $pengurus) use ($calon) {
            $jk = $calon->jenis_kelamin === 'laki-laki' ? 'Laki-laki' : 'Perempuan';
            $roleLabel = ucfirst($pengurus->role);

            return "Assalamu'alaikum *{$pengurus->nama}* ({$roleLabel})\n\n"
                . "Ada calon anggota baru yang mendaftar di RKM Al-Jannah:\n\n"
                . "• Nama: *{$calon->nama}*\n"
                . "• Jenis Kelamin: {$jk}\n"
                . "• Telepon: {$calon->telepon}\n"
                . "• Alamat: {$calon->alamat}\n"
                . "• RT/RW: {$calon->rt_rw}\n"
                . "• Kelurahan: {$calon->kelurahan}\n"
                . "• Tanggal Daftar: {$calon->created_at->format('d/m/Y H:i')}\n\n"
                . "Status: *Menunggu Verifikasi*\n\n"
                . "Silakan cek dashboard untuk melakukan verifikasi.\n"
                . "Wassalamu'alaikum warahmatullahi wabarakatuh.";
        });
    }

    // ----------------------------------------------------------------
    //  2. Sekretaris konfirmasi → "dalam_pembayaran"
    //     → WA ke calon anggota (info bayar + nomor bendahara)
    //     → WA ke bendahara (info calon anggota baru)
    // ----------------------------------------------------------------

    /**
     * Kirim notifikasi ke calon anggota bahwa pendaftarannya sedang diproses
     * dan memberikan daftar nomor bendahara untuk pembayaran.
     */
    public function notifyCalonDalamPembayaran(CalonAnggota $calon): array
    {
        if (! $this->whatsapp->isReady()) {
            return ['success' => false, 'reason' => 'whatsapp_not_ready'];
        }

        if (empty($calon->telepon)) {
            return ['success' => false, 'reason' => 'no_phone'];
        }

        $bendaharaList = $this->getBendaharaList();
        $daftarNomor = $this->formatDaftarBendahara($bendaharaList);

        $pesan = "Assalamu'alaikum *{$calon->nama}*,\n\n"
            . "Alhamdulillah, data pendaftaran Anda sebagai calon anggota RKM Al-Jannah telah *DIVERIFIKASI* oleh Sekretaris.\n\n"
            . "Silakan melakukan pembayaran pendaftaran sebesar *Rp 30.000* ke salah satu Bendahara berikut:\n\n"
            . $daftarNomor . "\n"
            . "Setelah melakukan pembayaran, mohon kirimkan bukti transfer kepada Bendahara yang bersangkutan.\n\n"
            . "Terima kasih atas perhatiannya.\n"
            . "Wassalamu'alaikum warahmatullahi wabarakatuh.";

        $result = $this->whatsapp->send($calon->telepon, $pesan);

        if (isset($result['error'])) {
            Log::warning('WA notifikasi calon dalam pembayaran gagal: ' . $result['error'], [
                'calon' => $calon->nama,
                'telepon' => $calon->telepon,
            ]);
        }

        return [
            'success' => ! isset($result['error']),
            'error' => $result['error'] ?? null,
        ];
    }

    /**
     * Kirim notifikasi ke semua bendahara bahwa ada calon anggota baru
     * yang masuk status "dalam pembayaran".
     */
    public function notifyBendaharaCalonBaru(CalonAnggota $calon): array
    {
        if (! $this->whatsapp->isReady()) {
            return ['success' => false, 'reason' => 'whatsapp_not_ready'];
        }

        $recipients = User::where('role', 'bendahara')
            ->where('status', 'aktif')
            ->whereNotNull('no_whatsapp')
            ->where('no_whatsapp', '!=', '')
            ->get();

        return $this->sendToMany($recipients, function (User $bendahara) use ($calon) {
            $jk = $calon->jenis_kelamin === 'laki-laki' ? 'Laki-laki' : 'Perempuan';

            return "Assalamu'alaikum *{$bendahara->nama}* (Bendahara)\n\n"
                . "Ada calon anggota baru yang telah diverifikasi oleh Sekretaris dan sedang menunggu pembayaran:\n\n"
                . "• Nama: *{$calon->nama}*\n"
                . "• Jenis Kelamin: {$jk}\n"
                . "• Telepon: {$calon->telepon}\n"
                . "• Alamat: {$calon->alamat}\n"
                . "• Tanggal Daftar: {$calon->created_at->format('d/m/Y H:i')}\n\n"
                . "Status: *Dalam Pembayaran*\n\n"
                . "Mohon konfirmasi di dashboard setelah pembayaran diterima.\n"
                . "Wassalamu'alaikum warahmatullahi wabarakatuh.";
        });
    }

    // ----------------------------------------------------------------
    //  3. Sekretaris menolak calon anggota
    //     → WA ke calon anggota (info ditolak + alasan)
    // ----------------------------------------------------------------

    /**
     * Kirim notifikasi ke calon anggota bahwa pendaftaran ditolak beserta alasan.
     */
    public function notifyCalonDitolak(CalonAnggota $calon, string $alasan): array
    {
        if (! $this->whatsapp->isReady()) {
            return ['success' => false, 'reason' => 'whatsapp_not_ready'];
        }

        if (empty($calon->telepon)) {
            return ['success' => false, 'reason' => 'no_phone'];
        }

        $pesan = "Assalamu'alaikum *{$calon->nama}*,\n\n"
            . "Mohon maaf, pendaftaran Anda sebagai calon anggota RKM Al-Jannah *TIDAK DAPAT DIPROSES*.\n\n"
            . "Alasan:\n*{$alasan}*\n\n"
            . "Anda dipersilakan untuk mendaftar kembali apabila syarat dan ketentuan sudah terpenuhi.\n\n"
            . "Terima kasih atas pengertian Anda.\n"
            . "Wassalamu'alaikum warahmatullahi wabarakatuh.";

        $result = $this->whatsapp->send($calon->telepon, $pesan);

        if (isset($result['error'])) {
            Log::warning('WA notifikasi calon ditolak gagal: ' . $result['error'], [
                'calon' => $calon->nama,
                'telepon' => $calon->telepon,
            ]);
        }

        return [
            'success' => ! isset($result['error']),
            'error' => $result['error'] ?? null,
        ];
    }

    // ----------------------------------------------------------------
    //  Helpers
    // ----------------------------------------------------------------

    /**
     * Ambil semua bendahara aktif yang punya nomor WhatsApp.
     */
    protected function getBendaharaList(): Collection
    {
        return User::where('role', 'bendahara')
            ->where('status', 'aktif')
            ->whereNotNull('no_whatsapp')
            ->where('no_whatsapp', '!=', '')
            ->get();
    }

    /**
     * Format daftar nomor bendahara untuk pesan WA ke calon anggota.
     */
    protected function formatDaftarBendahara(Collection $bendaharaList): string
    {
        if ($bendaharaList->isEmpty()) {
            return "• (Belum ada nomor bendahara terdaftar)\n";
        }

        $lines = [];
        foreach ($bendaharaList as $b) {
            $lines[] = "• *{$b->nama}*: {$b->no_whatsapp}";
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Kirim pesan ke banyak penerima, kembalikan ringkasan hasil.
     */
    protected function sendToMany(Collection $recipients, callable $messageBuilder): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            $message = $messageBuilder($recipient);
            $result = $this->whatsapp->send($recipient->no_whatsapp, $message);

            $results[] = [
                'nama' => $recipient->nama,
                'role' => $recipient->role,
                'success' => ! isset($result['error']),
                'error' => $result['error'] ?? null,
            ];
        }

        return [
            'success' => true,
            'sent' => count(array_filter($results, fn($r) => $r['success'])),
            'failed' => count(array_filter($results, fn($r) => ! $r['success'])),
            'details' => $results,
        ];
    }
}
