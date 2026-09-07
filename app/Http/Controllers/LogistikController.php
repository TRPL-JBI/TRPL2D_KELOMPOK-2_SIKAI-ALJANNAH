<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StokBarang;
use App\Models\KategoriBarang;
use App\Models\RiwayatBarang;
use App\Models\AsetKendaraan;
use App\Models\KategoriAset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\LogSuperadmin;

class LogistikController extends Controller
{
    public function index()
    {
        $totalBarang = StokBarang::count();

        $barangMasukHariIni = RiwayatBarang::whereDate('waktu', today())
            ->where('tipe', 'masuk')
            ->sum('jumlah');

        $barangKeluarHariIni = RiwayatBarang::whereDate('waktu', today())
            ->where('tipe', 'keluar')
            ->sum('jumlah');

        $totalStokMenipis = StokBarang::where('stok', '<', 10)->count();

        $stokMenipis = StokBarang::with('kategoriBarang')
            ->where('stok', '<', 10)
            ->get();

        return view('dashboard.logistik.index', compact(
            'totalBarang',
            'barangMasukHariIni',
            'barangKeluarHariIni',
            'totalStokMenipis',
            'stokMenipis'
        ));
    }

    public function stok(Request $request)
    {
        $query = StokBarang::with('kategoriBarang');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                  ->orWhere('nama_barang', 'like', "%{$search}%")
                  ->orWhere('satuan', 'like', "%{$search}%")
                  ->orWhereHas('kategoriBarang', function ($q) use ($search) {
                      $q->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $barang = $query->get();
        $kategoris = KategoriBarang::all();
        return view('dashboard.logistik.stok', compact('barang', 'kategoris'));
    }

    public function storeBarang(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|unique:stok_barang,kode_barang',
            'nama_barang' => 'required',
            'kategori_barang_id' => 'required|exists:kategori_barang,id',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required',
        ]);

        $validated['stok_minimum'] = 0;
        $validated['status'] = $validated['stok'] > 0 ? 'tersedia' : 'habis';

        StokBarang::create($validated);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'deskripsi' => 'Menambahkan barang baru: ' . $validated['nama_barang'] . ' (' . $validated['kode_barang'] . ')',
            'modul' => 'logistik',
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('logistik.stok')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function updateBarang(Request $request, $id)
    {
        $barang = StokBarang::findOrFail($id);

        $validated = $request->validate([
            'kode_barang' => 'required|unique:stok_barang,kode_barang,' . $id,
            'nama_barang' => 'required',
            'kategori_barang_id' => 'required|exists:kategori_barang,id',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required',
            'stok_minimum' => 'nullable|integer|min:0',
        ]);

        $validated['status'] = (int) $validated['stok'] > 0 ? 'tersedia' : 'habis';
        $validated['stok_minimum'] = $validated['stok_minimum'] ?? 0;

        $barang->update($validated);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'update',
            'deskripsi' => 'Memperbarui barang: ' . $barang->nama_barang . ' (' . $barang->kode_barang . ')',
            'modul' => 'logistik',
            'referensi_id' => $barang->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('logistik.stok')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroyBarang($id)
    {
        $barang = StokBarang::findOrFail($id);
        
        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'delete',
            'deskripsi' => 'Menghapus barang: ' . $barang->nama_barang . ' (' . $barang->kode_barang . ')',
            'modul' => 'logistik',
            'referensi_id' => $barang->id,
            'ip_address' => request()->ip(),
        ]);
        
        $barang->delete();

        return redirect()->route('logistik.stok')->with('success', 'Barang berhasil dihapus.');
    }

    public function storeBarangMasuk(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:stok_barang,id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $barang = StokBarang::findOrFail($validated['barang_id']);
            $newStok = $barang->stok + $validated['jumlah'];

            $barang->update([
                'stok' => $newStok,
                'status' => 'tersedia',
            ]);

            RiwayatBarang::create([
                'waktu' => now(),
                'tipe' => 'masuk',
                'tipe_referensi' => 'stok_barang',
                'referensi_id' => $barang->id,
                'jumlah' => $validated['jumlah'],
                'keterangan' => $validated['keterangan'],
                'user_id' => Auth::id(),
            ]);

            LogSuperadmin::create([
                'user_id' => auth()->id(),
                'aksi' => 'create',
                'deskripsi' => 'Barang masuk: ' . $barang->nama_barang . ' +' . $validated['jumlah'] . ' ' . $barang->satuan,
                'modul' => 'logistik',
                'referensi_id' => $barang->id,
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->route('logistik.stok')->with('success', 'Stok berhasil ditambahkan.');
    }

    public function storeBarangKeluar(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:stok_barang,id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        $barang = StokBarang::findOrFail($validated['barang_id']);

        if ($barang->stok < $validated['jumlah']) {
            return redirect()->back()
                ->with('error', 'Jumlah barang keluar tidak boleh melebihi stok yang tersedia. Stok tersedia: ' . $barang->stok)
                ->withInput();
        }

        DB::transaction(function () use ($validated, $barang) {
            $newStok = $barang->stok - $validated['jumlah'];

            $barang->update([
                'stok' => $newStok,
                'status' => $newStok <= 0 ? 'habis' : 'tersedia',
            ]);

            RiwayatBarang::create([
                'waktu' => now(),
                'tipe' => 'keluar',
                'tipe_referensi' => 'stok_barang',
                'referensi_id' => $barang->id,
                'jumlah' => $validated['jumlah'],
                'keterangan' => $validated['keterangan'],
                'user_id' => Auth::id(),
            ]);

            LogSuperadmin::create([
                'user_id' => auth()->id(),
                'aksi' => 'update',
                'deskripsi' => 'Barang keluar: ' . $barang->nama_barang . ' -' . $validated['jumlah'] . ' ' . $barang->satuan,
                'modul' => 'logistik',
                'referensi_id' => $barang->id,
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->route('logistik.stok')->with('success', 'Stok berhasil dikurangi.');
    }

    public function aset()
    {
        $aset = AsetKendaraan::with('kategoriAset')->get();
        $kategoris = KategoriAset::all();
        return view('dashboard.logistik.aset', compact('aset', 'kategoris'));
    }

    public function storeAset(Request $request)
    {
        $validated = $request->validate([
            'kode_aset' => 'required|unique:aset_kendaraan,kode_aset',
            'nama_aset' => 'required',
            'nomor_plat_seri' => 'nullable',
            'kategori_aset_id' => 'required|exists:kategori_aset,id',
            'kondisi' => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'tersedia';

        AsetKendaraan::create($validated);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'deskripsi' => 'Menambahkan aset baru: ' . $validated['nama_aset'] . ' (' . $validated['kode_aset'] . ')',
            'modul' => 'logistik',
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('logistik.aset')->with('success', 'Aset berhasil ditambahkan.');
    }

    public function updateAset(Request $request, $id)
    {
        $aset = AsetKendaraan::findOrFail($id);

        $validated = $request->validate([
            'kode_aset' => 'required|unique:aset_kendaraan,kode_aset,' . $id,
            'nama_aset' => 'required',
            'nomor_plat_seri' => 'nullable',
            'kategori_aset_id' => 'required|exists:kategori_aset,id',
            'kondisi' => 'nullable|string|max:255',
        ]);

        $aset->update($validated);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'update',
            'deskripsi' => 'Memperbarui aset: ' . $aset->nama_aset . ' (' . $aset->kode_aset . ')',
            'modul' => 'logistik',
            'referensi_id' => $aset->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('logistik.aset')->with('success', 'Aset berhasil diperbarui.');
    }

    public function destroyAset($id)
    {
        $aset = AsetKendaraan::findOrFail($id);
        
        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'delete',
            'deskripsi' => 'Menghapus aset: ' . $aset->nama_aset . ' (' . $aset->kode_aset . ')',
            'modul' => 'logistik',
            'referensi_id' => $aset->id,
            'ip_address' => request()->ip(),
        ]);
        
        $aset->delete();

        return redirect()->route('logistik.aset')->with('success', 'Aset berhasil dihapus.');
    }

    public function updateStatusAset(Request $request, $id)
    {
        $aset = AsetKendaraan::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:tersedia,dipinjam,rusak,dihapus',
            'kondisi' => 'nullable|string|max:255',
        ]);

        $oldStatus = $aset->status;
        $newStatus = $validated['status'];

        $aset->update($validated);

        $tipeRiwayat = match ($newStatus) {
            'dipinjam' => 'dipinjam',
            'tersedia' => $oldStatus === 'dipinjam' ? 'dikembalikan' : 'masuk',
            'rusak', 'dihapus' => 'keluar',
        };

        $labelStatus = [
            'tersedia' => 'Tersedia',
            'dipinjam' => 'Dipinjam',
            'rusak' => 'Rusak',
            'dihapus' => 'Dihapus',
        ];

        RiwayatBarang::create([
            'waktu' => now(),
            'tipe' => $tipeRiwayat,
            'tipe_referensi' => 'aset_kendaraan',
            'referensi_id' => $aset->id,
            'jumlah' => 1,
            'keterangan' => $labelStatus[$oldStatus] . ' → ' . $labelStatus[$newStatus],
            'user_id' => Auth::id(),
        ]);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'update',
            'deskripsi' => 'Update status aset: ' . $aset->nama_aset . ' dari ' . $labelStatus[$oldStatus] . ' ke ' . $labelStatus[$newStatus],
            'modul' => 'logistik',
            'referensi_id' => $aset->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('logistik.aset')->with('success', 'Status aset berhasil diperbarui.');
    }

    public function riwayat()
    {
        $riwayat = DB::table('riwayat_barang')
            ->leftJoin('stok_barang', function ($join) {
                $join->on('riwayat_barang.referensi_id', '=', 'stok_barang.id')
                    ->where('riwayat_barang.tipe_referensi', '=', 'stok_barang');
            })
            ->leftJoin('aset_kendaraan', function ($join) {
                $join->on('riwayat_barang.referensi_id', '=', 'aset_kendaraan.id')
                    ->where('riwayat_barang.tipe_referensi', '=', 'aset_kendaraan');
            })
            ->select(
                'riwayat_barang.*',
                'stok_barang.nama_barang',
                'stok_barang.kode_barang',
                'aset_kendaraan.nama_aset',
                'aset_kendaraan.kode_aset'
            )
            ->orderBy('riwayat_barang.waktu', 'desc')
            ->get();

        return view('dashboard.logistik.riwayat', compact('riwayat'));
    }
}
