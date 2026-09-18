<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Pemasukan extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'pemasukan';

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected $fillable = [
        'tanggal',
        'kategori_pemasukan_id',
        'jumlah',
        'keterangan',
        'created_by',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('bukti')->singleFile();
    }

    public function kategoriPemasukan(): BelongsTo
    {
        return $this->belongsTo(KategoriPemasukan::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function iuranTahunan(): HasMany
    {
        return $this->hasMany(IuranTahunan::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
