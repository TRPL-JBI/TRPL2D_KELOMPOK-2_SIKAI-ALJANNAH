<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Pengeluaran extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'pengeluaran';

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected $fillable = [
        'tanggal',
        'kategori_pengeluaran_id',
        'jumlah',
        'keterangan',
        'created_by',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('bukti')->singleFile();
    }

    public function kategoriPengeluaran(): BelongsTo
    {
        return $this->belongsTo(KategoriPengeluaran::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
