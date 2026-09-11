<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\CalonAnggota;
use App\Models\KeluargaAnggota;
use App\Models\LogAktivitas;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\WhatsAppService;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;

class SekretarisController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $calonAnggota = CalonAnggota::whereIn('status', ['menunggu_verifikasi', 'sudah_membayar'])
            ->latest()
            ->get();

        $totalAnggota = Anggota::count();
        $anggotaAktif = Anggota::where('status', 'aktif')->count();
        $anggotaNonAktif = Anggota::where('status', 'non_aktif')->count();
        $menungguVerifikasi = CalonAnggota::whereIn('status', ['menunggu_verifikasi', 'sudah_membayar'])->count();

        // Additional stats
        $sudahMembayar = CalonAnggota::where('status', 'sudah_membayar')->count();
        $belumBayar = CalonAnggota::where('status', 'menunggu_verifikasi')->count();
        $diverifikasiBulanIni = Anggota::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count();
        $activities = LogAktivitas::where('modul', 'Sekretaris')->latest()->take(8)->get();

        return view('dashboard.sekretaris.index', compact(
            'calonAnggota',
            'totalAnggota',
            'anggotaAktif',
            'anggotaNonAktif',
            'menungguVerifikasi',
            'sudahMembayar',
            'belumBayar',
            'diverifikasiBulanIni',
            'activities'
        ));
    }

    public function verifikasi($id)
    {
        $calon = CalonAnggota::findOrFail($id);

        if ($calon->status !== 'sudah_membayar') {
            return back()->with('error', 'Calon anggota belum melakukan pembayaran.');
        }

        $calon->update(['status' => 'disetujui']);

        $lastId = Anggota::max('id') ?? 0;
        $nomorAnggota = 'RKM-'.str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

        while (Anggota::where('nomor_anggota', $nomorAnggota)->exists()) {
            $lastId++;
            $nomorAnggota = 'RKM-'.str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
        }

        Anggota::create([
            'nomor_anggota' => $nomorAnggota,
            'calon_anggota_id' => $calon->id,
            'nama' => $calon->nama,
            'email' => $calon->email,
            'telepon' => $calon->telepon,
            'alamat' => $calon->alamat,
            'status' => 'aktif',
            'created_by' => Auth::id(),
        ]);

        LogAktivitas::create([
            'user_id' => Auth::id(),
            'aksi' => 'verifikasi',
            'deskripsi' => "Verifikasi calon atas nama {$calon->nama} - {$nomorAnggota}",
            'modul' => 'Sekretaris',
            'referensi_id' => $calon->id,
        ]);

        $waStatus = $this->kirimPesanWa($calon);

        return back()->with('success', "Calon anggota {$calon->nama} berhasil diverifikasi. {$waStatus}");
    }

    public function kirimWa(Request $request, $id)
    {
        $calon = CalonAnggota::with('anggota')->findOrFail($id);

        if (empty($calon->telepon)) {
            return back()->with('error', "Nomor telepon calon anggota {$calon->nama} tidak tersedia, pesan WhatsApp tidak dapat dikirim.");
        }

        $waStatus = $this->kirimPesanWa($calon);

        if (str_starts_with($waStatus, 'Pesan WhatsApp berhasil')) {
            LogAktivitas::create([
                'user_id' => Auth::id(),
                'aksi' => 'kirim_wa',
                'deskripsi' => "Mengirim pesan WhatsApp ke calon anggota {$calon->nama} ({$calon->telepon})",
                'modul' => 'Sekretaris',
                'referensi_id' => $calon->id,
            ]);

            return back()->with('success', $waStatus);
        }

        return back()->with('error', $waStatus);
    }

    protected function kirimPesanWa(CalonAnggota $calon): string
    {
        if (empty($calon->telepon)) {
            return 'WhatsApp tidak terkirim (nomor telepon calon anggota kosong).';
        }

        $pesan = $this->buatPesanWa($calon);

        try {
            $result = $this->whatsapp->send($calon->telepon, $pesan);

            return isset($result['error'])
                ? "Pesan WhatsApp gagal dikirim ke {$calon->nama} ({$result['error']})."
                : "Pesan WhatsApp berhasil dikirim ke {$calon->nama}.";
        } catch (\Throwable $e) {
            return "Pesan WhatsApp gagal dikirim ke {$calon->nama} ({$e->getMessage()}).";
        }
    }

    protected function buatPesanWa(CalonAnggota $calon): string
    {
        $nama = $calon->nama;

        if ($calon->status === 'disetujui') {
            $nomorAnggota = $calon->anggota?->nomor_anggota ?? '-';

            return "Assalamu'alaikum *$nama*\n\n"
                . "Kami dari Sekretariat RKM Al-Jannah Banyuwangi ingin menyampaikan kabar baik.\n"
                . "Data pendaftaran Anda sebagai calon anggota telah *DIVERIFIKASI* dan *DISETUJUI* oleh Sekretaris.\n\n"
                . "Nomor Anggota: *$nomorAnggota*\n"
                . "Status: *AKTIF*\n\n"
                . "Terima kasih atas kepercayaan Anda kepada RKM Al-Jannah Banyuwangi.\n"
                . "Wassalamu'alaikum warahmatullahi wabarakatuh.";
        }

        if ($calon->status === 'sudah_membayar') {
            return "Assalamu'alaikum *$nama*\n\n"
                . "Pembayaran pendaftaran Anda telah kami *terima*. Data Anda saat ini sedang menunggu verifikasi oleh Sekretaris.\n\n"
                . "Mohon menunggu informasi selanjutnya. Terima kasih atas kesabaran Anda.\n"
                . "Wassalamu'alaikum warahmatullahi wabarakatuh.";
        }

        return "Assalamu'alaikum *$nama*\n\n"
            . "Data pendaftaran Anda sebagai calon anggota RKM Al-Jannah Banyuwangi telah kami terima dan akan segera kami proses.\n\n"
            . "Terima kasih.\n"
            . "Wassalamu'alaikum warahmatullahi wabarakatuh.";
    }

    public function log(Request $request)
    {
        $search = $request->get('search');

        $query = LogAktivitas::where('user_id', Auth::id())->latest();

        if ($search) {
            $query->where('deskripsi', 'like', "%{$search}%")
                ->orWhere('aksi', 'like', "%{$search}%")
                ->orWhere('modul', 'like', "%{$search}%");
        }

        $activities = $query->get();

        return view('dashboard.sekretaris.log', compact('activities', 'search'));
    }

    public function anggota(Request $request)
    {
        $search = $request->get('search');
        $statusFilter = $request->get('status', 'all');

        $query = Anggota::with('calonAnggota.keluargaAnggota')->latest();

        if ($search) {
            $query->where('nama', 'like', "%{$search}%")
                ->orWhere('nomor_anggota', 'like', "%{$search}%")
                ->orWhere('telepon', 'like', "%{$search}%");
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $anggota = $query->paginate(20)->appends(['search' => $search, 'status' => $statusFilter]);

        $totalAnggota = Anggota::count();
        $anggotaAktif = Anggota::where('status', 'aktif')->count();
        $anggotaNonAktif = Anggota::where('status', 'non_aktif')->count();

        return view('dashboard.sekretaris.anggota', compact(
            'anggota',
            'totalAnggota',
            'anggotaAktif',
            'anggotaNonAktif',
            'search',
            'statusFilter'
        ));
    }

    public function editAnggota($id)
    {
        return redirect()->route('sekretaris.anggota');
    }

    public function prosesEditAnggota(Request $request, $id)
    {
        $anggota = Anggota::find($id);

        if (! $anggota) {
            return response()->json(['success' => false, 'message' => 'Anggota tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'anggota_keluarga' => 'nullable|array',
            'anggota_keluarga.*.nama' => 'required|string|max:255',
            'anggota_keluarga.*.jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'anggota_keluarga.*.tanggal_lahir' => 'nullable|date',
        ]);

        $anggota->update([
            'nama' => $validated['nama'],
            'telepon' => $validated['telepon'],
        ]);

        // Sync keluarga
        if ($request->has('anggota_keluarga') && $anggota->calon_anggota_id) {
            KeluargaAnggota::where('calon_anggota_id', $anggota->calon_anggota_id)->delete();
            foreach ($validated['anggota_keluarga'] as $item) {
                if (trim($item['nama'])) {
                    KeluargaAnggota::create([
                        'calon_anggota_id' => $anggota->calon_anggota_id,
                        'nama' => trim($item['nama']),
                        'jenis_kelamin' => $item['jenis_kelamin'],
                        'tanggal_lahir' => $item['tanggal_lahir'] ?? now()->toDateString(),
                    ]);
                }
            }
        }

        LogAktivitas::create([
            'user_id' => Auth::id(),
            'aksi' => 'update',
            'deskripsi' => "Mengupdate data anggota {$anggota->nama} - {$anggota->nomor_anggota}",
            'modul' => 'Sekretaris',
            'referensi_id' => $anggota->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Data anggota {$anggota->nama} berhasil diperbarui.",
        ]);
    }

    public function nonaktifAnggota($id)
    {
        return redirect()->route('sekretaris.anggota');
    }

    public function prosesNonaktifAnggota(Request $request, $id)
    {
        $anggota = Anggota::find($id);

        if (! $anggota) {
            return response()->json(['success' => false, 'message' => 'Anggota tidak ditemukan.'], 404);
        }

        if ($anggota->status !== 'aktif') {
            return response()->json(['success' => false, 'message' => 'Anggota ini sudah nonaktif.'], 422);
        }

        $anggota->update(['status' => 'non_aktif']);

        LogAktivitas::create([
            'user_id' => Auth::id(),
            'aksi' => 'nonaktif',
            'deskripsi' => "Menonaktifkan anggota {$anggota->nama} - {$anggota->nomor_anggota}",
            'modul' => 'Sekretaris',
            'referensi_id' => $anggota->id,
        ]);

        return response()->json(['success' => true, 'message' => "Anggota {$anggota->nama} berhasil dinonaktifkan."]);
    }

    public function prosesAktifkanAnggota(Request $request, $id)
    {
        $anggota = Anggota::find($id);

        if (! $anggota) {
            return response()->json(['success' => false, 'message' => 'Anggota tidak ditemukan.'], 404);
        }

        if ($anggota->status !== 'non_aktif') {
            return response()->json(['success' => false, 'message' => 'Anggota ini sudah aktif.'], 422);
        }

        $anggota->update(['status' => 'aktif']);

        LogAktivitas::create([
            'user_id' => Auth::id(),
            'aksi' => 'aktif',
            'deskripsi' => "Mengaktifkan kembali anggota {$anggota->nama} - {$anggota->nomor_anggota}",
            'modul' => 'Sekretaris',
            'referensi_id' => $anggota->id,
        ]);

        return response()->json(['success' => true, 'message' => "Anggota {$anggota->nama} berhasil diaktifkan kembali."]);
    }

    // ---------------------------------------------------------------
    //  Lihat Formulir Pendaftaran & Cetak PDF
    // ---------------------------------------------------------------

    public function formulirCalon($id)
    {
        $calon = CalonAnggota::with('keluargaAnggota')->findOrFail($id);

        $payload = $this->formulirPayload($calon, false, 'REG-'.str_pad($calon->id, 5, '0', STR_PAD_LEFT));

        return view('dashboard.sekretaris.formulir', $payload + [
            'backUrl' => route('sekretaris.dashboard'),
            'pdfUrl' => route('sekretaris.formulir.pdf', $calon->id),
        ]);
    }

    public function formulirCalonPdf($id)
    {
        $this->preparePdfLimits();

        $calon = CalonAnggota::with('keluargaAnggota')->findOrFail($id);

        $payload = $this->formulirPayload($calon, false, 'REG-'.str_pad($calon->id, 5, '0', STR_PAD_LEFT));

        return Pdf::loadView('components.formulir-pendaftaran', $payload)
            ->download('Formulir-'.Str::slug($calon->nama).'.pdf');
    }

    public function formulirAnggota($id)
    {
        $anggota = Anggota::with('calonAnggota.keluargaAnggota')->findOrFail($id);

        $calon = $anggota->calonAnggota ?? $this->calonFallback($anggota);
        $payload = $this->formulirPayload($calon, true, $anggota->nomor_anggota);

        return view('dashboard.sekretaris.formulir', $payload + [
            'backUrl' => route('sekretaris.anggota'),
            'pdfUrl' => route('sekretaris.anggota.formulir.pdf', $anggota->id),
        ]);
    }

    public function formulirAnggotaPdf($id)
    {
        $this->preparePdfLimits();

        $anggota = Anggota::with('calonAnggota.keluargaAnggota')->findOrFail($id);

        $calon = $anggota->calonAnggota ?? $this->calonFallback($anggota);
        $payload = $this->formulirPayload($calon, true, $anggota->nomor_anggota);

        return Pdf::loadView('components.formulir-pendaftaran', $payload)
            ->download('Formulir-'.Str::slug($anggota->nama).'.pdf');
    }

    private function calonFallback(Anggota $anggota): CalonAnggota
    {
        $calon = new CalonAnggota([
            'nama' => $anggota->nama,
            'email' => $anggota->email,
            'telepon' => $anggota->telepon,
            'alamat' => $anggota->alamat,
            'status' => 'disetujui',
            'anggota_id' => $anggota->id,
        ]);
        $calon->created_at = $anggota->created_at;

        return $calon;
    }

    private function formulirPayload(CalonAnggota $calon, bool $signed, string $nomorPendaftaran): array
    {
        $ketua = User::ketua();

        return [
            'pendaftar' => [
                'nomor_pendaftaran' => $nomorPendaftaran,
                'nama' => $calon->nama ?: '-',
                'jenis_kelamin' => $calon->jenis_kelamin ? ucfirst($calon->jenis_kelamin) : '-',
                'tanggal_lahir' => $calon->tanggal_lahir
                    ? Carbon::parse($calon->tanggal_lahir)->translatedFormat('d F Y')
                    : '-',
                'alamat' => $calon->alamat ?: '-',
                'rt_rw' => $calon->rt_rw ?: '-',
                'kelurahan' => $calon->kelurahan ?: '-',
                'telepon' => $calon->telepon ?: '-',
                'email' => $calon->email ?: '-',
                'tanggal_daftar' => $calon->created_at?->translatedFormat('d F Y') ?? '-',
            ],
            'keluarga' => $calon->keluargaAnggota
                ->pluck('nama')
                ->filter()
                ->map(fn ($nama) => $nama ?: '-')
                ->values()
                ->all(),
            'signed' => $signed,
            'ketuaData' => [
                'nama' => $ketua?->nama ?: '................................',
                'signature_data_uri' => $ketua?->tandaTanganDataUri(),
            ],
            'logoDataUri' => $this->logoDataUri(),
        ];
    }

    private function logoDataUri(): string
    {
        $path = public_path('images/logo-al-jannah.png');

        if (! is_file($path)) {
            return '';
        }

        if (function_exists('imagecreatefrompng')) {
            $src = @imagecreatefrompng($path);

            if ($src) {
                $tw = imagesx($src);
                $th = imagesy($src);
                $maxH = 320;

                if ($th > $maxH) {
                    $nw = (int) round($tw * $maxH / $th);
                    $dst = imagecreatetruecolor($nw, $maxH);
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $maxH, $tw, $th);

                    ob_start();
                    imagepng($dst);
                    $data = ob_get_clean();

                    imagedestroy($dst);
                    imagedestroy($src);

                    if ($data !== false) {
                        return 'data:image/png;base64,'.base64_encode($data);
                    }
                }

                imagedestroy($src);
            }
        }

        return 'data:image/png;base64,'.base64_encode(file_get_contents($path));
    }

    private function preparePdfLimits(): void
    {
        $memory = (int) ini_get('memory_limit');

        if ($memory > 0 && $memory < 256) {
            @ini_set('memory_limit', '256M');
        }

        @set_time_limit(120);
    }

    // ---------------------------------------------------------------
    //  Import & Template Anggota
    // ---------------------------------------------------------------

    public function downloadTemplateAnggota()
    {
        $writer = new Writer;
        $writer->openToBrowser('Template-Import-Anggota.xlsx');

        $writer->addRow(Row::fromValues([
            'PETUNJUK: Isi setiap kolom sesuai petunjuk di bawah. Hapus baris contoh sebelum import.',
            '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'nomor_anggota', 'nama', 'email', 'telepon', 'alamat', 'status', 'nama_tertanggung',
        ]));

        $writer->addRow(Row::fromValues([
            '(auto)', 'Contoh Nama', 'contoh@email.com', '08123456789', 'Alamat lengkap', 'aktif', 'Contoh Istri, Contoh Anak',
        ]));

        $writer->addRow(Row::fromValues([
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'KETERANGAN KOLOM:',
            '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'nomor_anggota',
            'Opsional. Format RKM-XXXXX. Kosongkan untuk diisi otomatis.',
            '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'nama',
            'Wajib. Nama lengkap anggota.',
            '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'email',
            'Wajib. Alamat email (harus unik).',
            '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'telepon',
            'Wajib. Nomor telepon.',
            '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'alamat',
            'Wajib. Alamat lengkap anggota.',
            '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'status',
            'Opsional. "aktif" atau "non_aktif". Default: aktif.',
            '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'nama_tertanggung',
            'Opsional. Nama anggota keluarga yang ditanggung. Pisahkan dengan koma (,) jika lebih dari satu.',
            '', '', '', '', '',
        ]));

        $writer->close();
        exit;
    }

    public function importAnggota(Request $request)
    {
        $request->validate([
            'file_import' => 'required|file|mimes:xlsx,xls,ods|max:5120',
        ]);

        $file = $request->file('file_import');
        $filePath = $file->getRealPath();

        $reader = new Reader;
        $reader->open($filePath);

        $berhasil = 0;
        $gagal = 0;
        $errors = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $idx => $row) {
                if ($idx === 1) {
                    continue;
                }

                $cells = $row->toArray();

                $nomorAnggota = trim((string) ($cells[0] ?? ''));
                $nama = trim((string) ($cells[1] ?? ''));
                $email = trim((string) ($cells[2] ?? ''));
                $telepon = trim((string) ($cells[3] ?? ''));
                $alamat = trim((string) ($cells[4] ?? ''));
                $status = trim((string) ($cells[5] ?? ''));
                $namaTertanggung = trim((string) ($cells[6] ?? ''));

                if (empty($nama) && empty($email)) {
                    continue;
                }

                try {
                    if (empty($nama)) {
                        throw new \Exception("Baris $idx: nama wajib diisi.");
                    }
                    if (empty($email)) {
                        throw new \Exception("Baris $idx: email wajib diisi.");
                    }
                    if (empty($telepon)) {
                        throw new \Exception("Baris $idx: telepon wajib diisi.");
                    }
                    if (empty($alamat)) {
                        throw new \Exception("Baris $idx: alamat wajib diisi.");
                    }

                    if (Anggota::where('email', $email)->exists()) {
                        throw new \Exception("Baris $idx: email \"$email\" sudah terdaftar.");
                    }

                    if (empty($status)) {
                        $status = 'aktif';
                    } elseif (! in_array($status, ['aktif', 'non_aktif'])) {
                        throw new \Exception("Baris $idx: status \"$status\" tidak valid. Gunakan aktif atau non_aktif.");
                    }

                    if (empty($nomorAnggota)) {
                        $lastId = Anggota::max('id') ?? 0;
                        $nomorAnggota = 'RKM-'.str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

                        while (Anggota::where('nomor_anggota', $nomorAnggota)->exists()) {
                            $lastId++;
                            $nomorAnggota = 'RKM-'.str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
                        }
                    } else {
                        if (Anggota::where('nomor_anggota', $nomorAnggota)->exists()) {
                            throw new \Exception("Baris $idx: nomor anggota \"$nomorAnggota\" sudah terdaftar.");
                        }
                    }

                    $anggota = Anggota::create([
                        'nomor_anggota' => $nomorAnggota,
                        'nama' => $nama,
                        'email' => $email,
                        'telepon' => $telepon,
                        'alamat' => $alamat,
                        'status' => $status,
                        'created_by' => Auth::id(),
                    ]);

                    $calon = CalonAnggota::create([
                        'nama' => $nama,
                        'email' => $email,
                        'telepon' => $telepon,
                        'alamat' => $alamat,
                        'status' => 'disetujui',
                        'anggota_id' => $anggota->id,
                    ]);

                    $anggota->update(['calon_anggota_id' => $calon->id]);

                    if (! empty($namaTertanggung)) {
                        $namaList = array_map('trim', explode(',', $namaTertanggung));
                        $namaList = array_filter($namaList);

                        foreach ($namaList as $nm) {
                            KeluargaAnggota::create([
                                'calon_anggota_id' => $calon->id,
                                'nama' => $nm,
                                'jenis_kelamin' => 'laki-laki',
                                'tanggal_lahir' => now()->toDateString(),
                            ]);
                        }
                    }

                    LogAktivitas::create([
                        'user_id' => Auth::id(),
                        'aksi' => 'import',
                        'deskripsi' => "Import anggota {$nama} - {$nomorAnggota}",
                        'modul' => 'Sekretaris',
                        'referensi_id' => 0,
                    ]);

                    $berhasil++;
                } catch (\Exception $e) {
                    $gagal++;
                    $errors[] = $e->getMessage();
                }
            }
        }

        $reader->close();

        return response()->json([
            'success' => $berhasil > 0,
            'message' => "Import selesai: $berhasil berhasil, $gagal gagal.",
            'berhasil' => $berhasil,
            'gagal' => $gagal,
            'errors' => $errors,
        ]);
    }
}
