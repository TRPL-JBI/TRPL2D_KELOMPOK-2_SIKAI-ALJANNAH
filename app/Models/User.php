<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'status',
        'tanda_tangan',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function ketua(): ?User
    {
        return self::where('role', 'ketua')->where('status', 'aktif')->first();
    }

    public function tandaTanganDataUri(): ?string
    {
        if (blank($this->tanda_tangan)) {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($this->tanda_tangan)) {
            return null;
        }

        $mime = $disk->mimeType($this->tanda_tangan) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($disk->get($this->tanda_tangan));
    }

    public function pemasukan(): HasMany
    {
        return $this->hasMany(Pemasukan::class, 'created_by');
    }

    public function pengeluaran(): HasMany
    {
        return $this->hasMany(Pengeluaran::class, 'created_by');
    }

    public function anggota(): HasMany
    {
        return $this->hasMany(Anggota::class, 'created_by');
    }

    public function iuranVerifikasi(): HasMany
    {
        return $this->hasMany(IuranTahunan::class, 'verified_by');
    }

    public function pembayaranVerifikasi(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'verified_by');
    }

    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class);
    }

    public function riwayatBarang(): HasMany
    {
        return $this->hasMany(RiwayatBarang::class, 'user_id');
    }

    public function permintaanIzin(): HasMany
    {
        return $this->hasMany(PermintaanIzin::class, 'user_id');
    }

    public function approvedPermintaan(): HasMany
    {
        return $this->hasMany(PermintaanIzin::class, 'approved_by');
    }

    public function fileOrganisasi(): HasMany
    {
        return $this->hasMany(FileOrganisasi::class, 'uploaded_by');
    }

    public function logSuperadmin(): HasMany
    {
        return $this->hasMany(LogSuperadmin::class, 'user_id');
    }
}
