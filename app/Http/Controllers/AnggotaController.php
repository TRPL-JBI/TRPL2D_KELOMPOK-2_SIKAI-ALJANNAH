<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;
use App\Models\LogSuperadmin;

class AnggotaController extends Controller
{
    public function showAccessCode()
    {
        return view('public.anggota');
    }

    public function verifyAccessCode(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string|max:50',
        ]);

        $anggota = Anggota::where('access_code', $request->access_code)
            ->where('status', 'aktif')
            ->first();

        if (!$anggota) {
            return back()->withErrors(['access_code' => 'Kode akses tidak valid.'])->withInput();
        }

        if (!$anggota->access_code_generated_at || \Carbon\Carbon::parse($anggota->access_code_generated_at)->addDay()->isPast()) {
            $anggota->access_code = null;
            $anggota->access_code_generated_at = null;
            $anggota->save();
            return back()->withErrors(['access_code' => 'Kode akses sudah kedaluwarsa (1 x 24 jam). Silakan hubungi bendahara untuk generate ulang.'])->withInput();
        }

        session(['anggota_id' => $anggota->id]);

        LogSuperadmin::create([
            'user_id' => null,
            'aksi' => 'login',
            'deskripsi' => 'Anggota login: ' . $anggota->nama . ' (kode akses)',
            'modul' => 'anggota',
            'referensi_id' => $anggota->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('anggota.dashboard');
    }

    public function dashboard(Request $request)
    {
        $anggotaId = session('anggota_id');
        if (!$anggotaId) {
            return redirect()->route('anggota');
        }

        $anggota = Anggota::with('iuranTahunan')->findOrFail($anggotaId);

        $tahunMulai = $anggota->tanggal_aktif_kembali
            ? (int) \Carbon\Carbon::parse($anggota->tanggal_aktif_kembali)->format('Y')
            : (int) $anggota->created_at->format('Y');
        $tahunMulai = max(2025, $tahunMulai);

        $tahunDipilih = (int) $request->input('tahun', date('Y'));
        $tahunDipilih = max($tahunMulai, min($tahunDipilih, date('Y')));

        $nominalPerBulan = 10000;

        $iuranBulanan = [];
        $totalLunas = 0;
        $totalBelum = 0;

        for ($b = 1; $b <= 12; $b++) {
            $iuran = $anggota->iuranTahunan
                ->where('tahun', $tahunDipilih)
                ->where('bulan', $b)
                ->first();

            $lunas = $iuran && $iuran->status === 'lunas';
            if ($lunas) $totalLunas++;
            else $totalBelum++;

            $iuranBulanan[] = [
                'bulan' => $b,
                'nama_bulan' => [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                    10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ][$b],
                'lunas' => $lunas,
                'nominal' => $nominalPerBulan,
                'tanggal_bayar' => $iuran?->tanggal_bayar,
            ];
        }

        $totalHarusDibayar = $totalBelum * $nominalPerBulan;
        $totalSudahDibayar = $totalLunas * $nominalPerBulan;
        $progressPersen = $totalLunas / 12 * 100;

        $daftarTahun = range($tahunMulai, date('Y'));

        return view('anggota.dashboard', compact(
            'anggota', 'tahunDipilih', 'tahunMulai', 'daftarTahun',
            'iuranBulanan', 'totalLunas', 'totalBelum',
            'totalHarusDibayar', 'totalSudahDibayar',
            'nominalPerBulan', 'progressPersen'
        ));
    }
}
