<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\CalonAnggota;
use App\Models\KeluargaAnggota;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;

class SekretarisController extends Controller
{
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
        $nomorAnggota = 'RKM-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

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

        return back()->with('success', "Calon anggota {$calon->nama} berhasil diverifikasi.");
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

        if (!$anggota) {
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

        if (!$anggota) {
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

        if (!$anggota) {
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
    //  Import & Template Anggota
    // ---------------------------------------------------------------

    public function downloadTemplateAnggota()
    {
        $writer = new Writer();
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

        $reader = new Reader();
        $reader->open($filePath);

        $berhasil = 0;
        $gagal = 0;
        $errors = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $idx => $row) {
                if ($idx === 1) continue;

                $cells = $row->toArray();

                $nomorAnggota = trim((string) ($cells[0] ?? ''));
                $nama = trim((string) ($cells[1] ?? ''));
                $email = trim((string) ($cells[2] ?? ''));
                $telepon = trim((string) ($cells[3] ?? ''));
                $alamat = trim((string) ($cells[4] ?? ''));
                $status = trim((string) ($cells[5] ?? ''));
                $namaTertanggung = trim((string) ($cells[6] ?? ''));

                if (empty($nama) && empty($email)) continue;

                try {
                    if (empty($nama)) throw new \Exception("Baris $idx: nama wajib diisi.");
                    if (empty($email)) throw new \Exception("Baris $idx: email wajib diisi.");
                    if (empty($telepon)) throw new \Exception("Baris $idx: telepon wajib diisi.");
                    if (empty($alamat)) throw new \Exception("Baris $idx: alamat wajib diisi.");

                    if (Anggota::where('email', $email)->exists()) {
                        throw new \Exception("Baris $idx: email \"$email\" sudah terdaftar.");
                    }

                    if (empty($status)) {
                        $status = 'aktif';
                    } elseif (!in_array($status, ['aktif', 'non_aktif'])) {
                        throw new \Exception("Baris $idx: status \"$status\" tidak valid. Gunakan aktif atau non_aktif.");
                    }

                    if (empty($nomorAnggota)) {
                        $lastId = Anggota::max('id') ?? 0;
                        $nomorAnggota = 'RKM-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

                        while (Anggota::where('nomor_anggota', $nomorAnggota)->exists()) {
                            $lastId++;
                            $nomorAnggota = 'RKM-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
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

                    if (!empty($namaTertanggung)) {
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
