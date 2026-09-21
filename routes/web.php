<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SekretarisController;
use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\LogistikController;
use App\Http\Controllers\EditorUploadController;
use App\Http\Controllers\BendaharaController;
use App\Http\Controllers\KetuaController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\WhatsAppController;

use App\Models\Page;

Route::get('/', function () {
    $posts = App\Models\Post::with(['category', 'legacyMedia'])
        ->where('status', 'published')
        ->latest('published_at')
        ->get();

    $page = Page::where('slug', 'beranda')->where('status', 'published')->first();
    $content = $page ? json_decode($page->content, true) : [];

    return view('public.index', compact('posts', 'content'));
})->name('home');

Route::get('/berita/{slug}', function ($slug) {
    $post = App\Models\Post::with(['category', 'legacyMedia'])
        ->where('slug', $slug)
        ->where('status', 'published')
        ->firstOrFail();

    $relatedPosts = App\Models\Post::with('legacyMedia')
        ->where('id', '!=', $post->id)
        ->where('status', 'published')
        ->latest('published_at')
        ->take(3)
        ->get();

    return view('public.berita-detail', compact('post', 'relatedPosts'));
})->name('post.show');

Route::get('/page/{slug}', function ($slug) {
    $page = App\Models\Page::where('slug', $slug)->where('status', 'published')->firstOrFail();
    return view('public.page-detail', compact('page'));
})->name('page.show');

Route::get('/daftar', [PendaftaranController::class, 'create'])->name('register');
Route::post('/register-member', [PendaftaranController::class, 'store'])->name('register-member.store');

Route::prefix('anggota')->group(function () {
    Route::get('/', [App\Http\Controllers\AnggotaController::class, 'showAccessCode'])->name('anggota');
    Route::post('/', [App\Http\Controllers\AnggotaController::class, 'verifyAccessCode']);
    Route::get('/dashboard', [App\Http\Controllers\AnggotaController::class, 'dashboard'])->name('anggota.dashboard');
    Route::post('/logout', function () {
        session()->forget('anggota_id');
        return redirect()->route('anggota');
    })->name('anggota.logout');
});

Route::middleware(['auth', 'role:sekretaris,superadmin'])->group(function () {
    Route::get('/sekretaris', [SekretarisController::class, 'index'])->name('sekretaris.dashboard');
    Route::post('/sekretaris/{id}/verifikasi', [SekretarisController::class, 'verifikasi'])->name('sekretaris.verifikasi');

    Route::get('/sekretaris/anggota', [SekretarisController::class, 'anggota'])->name('sekretaris.anggota');
    Route::post('/sekretaris/anggota/import', [SekretarisController::class, 'importAnggota'])->name('sekretaris.anggota.import');
    Route::get('/sekretaris/anggota/template', [SekretarisController::class, 'downloadTemplateAnggota'])->name('sekretaris.anggota.template');

    Route::get('/sekretaris/log', [SekretarisController::class, 'log'])->name('sekretaris.log');

    Route::get('/sekretaris/formulir/{id}', [SekretarisController::class, 'formulirCalon'])->name('sekretaris.formulir');
    Route::get('/sekretaris/formulir/{id}/pdf', [SekretarisController::class, 'formulirCalonPdf'])->name('sekretaris.formulir.pdf');
    Route::get('/sekretaris/anggota/{id}/formulir', [SekretarisController::class, 'formulirAnggota'])->name('sekretaris.anggota.formulir');
    Route::get('/sekretaris/anggota/{id}/formulir/pdf', [SekretarisController::class, 'formulirAnggotaPdf'])->name('sekretaris.anggota.formulir.pdf');

    Route::get('/sekretaris/anggota/{id}/edit', [SekretarisController::class, 'editAnggota'])->name('sekretaris.anggota.edit');
    Route::post('/sekretaris/anggota/{id}/edit', [SekretarisController::class, 'prosesEditAnggota'])->name('sekretaris.anggota.proses-edit');

    Route::get('/sekretaris/anggota/{id}/nonaktif', [SekretarisController::class, 'nonaktifAnggota'])->name('sekretaris.anggota.nonaktif');
    Route::post('/sekretaris/anggota/{id}/nonaktif', [SekretarisController::class, 'prosesNonaktifAnggota'])->name('sekretaris.anggota.proses-nonaktif');
    Route::post('/sekretaris/anggota/{id}/aktifkan', [SekretarisController::class, 'prosesAktifkanAnggota'])->name('sekretaris.anggota.proses-aktifkan');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('forgot-password.send');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.do');
});

Route::middleware(['auth', 'role:logistik,superadmin'])->group(function () {
    Route::get('/logistik', [LogistikController::class, 'index'])->name('logistik.dashboard');

    Route::get('/logistik/stok', [LogistikController::class, 'stok'])->name('logistik.stok');
    Route::post('/logistik/stok', [LogistikController::class, 'storeBarang'])->name('logistik.stok.store');
    Route::put('/logistik/stok/{id}', [LogistikController::class, 'updateBarang'])->name('logistik.stok.update');
    Route::delete('/logistik/stok/{id}', [LogistikController::class, 'destroyBarang'])->name('logistik.stok.destroy');
    Route::post('/logistik/stok/masuk', [LogistikController::class, 'storeBarangMasuk'])->name('logistik.stok.masuk');
    Route::post('/logistik/stok/keluar', [LogistikController::class, 'storeBarangKeluar'])->name('logistik.stok.keluar');

    Route::get('/logistik/aset', [LogistikController::class, 'aset'])->name('logistik.aset');
    Route::post('/logistik/aset', [LogistikController::class, 'storeAset'])->name('logistik.aset.store');
    Route::put('/logistik/aset/{id}', [LogistikController::class, 'updateAset'])->name('logistik.aset.update');
    Route::delete('/logistik/aset/{id}', [LogistikController::class, 'destroyAset'])->name('logistik.aset.destroy');
    Route::put('/logistik/aset/status/{id}', [LogistikController::class, 'updateStatusAset'])->name('logistik.aset.status');

    Route::get('/logistik/riwayat', [LogistikController::class, 'riwayat'])->name('logistik.riwayat');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/editor/upload', [EditorUploadController::class, 'store'])->name('editor.upload');
});

Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/superadmin', [SuperAdminController::class, 'index'])->name('superadmin.dashboard');
    Route::get('/superadmin/user', [SuperAdminController::class, 'userIndex'])->name('superadmin.user');
    Route::get('/superadmin/log', [SuperAdminController::class, 'logIndex'])->name('superadmin.log');
    Route::get('/superadmin/file', [SuperAdminController::class, 'fileIndex'])->name('superadmin.file');
    Route::post('/superadmin/file/upload', [SuperAdminController::class, 'uploadFile'])->name('superadmin.file.upload');
    Route::get('/superadmin/file/{id}/download', [SuperAdminController::class, 'downloadFile'])->name('superadmin.file.download');
    Route::get('/superadmin/file/trash', [SuperAdminController::class, 'trashFiles'])->name('superadmin.file.trash');
    Route::post('/superadmin/file/{id}/restore', [SuperAdminController::class, 'restoreFile'])->name('superadmin.file.restore');
    Route::put('/superadmin/user/{id}', [SuperAdminController::class, 'updateUser'])->name('superadmin.user.update');
    Route::post('/superadmin/user', [SuperAdminController::class, 'storeUser'])->name('superadmin.user.store');
});

Route::middleware(['auth', 'role:bendahara,superadmin'])->group(function () {
    Route::get('/bendahara', [BendaharaController::class, 'index'])->name('bendahara.dashboard');

    Route::get('/bendahara/pemasukan', [BendaharaController::class, 'pemasukan'])->name('bendahara.pemasukan');
    Route::post('/bendahara/pemasukan', [BendaharaController::class, 'storePemasukan'])->name('bendahara.pemasukan.store');

    Route::get('/bendahara/pengeluaran', [BendaharaController::class, 'pengeluaran'])->name('bendahara.pengeluaran');
    Route::post('/bendahara/pengeluaran', [BendaharaController::class, 'storePengeluaran'])->name('bendahara.pengeluaran.store');

    Route::get('/bendahara/iuran', [BendaharaController::class, 'iuran'])->name('bendahara.iuran');
    Route::get('/bendahara/iuran/data', [BendaharaController::class, 'getIuranData'])->name('bendahara.iuran.data');
    Route::post('/bendahara/iuran', [BendaharaController::class, 'storeIuran'])->name('bendahara.iuran.store');
    Route::post('/bendahara/iuran/kirim-tagihan', [BendaharaController::class, 'kirimTagihanIuran'])->name('bendahara.iuran.kirim-tagihan');
    Route::post('/bendahara/iuran/kirim-kwitansi', [BendaharaController::class, 'kirimKwitansiIuran'])->name('bendahara.iuran.kirim-kwitansi');

    Route::get('/bendahara/laporan', [BendaharaController::class, 'laporan'])->name('bendahara.laporan');
    Route::get('/bendahara/laporan/data', [BendaharaController::class, 'getLaporanData'])->name('bendahara.laporan.data');
    Route::get('/bendahara/laporan/export', [BendaharaController::class, 'exportLaporan'])->name('bendahara.laporan.export');

    Route::get('/bendahara/verifikasi', [BendaharaController::class, 'verifikasi'])->name('bendahara.verifikasi');
    Route::get('/bendahara/verifikasi/data', [BendaharaController::class, 'getVerifikasiData'])->name('bendahara.verifikasi.data');
    Route::post('/bendahara/verifikasi/{id}', [BendaharaController::class, 'verifikasiPembayaran'])->name('bendahara.verifikasi.store');

    Route::get('/bendahara/catat-transaksi', [BendaharaController::class, 'catatTransaksi'])->name('bendahara.catat-transaksi');
    Route::post('/bendahara/catat-transaksi', [BendaharaController::class, 'storeTransaksi'])->name('bendahara.catat-transaksi.store');
    Route::post('/bendahara/catat-transaksi/import', [BendaharaController::class, 'importTransaksi'])->name('bendahara.catat-transaksi.import');
    Route::get('/bendahara/catat-transaksi/template', [BendaharaController::class, 'downloadTemplateTransaksi'])->name('bendahara.catat-transaksi.template');
    Route::get('/bendahara/catat-transaksi/export', [BendaharaController::class, 'exportTransaksi'])->name('bendahara.catat-transaksi.export');

    Route::post('/bendahara/iuran/generate-access-code', [BendaharaController::class, 'generateAccessCode'])->name('bendahara.iuran.generate-access-code');
});

Route::middleware(['auth', 'role:ketua'])->group(function () {
    Route::get('/ketua', [KetuaController::class, 'index'])->name('ketua.dashboard');
    Route::get('/ketua/anggota', [KetuaController::class, 'anggota'])->name('ketua.anggota');
    Route::get('/ketua/keuangan', [KetuaController::class, 'keuangan'])->name('ketua.keuangan');
    Route::get('/ketua/aset', [KetuaController::class, 'aset'])->name('ketua.aset');
    Route::get('/ketua/log', [KetuaController::class, 'log'])->name('ketua.log');
    Route::get('/ketua/izin', [KetuaController::class, 'izin'])->name('ketua.izin');
    Route::post('/ketua/izin/{id}/approve', [KetuaController::class, 'approveIzin'])->name('ketua.izin.approve');
    Route::post('/ketua/izin/{id}/reject', [KetuaController::class, 'rejectIzin'])->name('ketua.izin.reject');
    Route::post('/ketua/tanda-tangan', [KetuaController::class, 'storeTandaTangan'])->name('ketua.tanda-tangan.store');
});

Route::middleware(['auth', 'role:adminweb'])->group(function () {
    Route::get('/adminweb', [AdminWebController::class, 'index'])->name('adminweb.dashboard');
    Route::get('/adminweb/posts', [AdminWebController::class, 'posts'])->name('adminweb.posts');
    Route::get('/adminweb/posts/buat', [AdminWebController::class, 'createPost'])->name('adminweb.posts.create');
    Route::get('/adminweb/posts/{id}/edit', [AdminWebController::class, 'editPost'])->name('adminweb.posts.edit');
    Route::put('/adminweb/posts/{id}/publish', [AdminWebController::class, 'publishPost'])->name('adminweb.posts.publish');
    Route::delete('/adminweb/posts/{id}', [AdminWebController::class, 'destroyPost'])->name('adminweb.posts.destroy');
    Route::get('/adminweb/posts/members', [AdminWebController::class, 'getMembers'])->name('adminweb.posts.members');
    Route::post('/adminweb/posts/{id}/share-whatsapp', [AdminWebController::class, 'shareWhatsApp'])->name('adminweb.posts.share-whatsapp');

    Route::post('/adminweb/kategori', [AdminWebController::class, 'storeCategory'])->name('adminweb.kategori.store');

    Route::get('/adminweb/pages', [AdminWebController::class, 'pages'])->name('adminweb.pages');
    Route::post('/adminweb/pages/buat', [AdminWebController::class, 'storePage'])->name('adminweb.pages.store');
    Route::get('/adminweb/pages/{id}/builder', [AdminWebController::class, 'builderPage'])->name('adminweb.pages.builder');
    Route::post('/adminweb/pages/{id}/builder/save', [AdminWebController::class, 'saveBuilder'])->name('adminweb.pages.builder.save');
    Route::get('/adminweb/pages/{id}/edit', [AdminWebController::class, 'editPage'])->name('adminweb.pages.edit');
    Route::put('/adminweb/pages/{id}', [AdminWebController::class, 'updatePage'])->name('adminweb.pages.update');
    Route::put('/adminweb/pages/{id}/publish', [AdminWebController::class, 'publishPage'])->name('adminweb.pages.publish');
    Route::delete('/adminweb/pages/{id}', [AdminWebController::class, 'destroyPage'])->name('adminweb.pages.destroy');

    Route::get('/adminweb/menus', [MenuController::class, 'index'])->name('adminweb.menus');
    Route::get('/adminweb/menus/create', [MenuController::class, 'create'])->name('adminweb.menus.create');
    Route::post('/adminweb/menus', [MenuController::class, 'store'])->name('adminweb.menus.store');
    Route::get('/adminweb/menus/{id}/edit', [MenuController::class, 'edit'])->name('adminweb.menus.edit');
    Route::put('/adminweb/menus/{id}', [MenuController::class, 'update'])->name('adminweb.menus.update');
    Route::delete('/adminweb/menus/{id}', [MenuController::class, 'destroy'])->name('adminweb.menus.destroy');
    Route::put('/adminweb/menus/{id}/toggle', [MenuController::class, 'toggle'])->name('adminweb.menus.toggle');

    Route::get('/adminweb/files', [AdminWebController::class, 'files'])->name('adminweb.files');
    Route::get('/adminweb/files/library', [AdminWebController::class, 'mediaLibrary'])->name('adminweb.files.library');
    Route::delete('/adminweb/files/{id}', [AdminWebController::class, 'destroyFile'])->name('adminweb.files.destroy');
    Route::post('/adminweb/files/{id}/restore', [AdminWebController::class, 'restoreFile'])->name('adminweb.files.restore');

    Route::get('/adminweb/whatsapp', [WhatsAppController::class, 'index'])->name('adminweb.whatsapp');
    Route::get('/adminweb/whatsapp/refresh', [WhatsAppController::class, 'refresh'])->name('adminweb.whatsapp.refresh');
    Route::post('/adminweb/whatsapp/connect-qr', [WhatsAppController::class, 'connectQr'])->name('adminweb.whatsapp.connect-qr');
    Route::post('/adminweb/whatsapp/send', [WhatsAppController::class, 'send'])->name('adminweb.whatsapp.send');
    Route::post('/adminweb/whatsapp/send-test-file', [WhatsAppController::class, 'sendTestFile'])->name('adminweb.whatsapp.send-test-file');
    Route::post('/adminweb/whatsapp/logout', [WhatsAppController::class, 'logout'])->name('adminweb.whatsapp.logout');
    Route::get('/adminweb/whatsapp/server/status', [WhatsAppController::class, 'serverStatusJson'])->name('adminweb.whatsapp.server-status');
    Route::post('/adminweb/whatsapp/server/start', [WhatsAppController::class, 'startServer'])->name('adminweb.whatsapp.server-start');
    Route::post('/adminweb/whatsapp/server/stop', [WhatsAppController::class, 'stopServer'])->name('adminweb.whatsapp.server-stop');
});

// Public file serving — zero-trust (download only)
Route::get('/file/{id}', [FileController::class, 'downloadFile'])->name('file.download');
Route::get('/media/{id}', [FileController::class, 'downloadMedia'])->name('media.download');
Route::get('/spatie-media/{id}', [FileController::class, 'serveSpatieMedia'])->name('media.spatie');

// Catch-all: intercept legacy /storage/* paths after symlink removal
Route::get('/storage/{path}', [FileController::class, 'serveStorage'])->where('path', '.*');
