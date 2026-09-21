<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('calon_anggota', function (Blueprint $table) {
            $table->enum('status', ['menunggu_verifikasi', 'dalam_pembayaran', 'sudah_membayar', 'disetujui', 'ditolak'])->default('menunggu_verifikasi')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calon_anggota', function (Blueprint $table) {
            $table->enum('status', ['menunggu_verifikasi', 'sudah_membayar', 'disetujui', 'ditolak'])->default('menunggu_verifikasi')->change();
        });
    }
};
