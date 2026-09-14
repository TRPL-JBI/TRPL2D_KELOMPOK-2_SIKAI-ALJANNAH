<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\AsetKendaraan;
use App\Models\KategoriPemasukan;
use App\Models\KategoriPengeluaran;
use App\Models\LogAktivitas;
use App\Models\LogSuperadmin;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\PermintaanIzin;
use App\Models\StokBarang;
use Illuminate\Http\Request;

class KetuaController extends Controller
{
    public function index()
    {
        $totalAnggota = Anggota::where('status', 'aktif')->count();
        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');
        $saldo = $totalPemasukan - $totalPengeluaran;
        $totalAset = AsetKendaraan::count();
        $pendingIzin = PermintaanIzin::where('status', 'menunggu')->count();

        $year = date('Y');
        $monthlyPemasukan = Pemasukan::selectRaw("strftime('%m', created_at) as bulan, SUM(jumlah) as total")
            ->whereRaw("strftime('%Y', created_at) = ?", [$year])
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $monthlyPengeluaran = Pengeluaran::selectRaw("strftime('%m', created_at) as bulan, SUM(jumlah) as total")
            ->whereRaw("strftime('%Y', created_at) = ?", [$year])
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartPemasukan = [];
        $chartPengeluaran = [];
        for ($b = 1; $b <= 12; $b++) {
            $chartPemasukan[] = (int) ($monthlyPemasukan[$b] ?? 0);
            $chartPengeluaran[] = (int) ($monthlyPengeluaran[$b] ?? 0);
        }

        $logAktivitas = collect(LogAktivitas::with('user')->latest()->take(5)->get()->map(fn ($l) => (object) [
            'deskripsi' => $l->deskripsi,
            'user_nama' => $l->user?->nama ?? 'Sistem',
            'created_at' => $l->created_at,
            'modul' => $l->modul,
            'source' => 'log_aktivitas',
        ]));

        $logSuperadmin = collect(LogSuperadmin::with('user')->latest()->take(5)->get()->map(fn ($l) => (object) [
            'deskripsi' => $l->deskripsi,
            'user_nama' => $l->user?->nama ?? 'Sistem',
            'created_at' => $l->created_at,
            'modul' => $l->modul,
            'source' => 'log_superadmin',
        ]));

        $activities = $logAktivitas->merge($logSuperadmin)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        $requests = PermintaanIzin::with('user')->where('status', 'menunggu')->latest()->take(5)->get();

        $signature = auth()->user()->tandaTanganDataUri();

        return view('dashboard.ketua.index', compact(
            'totalAnggota', 'totalPemasukan', 'totalPengeluaran', 'saldo', 'totalAset', 'pendingIzin',
            'chartLabels', 'chartPemasukan', 'chartPengeluaran',
            'activities', 'requests', 'signature'
        ));
    }

    public function anggota(Request $request)
    {
        $anggota = Anggota::where('status', 'aktif')
            ->when($request->search, fn ($q, $s) => $q->where('nama', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20);

        return view('dashboard.ketua.anggota', compact('anggota'));
    }

    public function keuangan(Request $request)
    {
        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');
        $saldo = $totalPemasukan - $totalPengeluaran;

        $pemasukan = Pemasukan::with('kategoriPemasukan')->get();
        $pengeluaran = Pengeluaran::with('kategoriPengeluaran')->get();

        $transactions = collect()
            ->merge($pemasukan->map(fn ($p) => [
                'tanggal' => $p->created_at->format('d/m/Y'),
                'keterangan' => $p->keterangan ?? '',
                'kategori' => 'Pemasukan',
                'kategori_nama' => $p->kategoriPemasukan?->nama ?? '-',
                'jumlah' => $p->jumlah,
            ]))
            ->merge($pengeluaran->map(fn ($p) => [
                'tanggal' => $p->created_at->format('d/m/Y'),
                'keterangan' => $p->keterangan ?? '',
                'kategori' => 'Pengeluaran',
                'kategori_nama' => $p->kategoriPengeluaran?->nama ?? '-',
                'jumlah' => $p->jumlah,
            ]))
            ->sortByDesc('tanggal')
            ->values();

        if ($request->search) {
            $s = $request->search;
            $transactions = $transactions->filter(fn ($t) => str_contains(strtolower($t['keterangan']), strtolower($s)) ||
                str_contains(strtolower($t['kategori_nama']), strtolower($s))
            )->values();
        }

        if ($request->kategori) {
            $transactions = $transactions->where('kategori', $request->kategori)->values();
        }

        return view('dashboard.ketua.keuangan', compact('totalPemasukan', 'totalPengeluaran', 'saldo', 'transactions'));
    }

    public function aset()
    {
        $barang = StokBarang::with('kategoriBarang')->latest()->get();
        $inventaris = AsetKendaraan::with('kategoriAset')->latest()->get();

        return view('dashboard.ketua.aset', compact('barang', 'inventaris'));
    }

    public function log(Request $request)
    {
        $search = $request->search;
        $filterRole = $request->role;
        $filterModul = $request->modul;
        $filterWaktu = $request->waktu;

        // Apply filters at DB level for both sources
        $applyFilters = function ($query, $table) use ($search, $filterRole, $filterModul, $filterWaktu) {
            $query->join('users', 'users.id', '=', "{$table}.user_id");

            if ($search) {
                $query->where(function ($w) use ($search, $table) {
                    $w->where("{$table}.deskripsi", 'like', "%{$search}%")
                      ->orWhere('users.nama', 'like', "%{$search}%")
                      ->orWhere("{$table}.modul", 'like', "%{$search}%");
                });
            }

            if ($filterRole) {
                $query->where('users.role', $filterRole);
            }

            if ($filterModul) {
                $query->where("{$table}.modul", $filterModul);
            }

            if ($filterWaktu) {
                $query->where(function ($w) use ($filterWaktu) {
                    $w->when($filterWaktu === 'hari', fn ($q) => $q->whereDate('created_at', now()->toDateString()))
                      ->when($filterWaktu === 'kemarin', fn ($q) => $q->whereDate('created_at', now()->subDay()->toDateString()))
                      ->when($filterWaktu === '7_hari', fn ($q) => $q->where('created_at', '>=', now()->subDays(7)))
                      ->when($filterWaktu === '30_hari', fn ($q) => $q->where('created_at', '>=', now()->subDays(30)));
                });
            }
        };

        // Query log_aktivitas with filters
        $queryAktivitas = \Illuminate\Support\Facades\DB::table('log_aktivitas')
            ->select('log_aktivitas.*', 'users.nama as user_nama', 'users.role as user_role')
            ->selectRaw("'aktivitas' as source");
        $applyFilters($queryAktivitas, 'log_aktivitas');
        $logAktivitas = collect($queryAktivitas->get());

        // Query log_superadmin with filters
        $querySuperadmin = \Illuminate\Support\Facades\DB::table('log_superadmin')
            ->select('log_superadmin.*', 'users.nama as user_nama', 'users.role as user_role')
            ->selectRaw("'superadmin' as source");
        $applyFilters($querySuperadmin, 'log_superadmin');
        $logSuperadmin = collect($querySuperadmin->get());

        // Merge, parse dates, and sort
        $merged = $logAktivitas->merge($logSuperadmin)
            ->map(fn ($l) => tap($l, fn ($o) => $o->created_at = \Carbon\Carbon::parse($o->created_at)))
            ->sortByDesc('created_at')
            ->values();

        // Paginate
        $page = (int) $request->get('page', 1);
        $perPage = 20;
        $total = $merged->count();
        $logs = $merged->slice(($page - 1) * $perPage, $perPage)->values();

        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            $logs,
            $total,
            $perPage,
            $page,
            [
                'path' => route('ketua.log'),
                'pageName' => 'page',
                'query' => $request->query(),
            ]
        );

        // Modul list from unfiltered data
        $modulList = \Illuminate\Support\Facades\DB::table('log_aktivitas')
            ->select('modul')
            ->union(\Illuminate\Support\Facades\DB::table('log_superadmin')->select('modul'))
            ->pluck('modul')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('dashboard.ketua.log', compact('logs', 'modulList'));
    }

    public function izin()
    {
        $izinData = PermintaanIzin::with('user')->latest()->get();

        return view('dashboard.ketua.izin', compact('izinData'));
    }

    public function approveIzin($id)
    {
        $izin = PermintaanIzin::findOrFail($id);
        if ($izin->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Permintaan sudah diproses.'], 400);
        }

        $target = $izin->target_table === 'pemasukan'
            ? Pemasukan::findOrFail($izin->target_id)
            : Pengeluaran::findOrFail($izin->target_id);

        $column = match ($izin->field_name) {
            'nominal' => 'jumlah',
            'keterangan' => 'keterangan',
            'kategori' => $izin->target_table === 'pemasukan' ? 'kategori_pemasukan_id' : 'kategori_pengeluaran_id',
            default => null,
        };

        if (! $column) {
            return response()->json(['success' => false, 'message' => 'Field tidak dikenal.'], 400);
        }

        if ($izin->field_name === 'kategori') {
            $kategoriModel = $izin->target_table === 'pemasukan' ? KategoriPemasukan::class : KategoriPengeluaran::class;
            $kategori = $kategoriModel::where('nama', $izin->new_value)->first();
            if (! $kategori) {
                return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan.'], 400);
            }
            $target->{$column} = $kategori->id;
        } elseif ($izin->field_name === 'nominal') {
            $target->{$column} = preg_replace('/[^0-9]/', '', $izin->new_value);
        } else {
            $target->{$column} = $izin->new_value;
        }

        $target->save();

        $izin->update([
            'status' => 'disetujui',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aksi' => 'setujui_izin_edit',
            'deskripsi' => "Menyetujui perubahan {$izin->field_name} pada {$izin->target_table}#{$izin->target_id}: {$izin->old_value} \u2192 {$izin->new_value}",
            'modul' => 'permintaan_izin',
            'referensi_id' => $izin->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Permintaan berhasil disetujui dan data telah diperbarui.']);
    }

    public function rejectIzin($id)
    {
        $izin = PermintaanIzin::findOrFail($id);
        if ($izin->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Permintaan sudah diproses.'], 400);
        }

        $izin->update([
            'status' => 'ditolak',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Permintaan ditolak.']);
    }

    public function storeTandaTangan(Request $request)
    {
        $request->validate([
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'foto_data_url' => 'nullable|string',
        ], [
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'foto.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        $source = null;
        $tempPath = null;

        if ($request->hasFile('foto')) {
            $source = $request->file('foto')->getRealPath();
        } elseif ($request->filled('foto_data_url')) {
            if (! preg_match('/^data:image\/(jpeg|png|webp);base64,(.+)$/s', $request->input('foto_data_url'), $m)) {
                return response()->json(['success' => false, 'message' => 'Data foto tidak valid.'], 422);
            }
            $tempPath = tempnam(sys_get_temp_dir(), 'ttd').'.png';
            file_put_contents($tempPath, base64_decode($m[2]));
            $source = $tempPath;
        } else {
            return response()->json(['success' => false, 'message' => 'Pilih atau ambil foto tanda tangan terlebih dahulu.'], 422);
        }

        $path = $this->processSignatureImage($source);
        if ($tempPath) {
            @unlink($tempPath);
        }

        if (! $path) {
            return response()->json(['success' => false, 'message' => 'Gagal memproses gambar. Coba foto lain.'], 422);
        }

        $user = $request->user();
        $user->tanda_tangan = $path;
        $user->save();

        LogAktivitas::create([
            'user_id' => $user->id,
            'aksi' => 'kirim_tanda_tangan',
            'deskripsi' => 'Ketua mengirim tanda tangan.',
            'modul' => 'tanda_tangan',
            'referensi_id' => $user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Tanda tangan berhasil disimpan.']);
    }

    private function processSignatureImage(string $sourcePath): ?string
    {
        if (! is_readable($sourcePath) || ! function_exists('imagecreatefromjpeg')) {
            return null;
        }

        $mime = mime_content_type($sourcePath);
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default => null,
        };

        if (! $image) {
            return null;
        }

        $w = imagesx($image);
        $h = imagesy($image);

        $maxH = 300;
        if ($h > $maxH) {
            $nw = max(1, (int) round($w * $maxH / $h));
            $resized = imagecreatetruecolor($nw, $maxH);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $nw, $maxH, $w, $h);
            $image = $resized;
            $w = $nw;
            $h = $maxH;
        }

        imagefilter($image, IMG_FILTER_GRAYSCALE);

        $threshold = 180;
        $white = imagecolorallocate($image, 255, 255, 255);
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $lum = (int) round(0.299 * $r + 0.587 * $g + 0.114 * $b);
                if ($lum > $threshold) {
                    imagesetpixel($image, $x, $y, $white);
                }
            }
        }

        imagefilter($image, IMG_FILTER_CONTRAST, -35);

        $dir = storage_path('app/public/tanda-tangan');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $filename = 'tanda-tangan/'.auth()->id().'.png';
        $outPath = storage_path('app/public/'.$filename);
        imagepng($image, $outPath);

        return $filename;
    }
}
