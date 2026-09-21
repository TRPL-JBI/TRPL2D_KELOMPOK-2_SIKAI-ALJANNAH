<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CalonAnggota extends Model
{
    protected $table = 'calon_anggota';

    protected $fillable = [
        'nama',
        'email',
        'telepon',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'rt_rw',
        'kelurahan',
        'status',
        'anggota_id',
        'alasan_penolakan',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function keluargaAnggota(): HasMany
    {
        return $this->hasMany(KeluargaAnggota::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
