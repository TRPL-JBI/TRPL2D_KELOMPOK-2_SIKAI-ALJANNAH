<?php

namespace App\Http\Controllers;

use App\Models\CalonAnggota;
use App\Models\KeluargaAnggota;
use Illuminate\Http\Request;
use App\Models\LogSuperadmin;

class PendaftaranController extends Controller
{
    public function create()
    {
        return view('public.pendaftaran');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:laki_laki,perempuan',
            'alamat' => 'required|string|max:1000',
            'rt_rw' => 'required|string|max:20',
            'kelurahan' => 'required|string|max:255',
            'nomor_hp' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:calon_anggota,email',
            'nama_ditanggung' => 'nullable|array',
            'nama_ditanggung.*' => 'nullable|string|max:255',
            'terms' => 'accepted',
        ]);

        $calon = CalonAnggota::create([
            'nama' => $validated['nama_lengkap'],
            'email' => $validated['email'],
            'telepon' => $validated['nomor_hp'],
            'alamat' => $validated['alamat'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'] === 'laki_laki' ? 'laki-laki' : 'perempuan',
            'rt_rw' => $validated['rt_rw'],
            'kelurahan' => $validated['kelurahan'],
            'status' => 'menunggu_verifikasi',
        ]);

        LogSuperadmin::create([
            'user_id' => null,
            'aksi' => 'create',
            'deskripsi' => 'Pendaftaran anggota baru: ' . $validated['nama_lengkap'],
            'modul' => 'pendaftaran',
            'referensi_id' => $calon->id,
            'ip_address' => request()->ip(),
        ]);

        if (!empty($validated['nama_ditanggung'])) {
            foreach ($validated['nama_ditanggung'] as $nama) {
                if (blank($nama)) continue;

                KeluargaAnggota::create([
                    'calon_anggota_id' => $calon->id,
                    'nama' => $nama,
                    'jenis_kelamin' => $validated['jenis_kelamin'] === 'laki_laki' ? 'laki-laki' : 'perempuan',
                    'tanggal_lahir' => $validated['tanggal_lahir'],
                ]);
            }
        }

        return redirect('/daftar')->with('success', 'Pendaftaran berhasil! Data Anda akan diverifikasi oleh sekretaris.');
    }
}
