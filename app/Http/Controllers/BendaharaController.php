<?php

namespace App\Http\Controllers;

use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\CalonAnggota;
use App\Models\Anggota;
use App\Models\IuranTahunan;
use App\Models\Pembayaran;
use App\Models\KategoriPemasukan;
use App\Models\KategoriPengeluaran;
use App\Models\PermintaanIzin;
use App\Services\KwitansiService;
use App\Services\WhatsAppService;
use App\Models\LogSuperadmin;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;

class BendaharaController extends Controller
{
    protected function wa(): WhatsAppService
    {
        return app(WhatsAppService::class);
    }

    protected function kwitansi(): KwitansiService
    {
        return app(KwitansiService::class);
    }

    protected function bulanNama(int $b): string
    {
        return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][$b - 1] ?? '';
    }

    protected function formatBulanRange(array $bulan): string
    {
        $nama = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $parts = [];
        $n = count($bulan);

        for ($i = 0; $i < $n; $i++) {
            $start = $bulan[$i];
            $end = $start;

            while ($i + 1 < $n && $bulan[$i + 1] === $bulan[$i] + 1) {
                $end = $bulan[++$i];
            }

            $parts[] = $start === $end
                ? $nama[$start - 1]
                : $nama[$start - 1] . '-' . $nama[$end - 1];
        }

        return implode(', ', $parts);
    }

    protected function kwitansiNo(string $tahun, int $pemasukanId): string
    {
        return 'KWT-' . $tahun . '-' . str_pad((string) $pemasukanId, 6, '0', STR_PAD_LEFT);
    }

    protected function kirimKwitansiIuranKe(Anggota $anggota, int $bulanMulai, int $bulanAkhir, int $tahun, int $jumlahBulan, float $nominal, int $pemasukanId): string
    {
        $no = $this->kwitansiNo((string) $tahun, $pemasukanId);
        $periode = 'Iuran ' . $jumlahBulan . ' bln (' . $this->bulanNama($bulanMulai) . ($bulanAkhir !== $bulanMulai ? '-' . $this->bulanNama($bulanAkhir) : '') . ' ' . $tahun . ')';
        $total = $nominal * $jumlahBulan;

        $pesan = "Assalamu'alaikum *{$anggota->nama}*,\n\n"
            . "Pembayaran iuran Anda telah kami catat:\n\n"
            . "• Periode: {$periode}\n"
            . '• Nominal: Rp ' . number_format($total, 0, ',', '.') . "\n"
            . '• Tanggal: ' . now()->format('d/m/Y') . "\n\n"
            . "Terlampir kwitansi pembayaran.\n\n"
            . 'Terima kasih 🙏' . "\n"
            . '— Bendahara RKM Al-Jannah';

        $base64 = $this->kwitansi()->pdf([
            'no' => $no,
            'nama' => $anggota->nama,
            'peruntukan' => $periode,
            'nominal' => $total,
            'tanggal' => now()->format('d/m/Y'),
            'dibayar_oleh' => auth()->user()->nama ?? 'Bendahara',
        ]);

        $res = $this->wa()->sendDocument($anggota->telepon, $base64, 'Kwitansi-' . $no . '.pdf', 'application/pdf', $pesan);

        return isset($res['error']) ? 'gagal' : 'terkirim';
    }

    // ---------------------------------------------------------------
    //  Dashboard
    // ---------------------------------------------------------------

    public function index()
    {
        $now = now();
        $bulanIni = $now->month;
        $tahunIni = $now->year;
        $bulanLalu = $bulanIni === 1 ? 12 : $bulanIni - 1;
        $tahunLalu = $bulanIni === 1 ? $tahunIni - 1 : $tahunIni;

        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');
        $saldo = $totalPemasukan - $totalPengeluaran;

        $pemasukanBulanIni = Pemasukan::whereYear('created_at', $tahunIni)
            ->whereMonth('created_at', $bulanIni)->sum('jumlah');
        $pengeluaranBulanIni = Pengeluaran::whereYear('created_at', $tahunIni)
            ->whereMonth('created_at', $bulanIni)->sum('jumlah');

        $pemasukanBulanLalu = Pemasukan::whereYear('created_at', $tahunLalu)
            ->whereMonth('created_at', $bulanLalu)->sum('jumlah');
        $pengeluaranBulanLalu = Pengeluaran::whereYear('created_at', $tahunLalu)
            ->whereMonth('created_at', $bulanLalu)->sum('jumlah');

        $persenPemasukan = $pemasukanBulanLalu > 0
            ? round(($pemasukanBulanIni - $pemasukanBulanLalu) / $pemasukanBulanLalu * 100, 1) : 0;
        $persenPengeluaran = $pengeluaranBulanLalu > 0
            ? round(($pengeluaranBulanIni - $pengeluaranBulanLalu) / $pengeluaranBulanLalu * 100, 1) : 0;

        $selisih = $pemasukanBulanIni - $pengeluaranBulanIni;
        $menunggu = PermintaanIzin::menunggu()->count();
        $disetujui = PermintaanIzin::disetujui()->count();
        $ditolak = PermintaanIzin::ditolak()->count();
        $totalTransaksi = Pemasukan::count() + Pengeluaran::count();

        // Grafik Arus Kas Bulanan
        $pemasukanBulanan = Pemasukan::selectRaw("CAST(strftime('%m', created_at) AS INTEGER) as bulan, SUM(jumlah) as total")
            ->whereYear('created_at', $tahunIni)
            ->groupBy('bulan')
            ->pluck('total', 'bulan');
        $pengeluaranBulanan = Pengeluaran::selectRaw("CAST(strftime('%m', created_at) AS INTEGER) as bulan, SUM(jumlah) as total")
            ->whereYear('created_at', $tahunIni)
            ->groupBy('bulan')
            ->pluck('total', 'bulan');
        $chartLabels = [];
        $chartPemasukan = [];
        $chartPengeluaran = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartLabels[] = \Carbon\Carbon::create()->month($m)->format('M');
            $chartPemasukan[] = (float) ($pemasukanBulanan[$m] ?? 0);
            $chartPengeluaran[] = (float) ($pengeluaranBulanan[$m] ?? 0);
        }

        // Grafik Pengeluaran per Kategori
        $pengeluaranPerKategori = \DB::table('pengeluaran')
            ->selectRaw('kategori_pengeluaran_id, SUM(jumlah) as total')
            ->whereYear('created_at', $tahunIni)
            ->groupBy('kategori_pengeluaran_id')
            ->get();
        $kategoriPengeluaranMap = KategoriPengeluaran::pluck('nama', 'id');
        $kategoriLabels = [];
        $kategoriData = [];
        $totalPengeluaranKategori = $pengeluaranPerKategori->sum('total') ?: 1;
        foreach ($pengeluaranPerKategori as $item) {
            $nama = $kategoriPengeluaranMap[$item->kategori_pengeluaran_id] ?? 'Lainnya';
            $kategoriLabels[] = $nama;
            $kategoriData[] = [
                'nominal' => (float) $item->total,
                'persen' => round($item->total / $totalPengeluaranKategori * 100, 1),
            ];
        }

        // Top 3 Kategori Pemasukan Bulan Ini
        $topPemasukanRaw = \DB::table('pemasukan')
            ->selectRaw('kategori_pemasukan_id, SUM(jumlah) as total')
            ->whereYear('created_at', $tahunIni)
            ->whereMonth('created_at', $bulanIni)
            ->groupBy('kategori_pemasukan_id')
            ->orderByDesc('total')
            ->take(3)
            ->get();
        $kategoriPemasukanMap = KategoriPemasukan::pluck('nama', 'id');
        $topPemasukanKategori = $topPemasukanRaw->map(fn($item) => (object)[
            'nama' => $kategoriPemasukanMap[$item->kategori_pemasukan_id] ?? 'Lainnya',
            'total' => (float) $item->total,
        ]);

        // Top 3 Kategori Pengeluaran Bulan Ini
        $topPengeluaranRaw = \DB::table('pengeluaran')
            ->selectRaw('kategori_pengeluaran_id, SUM(jumlah) as total')
            ->whereYear('created_at', $tahunIni)
            ->whereMonth('created_at', $bulanIni)
            ->groupBy('kategori_pengeluaran_id')
            ->orderByDesc('total')
            ->take(3)
            ->get();
        $topPengeluaranKategori = $topPengeluaranRaw->map(fn($item) => (object)[
            'nama' => $kategoriPengeluaranMap[$item->kategori_pengeluaran_id] ?? 'Lainnya',
            'total' => (float) $item->total,
        ]);

        // Tabel Transaksi Terbaru
        $transaksiMasuk = Pemasukan::with('kategoriPemasukan')
            ->latest()->take(5)->get()->map(fn($p) => (object)[
                'tanggal' => $p->tanggal->format('d/m/Y'),
                'keterangan' => $p->keterangan ?? $p->kategoriPemasukan?->nama ?? 'Pemasukan',
                'jumlah' => (float) $p->jumlah,
                'jumlah_fmt' => 'Rp ' . number_format($p->jumlah, 0, ',', '.'),
                'jenis' => 'pemasukan',
            ]);
        $transaksiKeluar = Pengeluaran::with('kategoriPengeluaran')
            ->latest()->take(5)->get()->map(fn($p) => (object)[
                'tanggal' => $p->tanggal->format('d/m/Y'),
                'keterangan' => $p->keterangan ?? $p->kategoriPengeluaran?->nama ?? 'Pengeluaran',
                'jumlah' => (float) $p->jumlah,
                'jumlah_fmt' => 'Rp ' . number_format($p->jumlah, 0, ',', '.'),
                'jenis' => 'pengeluaran',
            ]);

        // Ringkasan Iuran Anggota
        $totalAnggotaAktif = Anggota::where('status', 'aktif')->count();
        $anggotaSudahBayarTahunIni = Anggota::whereHas('iuranTahunan', function ($q) use ($tahunIni) {
            $q->where('tahun', $tahunIni)->where('status', 'lunas');
        })->count();
        $anggotaBelumBayarTahunIni = $totalAnggotaAktif - $anggotaSudahBayarTahunIni;
        $kepatuhanIuran = $totalAnggotaAktif > 0
            ? round($anggotaSudahBayarTahunIni / $totalAnggotaAktif * 100, 1) : 0;

        // Top Anggota Pembayar Iuran (sepanjang masa)
        $topAnggotaIuranRaw = \DB::table('iuran_tahunan')
            ->selectRaw('anggota_id, COUNT(*) as jumlah_bayar, SUM(nominal) as total_nominal')
            ->where('status', 'lunas')
            ->groupBy('anggota_id')
            ->orderByDesc('jumlah_bayar')
            ->orderByDesc('total_nominal')
            ->take(5)
            ->get();
        $allAnggotaIds = $topAnggotaIuranRaw->pluck('anggota_id');
        $allAnggotaMap = $allAnggotaIds->isNotEmpty()
            ? Anggota::whereIn('id', $allAnggotaIds)->pluck('nama', 'id')
            : collect();
        $topAnggotaIuran = $topAnggotaIuranRaw->map(fn($item) => (object)[
            'nama' => $allAnggotaMap[$item->anggota_id] ?? 'Unknown',
            'jumlah_bayar' => (int) $item->jumlah_bayar,
            'total_nominal' => (float) $item->total_nominal,
        ]);

        // Top Kontributor Pemasukan (5 kategori pemasukan terbesar sepanjang masa)
        $topKontributorRaw = \DB::table('pemasukan')
            ->selectRaw('kategori_pemasukan_id, SUM(jumlah) as total')
            ->groupBy('kategori_pemasukan_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();
        $totalSemuaPemasukan = $totalPemasukan ?: 1;
        $topKontributor = $topKontributorRaw->map(fn($item) => [
            'nama' => $kategoriPemasukanMap[$item->kategori_pemasukan_id] ?? 'Lainnya',
            'total' => (float) $item->total,
            'total_fmt' => 'Rp ' . number_format($item->total, 0, ',', '.'),
            'persen' => round($item->total / $totalSemuaPemasukan * 100, 1),
        ]);

        // Insight Keuangan Otomatis
        $insightKategoriMasuk = $topPemasukanKategori->isNotEmpty()
            ? $topPemasukanKategori->first()->nama : '-';
        $insightPersenMasuk = $topPemasukanKategori->isNotEmpty()
            ? round($topPemasukanKategori->first()->total / max($pemasukanBulanIni, 1) * 100, 1) : 0;
        $insightKategoriKeluar = $topPengeluaranKategori->isNotEmpty()
            ? $topPengeluaranKategori->first()->nama : '-';
        $insightPersenKeluar = $topPengeluaranKategori->isNotEmpty()
            ? round($topPengeluaranKategori->first()->total / max($pengeluaranBulanIni, 1) * 100, 1) : 0;
        $insightAnggotaAktif = $topAnggotaIuran->isNotEmpty()
            ? $topAnggotaIuran->first()->nama : '-';
        $rasio = $totalPengeluaran > 0
            ? round($totalPemasukan / $totalPengeluaran, 1) : ($totalPemasukan > 0 ? '∞' : '0');
        $insightSaldo = $persenPemasukan;

        return view('dashboard.bendahara.index', [
            // --- Display-ready arrays (controller-built, view just loops) ---
            'statCards' => $this->buildStatCards($saldo, $totalPemasukan, $pemasukanBulanIni, $pengeluaranBulanIni,
                $totalTransaksi, $selisih, $menunggu, $persenPemasukan, $persenPengeluaran, $disetujui, $ditolak),
            'iuranCards' => $this->buildIuranCards($totalAnggotaAktif, $anggotaSudahBayarTahunIni,
                $anggotaBelumBayarTahunIni, $kepatuhanIuran),
            'kategoriList' => $this->buildKategoriList($totalPemasukan, $totalPengeluaran,
                $topPemasukanKategori, $topPengeluaranKategori),
            'rankingList' => $this->buildRankingList($topAnggotaIuran, $topKontributor),
            'insightList' => $this->buildInsightList($insightKategoriMasuk, $insightPersenMasuk,
                $insightKategoriKeluar, $insightPersenKeluar, $insightAnggotaAktif, $rasio, $insightSaldo),

            // --- Chart raw data (for JS) ---
            'chartLabels' => $chartLabels,
            'chartPemasukan' => $chartPemasukan,
            'chartPengeluaran' => $chartPengeluaran,
            'kategoriLabels' => $kategoriLabels,
            'kategoriData' => $kategoriData,
            'pemasukanBulanIni' => $pemasukanBulanIni,
            'pengeluaranBulanIni' => $pengeluaranBulanIni,

            // --- Data used directly in template ---
            'transaksiMasuk' => $transaksiMasuk,
            'transaksiKeluar' => $transaksiKeluar,
            'kepatuhanIuran' => $kepatuhanIuran,
        ]);
    }

    // ---------------------------------------------------------------
    //  Display-array builders
    // ---------------------------------------------------------------

    private function buildStatCards($saldo, $totalPemasukan, $pemasukanBulanIni, $pengeluaranBulanIni,
        $totalTransaksi, $selisih, $menunggu, $persenPemasukan, $persenPengeluaran, $disetujui, $ditolak): array
    {
        $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');

        return [
            [
                'icon' => 'account_balance',         'color' => '#16423C,#1f5c53',
                'label' => 'Total Saldo',             'value' => $fmt($saldo),                     'delay' => 1,
            ],
            [
                'icon' => 'payments',                'color' => '#0369a1,#0284c7',
                'label' => 'Saldo Kas',               'value' => $fmt($totalPemasukan),            'delay' => 2,
            ],
            [
                'icon' => 'account_balance_wallet',  'color' => '#7c3aed,#8b5cf6',
                'label' => 'Saldo Bank',              'value' => $fmt($pemasukanBulanIni),         'delay' => 3,
            ],
            [
                'icon' => 'receipt_long',            'color' => '#0891b2,#06b6d4',
                'label' => 'Total Transaksi',         'value' => $totalTransaksi,                  'delay' => 4,
            ],
            [
                'icon' => 'trending_up',             'color' => '#059669,#10b981',
                'label' => 'Pemasukan Bulan Ini',    'value' => $fmt($pemasukanBulanIni),         'delay' => 5,
                'sub' => [
                    'text' => abs($persenPemasukan) . '% dari bulan lalu',
                    'class' => $persenPemasukan >= 0 ? 'up' : 'down',
                    'icon' => $persenPemasukan >= 0 ? 'arrow_upward' : 'arrow_downward',
                ],
            ],
            [
                'icon' => 'trending_down',           'color' => '#dc2626,#ef4444',
                'label' => 'Pengeluaran Bulan Ini',  'value' => $fmt($pengeluaranBulanIni),       'delay' => 6,
                'sub' => [
                    'text' => abs($persenPengeluaran) . '% dari bulan lalu',
                    'class' => $persenPengeluaran <= 0 ? 'up' : 'down',
                    'icon' => $persenPengeluaran <= 0 ? 'arrow_downward' : 'arrow_upward',
                ],
            ],
            [
                'icon' => 'swap_horiz',              'color' => '#d97706,#f59e0b',
                'label' => 'Selisih Keuangan',       'value' => $fmt($selisih),                   'delay' => 7,
                'value_class' => $selisih >= 0 ? 'text-green-600' : 'text-red-600',
            ],
            [
                'icon' => 'pending_actions',         'color' => '#6366f1,#818cf8',
                'label' => 'Menunggu Persetujuan',   'value' => $menunggu,                        'delay' => 8,
                'sub' => [
                    'text' => "Disetujui: {$disetujui} · Ditolak: {$ditolak}",
                    'class' => 'neutral',
                ],
            ],
        ];
    }

    private function buildIuranCards($totalAnggotaAktif, $anggotaSudahBayarTahunIni,
        $anggotaBelumBayarTahunIni, $kepatuhanIuran): array
    {
        return [
            [
                'icon' => 'groups',           'color' => '#0d4f46,#1a6b5c',
                'label' => 'Total Anggota Aktif',    'value' => $totalAnggotaAktif,               'delay' => 1,
            ],
            [
                'icon' => 'check_circle',     'color' => '#059669,#10b981',
                'label' => 'Sudah Bayar Iuran Tahun Ini', 'value' => $anggotaSudahBayarTahunIni,  'delay' => 2,
            ],
            [
                'icon' => 'cancel',           'color' => '#dc2626,#ef4444',
                'label' => 'Belum Bayar Iuran Tahun Ini', 'value' => $anggotaBelumBayarTahunIni,  'delay' => 3,
            ],
            [
                'icon' => 'percent',          'color' => '#6366f1,#818cf8',
                'label' => 'Kepatuhan Iuran',          'value' => $kepatuhanIuran . '%',           'delay' => 4,
                'sub' => ['type' => 'progress', 'value' => $kepatuhanIuran],
            ],
        ];
    }

    private function buildKategoriList($totalPemasukan, $totalPengeluaran,
        $topPemasukanKategori, $topPengeluaranKategori): array
    {
        $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $buildItems = function ($items, $total) use ($fmt) {
            return $items->map(fn($item) => [
                'nama' => $item->nama,
                'total' => $item->total,
                'total_fmt' => $fmt($item->total),
                'persen' => round($item->total / max($total, 1) * 100, 1),
            ]);
        };

        $totalP = max($totalPemasukan, 1);
        $totalR = max($totalPengeluaran, 1);

        return [
            [
                'total' => $totalP,
                'items' => $buildItems($topPemasukanKategori, $totalP),
                'title' => 'Pemasukan',
                'color' => '#059669',
                'gradient' => '#059669,#10b981',
            ],
            [
                'total' => $totalR,
                'items' => $buildItems($topPengeluaranKategori, $totalR),
                'title' => 'Pengeluaran',
                'color' => '#dc2626',
                'gradient' => '#dc2626,#ef4444',
            ],
        ];
    }

    private function buildRankingList($topAnggotaIuran, $topKontributor): array
    {
        $rank = fn($items, $detailFn) => $items->map(fn($e, $i) => [
            'rank' => $i + 1,
            'rank_class' => match ($i) { 0 => 'gold', 1 => 'silver', 2 => 'bronze', default => 'normal' },
            'name' => is_object($e) ? $e->nama : $e['nama'],
            'detail' => $detailFn($e),
            'amount' => is_object($e)
                ? 'Rp ' . number_format($e->total_nominal, 0, ',', '.')
                : ($e['total_fmt'] ?? 'Rp ' . number_format($e['total'], 0, ',', '.')),
        ])->values();

        return [
            [
                'title' => 'Top Anggota Pembayar Iuran',
                'items' => $rank($topAnggotaIuran, fn($e) => $e->jumlah_bayar . ' pembayaran'),
                'empty' => 'Belum ada data iuran',
            ],
            [
                'title' => 'Top Kontributor Pemasukan',
                'items' => $rank($topKontributor, fn($e) => 'Kontribusi ' . $e['persen'] . '%'),
                'empty' => 'Belum ada data pemasukan',
            ],
        ];
    }

    private function buildInsightList($insightKategoriMasuk, $insightPersenMasuk,
        $insightKategoriKeluar, $insightPersenKeluar, $insightAnggotaAktif, $rasio, $insightSaldo): array
    {
        $rasioLabel = is_numeric($rasio) ? ($rasio > 1 ? 'lebih besar' : 'lebih kecil') : 'tidak terhingga';

        return [
            [
                'type' => 'pemasukan',   'cls' => 'green',
                'label' => 'Pemasukan Terbesar',
                'value' => $insightKategoriMasuk,
                'sub' => $insightPersenMasuk . '% dari total',
            ],
            [
                'type' => 'pengeluaran', 'cls' => 'red',
                'label' => 'Pengeluaran Terbesar',
                'value' => $insightKategoriKeluar,
                'sub' => $insightPersenKeluar . '% dari total',
            ],
            [
                'type' => 'anggota',     'cls' => 'blue',
                'label' => 'Anggota Iuran Teraktif',
                'value' => $insightAnggotaAktif,
                'sub' => 'Paling sering membayar iuran',
            ],
            [
                'type' => 'rasio',       'cls' => 'amber',
                'label' => 'Rasio Pemasukan/Pengeluaran',
                'value' => $rasio . 'x',
                'sub' => 'Total pemasukan ' . $rasioLabel . ' dari pengeluaran',
            ],
            [
                'type' => 'saldo',       'cls' => 'purple',
                'label' => 'Perubahan Saldo',
                'value' => ($insightSaldo >= 0 ? '+' : '') . $insightSaldo . '%',
                'sub' => 'Dibanding bulan lalu',
            ],
        ];
    }

    // ---------------------------------------------------------------
    //  Catat Transaksi
    // ---------------------------------------------------------------

    public function catatTransaksi(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSampai = $request->input('tanggal_sampai');
        $tipe = $request->input('tipe');
        $kategori = $request->input('kategori');
        $keterangan = $request->input('keterangan');
        $nominalMin = $request->input('nominal_min');
        $nominalMax = $request->input('nominal_max');

        $queryPemasukan = Pemasukan::with('kategoriPemasukan');
        $queryPengeluaran = Pengeluaran::with('kategoriPengeluaran');

        if ($tipe !== 'pengeluaran') {
            $queryPemasukan->when($tanggalMulai, fn($q, $v) => $q->whereDate('tanggal', '>=', $v))
                ->when($tanggalSampai, fn($q, $v) => $q->whereDate('tanggal', '<=', $v))
                ->when($keterangan, fn($q, $v) => $q->where('keterangan', 'like', '%' . $v . '%'))
                ->when($nominalMin, fn($q, $v) => $q->where('jumlah', '>=', $v))
                ->when($nominalMax, fn($q, $v) => $q->where('jumlah', '<=', $v));

            if ($kategori) {
                $kategoriParts = explode('_', $kategori);
                if (isset($kategoriParts[1]) && $kategoriParts[0] === 'pemasukan') {
                    $queryPemasukan->where('kategori_pemasukan_id', $kategoriParts[1]);
                }
            }
        }

        if ($tipe !== 'pemasukan') {
            $queryPengeluaran->when($tanggalMulai, fn($q, $v) => $q->whereDate('tanggal', '>=', $v))
                ->when($tanggalSampai, fn($q, $v) => $q->whereDate('tanggal', '<=', $v))
                ->when($keterangan, fn($q, $v) => $q->where('keterangan', 'like', '%' . $v . '%'))
                ->when($nominalMin, fn($q, $v) => $q->where('jumlah', '>=', $v))
                ->when($nominalMax, fn($q, $v) => $q->where('jumlah', '<=', $v));

            if ($kategori) {
                $kategoriParts = explode('_', $kategori);
                if (isset($kategoriParts[1]) && $kategoriParts[0] === 'pengeluaran') {
                    $queryPengeluaran->where('kategori_pengeluaran_id', $kategoriParts[1]);
                }
            }
        }

        $pemasukanData = $tipe !== 'pengeluaran' ? $queryPemasukan->get()->map(fn($p) => [
            'id' => $p->id,
            'tipe' => 'Pemasukan',
            'tanggal' => $p->tanggal->format('d/m/Y'),
            'jumlah' => $p->jumlah,
            'kategori' => $p->kategoriPemasukan?->nama ?? '-',
            'keterangan' => $p->keterangan ?? '-',
            'created_at' => $p->created_at,
            'file_url' => $p->getFirstMediaUrl('bukti') ?: null,
        ]) : collect();

        $pengeluaranData = $tipe !== 'pemasukan' ? $queryPengeluaran->get()->map(fn($p) => [
            'id' => $p->id,
            'tipe' => 'Pengeluaran',
            'tanggal' => $p->tanggal->format('d/m/Y'),
            'jumlah' => $p->jumlah,
            'kategori' => $p->kategoriPengeluaran?->nama ?? '-',
            'keterangan' => $p->keterangan ?? '-',
            'created_at' => $p->created_at,
            'file_url' => $p->getFirstMediaUrl('bukti') ?: null,
        ]) : collect();

        $all = $pemasukanData->concat($pengeluaranData)->sortByDesc('created_at')->values();

        $page = $request->get('page', 1);
        $perPage = 20;
        $transaksi = new LengthAwarePaginator(
            $all->forPage($page, $perPage),
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $kategoriPemasukan = KategoriPemasukan::all();
        $kategoriPengeluaran = KategoriPengeluaran::all();

        return view('dashboard.bendahara.catat-transaksi', compact('transaksi', 'kategoriPemasukan', 'kategoriPengeluaran') + [
            'waReady' => $this->wa()->isReady(),
            'filter' => [
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_sampai' => $tanggalSampai,
                'tipe' => $tipe,
                'kategori' => $kategori,
                'keterangan' => $keterangan,
                'nominal_min' => $nominalMin,
                'nominal_max' => $nominalMax,
            ],
        ]);
    }

    public function storeTransaksi(Request $request)
    {
        $rules = [
            'tipe' => 'required|in:pemasukan,pengeluaran',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:1000',
            'file_bukti' => 'required|file|max:2048',
            'wa_nama' => 'nullable|string|max:100',
            'wa_penerima' => 'nullable|string|max:30',
        ];

        $rules['kategori_id'] = $request->tipe === 'pemasukan'
            ? 'required|exists:kategori_pemasukan,id'
            : 'required|exists:kategori_pengeluaran,id';

        $validated = $request->validate($rules);

        try {
            if ($validated['tipe'] === 'pemasukan') {
                $model = Pemasukan::create([
                    'tanggal' => $validated['tanggal'],
                    'kategori_pemasukan_id' => $validated['kategori_id'],
                    'jumlah' => $validated['jumlah'],
                    'keterangan' => $validated['keterangan'],
                    'created_by' => auth()->id(),
                ]);
                $kategori = $model->kategoriPemasukan?->nama ?? '-';
            } else {
                $model = Pengeluaran::create([
                    'tanggal' => $validated['tanggal'],
                    'kategori_pengeluaran_id' => $validated['kategori_id'],
                    'jumlah' => $validated['jumlah'],
                    'keterangan' => $validated['keterangan'],
                    'created_by' => auth()->id(),
                ]);
                $kategori = $model->kategoriPengeluaran?->nama ?? '-';
            }

            $model->addMedia($request->file('file_bukti'))->withCustomProperties(['uploaded_by' => auth()->id()])->toMediaCollection('bukti');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal simpan transaksi: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()], 500);
        }

        // --- Kirim kwitansi WA (di luar try utama agar transaksi tetap tersimpan) ---
        $waStatus = 'tidak_aktif';
        $waPenerima = trim((string) $request->input('wa_penerima', ''));

        if ($validated['tipe'] === 'pemasukan' && $waPenerima !== '' && $this->wa()->isReady()) {
            try {
                $no = $this->kwitansiNo(now()->format('Y'), $model->id);
                $keterangan = $model->keterangan ? ' (' . $model->keterangan . ')' : '';
                $peruntukan = 'Pemasukan - ' . $kategori . $keterangan;
                $namaPenerima = trim((string) $request->input('wa_nama', '')) ?: ('Transaksi #' . $model->id);

                $pesan = 'Pembayaran *' . $peruntukan . "* telah dicatat:\n\n"
                    . '• Nominal: Rp ' . number_format($model->jumlah, 0, ',', '.') . "\n"
                    . '• Tanggal: ' . $model->tanggal->format('d/m/Y') . "\n\n"
                    . "Terlampir kwitansi pembayaran.\n\n"
                    . 'Terima kasih 🙏' . "\n"
                    . '— Bendahara RKM Al-Jannah';

                $base64 = $this->kwitansi()->pdf([
                    'no' => $no,
                    'nama' => $namaPenerima,
                    'peruntukan' => $peruntukan,
                    'nominal' => $model->jumlah,
                    'tanggal' => $model->tanggal->format('d/m/Y'),
                    'dibayar_oleh' => auth()->user()->nama ?? 'Bendahara',
                ]);

                $res = $this->wa()->sendDocument($waPenerima, $base64, 'Kwitansi-' . $no . '.pdf', 'application/pdf', $pesan);

                if (isset($res['error'])) {
                    \Illuminate\Support\Facades\Log::warning('WA kwitansi transaksi gagal: ' . $res['error'], [
                        'transaksi_id' => $model->id,
                        'telepon' => $waPenerima,
                    ]);
                    // Fallback: kirim teks tanpa lampiran
                    $resText = $this->wa()->send($waPenerima, $pesan);
                    $waStatus = isset($resText['error']) ? 'gagal' : 'terkirim_teks';
                } else {
                    $waStatus = 'terkirim';
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error kirim kwitansi WA transaksi: ' . $e->getMessage(), [
                    'transaksi_id' => $model->id,
                    'telepon' => $waPenerima,
                    'trace' => $e->getTraceAsString(),
                ]);
                $waStatus = 'gagal';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $model->id,
                'tipe' => $validated['tipe'] === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran',
                'tanggal' => $model->tanggal->format('d/m/Y'),
                'jumlah' => $model->jumlah,
                'kategori' => $kategori,
                'keterangan' => $model->keterangan ?? '-',
                'file_url' => $model->getFirstMediaUrl('bukti') ?: null,
                'wa' => $waStatus,
            ]
        ]);
    }

    // ---------------------------------------------------------------
    //  Pemasukan / Pengeluaran
    // ---------------------------------------------------------------

    public function pemasukan()
    {
        $pemasukan = Pemasukan::with('kategoriPemasukan')->latest()->paginate(20);
        $kategoriList = KategoriPemasukan::all();
        return view('dashboard.bendahara.pemasukan', compact('pemasukan', 'kategoriList'));
    }

    public function pengeluaran()
    {
        $pengeluaran = Pengeluaran::with('kategoriPengeluaran')->latest()->paginate(20);
        $kategoriList = KategoriPengeluaran::all();
        return view('dashboard.bendahara.pengeluaran', compact('pengeluaran', 'kategoriList'));
    }

    public function storePemasukan(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori_pemasukan_id' => 'required|exists:kategori_pemasukan,id',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:1000',
            'file_bukti' => 'required|file|max:2048',
        ]);

        try {
            $pemasukan = Pemasukan::create([
                'tanggal' => $validated['tanggal'],
                'kategori_pemasukan_id' => $validated['kategori_pemasukan_id'],
                'jumlah' => $validated['jumlah'],
                'keterangan' => $validated['keterangan'],
                'created_by' => auth()->id(),
            ]);

            $pemasukan->addMedia($request->file('file_bukti'))->withCustomProperties(['uploaded_by' => auth()->id()])->toMediaCollection('bukti');

            LogSuperadmin::create([
                'user_id' => auth()->id(),
                'aksi' => 'create',
                'deskripsi' => 'Menambahkan pemasukan: Rp ' . number_format($pemasukan->jumlah, 0, ',', '.') . ' (' . ($pemasukan->kategoriPemasukan?->nama ?? '-') . ')',
                'modul' => 'bendahara',
                'referensi_id' => $pemasukan->id,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal simpan pemasukan: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan pemasukan: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pemasukan->id,
                'tanggal' => $pemasukan->tanggal->format('d/m/Y'),
                'jumlah' => $pemasukan->jumlah,
                'kategori' => $pemasukan->kategoriPemasukan?->nama ?? '-',
                'keterangan' => $pemasukan->keterangan ?? '-',
            ]
        ]);
    }

    public function storePengeluaran(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori_pengeluaran_id' => 'required|exists:kategori_pengeluaran,id',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:1000',
            'file_bukti' => 'required|file|max:2048',
        ]);

        try {
            $pengeluaran = Pengeluaran::create([
                'tanggal' => $validated['tanggal'],
                'kategori_pengeluaran_id' => $validated['kategori_pengeluaran_id'],
                'jumlah' => $validated['jumlah'],
                'keterangan' => $validated['keterangan'],
                'created_by' => auth()->id(),
            ]);

            $pengeluaran->addMedia($request->file('file_bukti'))->withCustomProperties(['uploaded_by' => auth()->id()])->toMediaCollection('bukti');

            LogSuperadmin::create([
                'user_id' => auth()->id(),
                'aksi' => 'create',
                'deskripsi' => 'Menambahkan pengeluaran: Rp ' . number_format($pengeluaran->jumlah, 0, ',', '.') . ' (' . ($pengeluaran->kategoriPengeluaran?->nama ?? '-') . ')',
                'modul' => 'bendahara',
                'referensi_id' => $pengeluaran->id,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal simpan pengeluaran: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan pengeluaran: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pengeluaran->id,
                'tanggal' => $pengeluaran->tanggal->format('d/m/Y'),
                'jumlah' => $pengeluaran->jumlah,
                'kategori' => $pengeluaran->kategoriPengeluaran?->nama ?? '-',
                'keterangan' => $pengeluaran->keterangan ?? '-',
            ]
        ]);
    }

    // ---------------------------------------------------------------
    //  Iuran
    // ---------------------------------------------------------------

    public function iuran()
    {
        return view('dashboard.bendahara.iuran', ['waReady' => $this->wa()->isReady()]);
    }

    public function getIuranData(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $memberId = $request->input('member_id');

        $daftarAnggota = Anggota::where('status', 'aktif')
            ->when($memberId, fn($q) => $q->where('id', $memberId))
            ->get();

        $data = $daftarAnggota->map(function ($a) use ($tahun) {
            $iuran = $a->iuranTahunan()->where('tahun', $tahun)->get()->keyBy('bulan');

            $bulan = [];
            for ($b = 1; $b <= 12; $b++) {
                $i = $iuran->get($b);
                $bulan[] = [
                    'bulan' => $b,
                    'status' => $i ? $i->status : 'belum_lunas',
                    'tanggal_bayar' => $i ? ($i->tanggal_bayar ? \Carbon\Carbon::parse($i->tanggal_bayar)->format('d/m/Y') : '') : '',
                ];
            }

            return [
                'id' => $a->id,
                'nomor_anggota' => $a->nomor_anggota,
                'nama' => $a->nama,
                'telepon' => $a->telepon,
                'access_code' => $a->access_code,
                'tahun_mulai' => max(2025, $a->tanggal_aktif_kembali
                    ? (int) \Carbon\Carbon::parse($a->tanggal_aktif_kembali)->format('Y')
                    : (int) $a->created_at->format('Y')),
                'bulan' => $bulan,
            ];
        });

        return response()->json([
            'tahun' => $tahun,
            'anggota' => $data,
        ]);
    }

    public function generateAccessCode(Request $request)
    {
        $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
        ]);

        $anggota = Anggota::findOrFail($request->anggota_id);
        $code = strtoupper(substr(md5(uniqid($anggota->id . microtime(), true)), 0, 8));
        $anggota->access_code = $code;
        $anggota->access_code_generated_at = now();
        $anggota->save();

        return response()->json([
            'success' => true,
            'access_code' => $code,
        ]);
    }

    public function storeIuran(Request $request)
    {
        $validated = $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'tahun' => 'required|integer|min:2020|max:2099',
            'bulan_mulai' => 'required|integer|min:1|max:12',
            'jumlah_bulan' => 'required|integer|min:1|max:12',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:1000',
            'file_bukti' => 'required|file|max:2048',
        ]);

        $anggota = Anggota::findOrFail($validated['anggota_id']);
        $tahunMulai = max(2025, $anggota->tanggal_aktif_kembali
            ? (int) \Carbon\Carbon::parse($anggota->tanggal_aktif_kembali)->format('Y')
            : (int) $anggota->created_at->format('Y'));

        if ($validated['tahun'] < $tahunMulai) {
            return response()->json([
                'success' => false,
                'message' => "Anggota ini mulai aktif tahun $tahunMulai, tidak bisa membayar iuran tahun sebelumnya."
            ], 422);
        }

        for ($tahun = $tahunMulai; $tahun < $validated['tahun']; $tahun++) {
            $bulanLunas = IuranTahunan::where('anggota_id', $validated['anggota_id'])
                ->where('tahun', $tahun)
                ->where('status', 'lunas')
                ->count();
            if ($bulanLunas < 12) {
                return response()->json([
                    'success' => false,
                    'message' => "Anggota masih memiliki tanggungan iuran tahun $tahun. Selesaikan semua tanggungan tahun sebelumnya terlebih dahulu."
                ], 422);
            }
        }

        $totalNominal = $validated['nominal'] * $validated['jumlah_bulan'];
        $bulanAkhir = min($validated['bulan_mulai'] + $validated['jumlah_bulan'] - 1, 12);

        $pemasukan = Pemasukan::create([
            'tanggal' => now()->toDateString(),
            'kategori_pemasukan_id' => 1,
            'jumlah' => $totalNominal,
            'keterangan' => 'Iuran ' . $validated['jumlah_bulan'] . ' bln - ' . ($validated['keterangan'] ?? ''),
            'created_by' => auth()->id(),
        ]);

        $pemasukan->addMedia($request->file('file_bukti'))->withCustomProperties(['uploaded_by' => auth()->id()])->toMediaCollection('bukti');

        for ($b = $validated['bulan_mulai']; $b <= $bulanAkhir; $b++) {
            IuranTahunan::create([
                'tahun' => $validated['tahun'],
                'anggota_id' => $validated['anggota_id'],
                'bulan' => $b,
                'nominal' => $validated['nominal'],
                'status' => 'lunas',
                'tanggal_bayar' => now()->toDateString(),
                'verified_by' => auth()->id(),
                'pemasukan_id' => $pemasukan->id,
            ]);
        }

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'deskripsi' => 'Mencatat iuran anggota: ' . $anggota->nama . ' - ' . $validated['jumlah_bulan'] . ' bulan - Rp ' . number_format($totalNominal, 0, ',', '.'),
            'modul' => 'bendahara',
            'referensi_id' => $pemasukan->id,
            'ip_address' => request()->ip(),
        ]);

        $waStatus = 'tidak_aktif';
        if ($anggota->telepon && $this->wa()->isReady()) {
            $waStatus = $this->kirimKwitansiIuranKe(
                $anggota,
                $validated['bulan_mulai'],
                $bulanAkhir,
                $validated['tahun'],
                $validated['jumlah_bulan'],
                $validated['nominal'],
                $pemasukan->id,
            );
        }

        return response()->json([
            'success' => true,
            'wa' => $waStatus,
            'data' => [
                'pemasukan_id' => $pemasukan->id,
                'anggota_nama' => $anggota?->nama ?? '',
                'bulan_mulai' => $validated['bulan_mulai'],
                'bulan_akhir' => $bulanAkhir,
                'jumlah_bulan' => $validated['jumlah_bulan'],
            ]
        ]);
    }

    public function kirimTagihanIuran(Request $request)
    {
        $request->validate([
            'anggota_id' => 'nullable|exists:anggota,id',
        ]);

        if (! $this->wa()->isReady()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp tidak terhubung. Pastikan server WhatsApp aktif & tersambung di menu adminweb.',
            ], 409);
        }

        $tahun = date('Y');
        $daftarAnggota = Anggota::where('status', 'aktif')
            ->when($request->anggota_id, fn ($q) => $q->where('id', $request->anggota_id))
            ->get();

        $terkirim = 0;
        $gagal = 0;
        $tanpaNomor = 0;
        $lunas = 0;

        foreach ($daftarAnggota as $anggota) {
            $iuran = $anggota->iuranTahunan()->where('tahun', $tahun)->get()->keyBy('bulan');

            $belum = [];
            for ($b = 1; $b <= 12; $b++) {
                if (! isset($iuran[$b]) || $iuran[$b]->status !== 'lunas') {
                    $belum[] = $b;
                }
            }

            if (empty($belum)) {
                $lunas++;
                continue;
            }

            if (! $anggota->telepon) {
                $tanpaNomor++;
                continue;
            }

            $range = $this->formatBulanRange($belum);
            $total = count($belum) * 10000;

            $pesan = "Assalamu'alaikum *{$anggota->nama}*,\n\n"
                . "Kami mengingatkan tagihan iuran tahunan RKM Al-Jannah tahun {$tahun}:\n\n"
                . "• Bulan belum lunas: {$range}\n"
                . '• Total tunggakan: Rp ' . number_format($total, 0, ',', '.') . "\n\n"
                . 'Mohon segera melunasi. Terima kasih 🙏' . "\n"
                . '— Bendahara RKM Al-Jannah';

            $res = $this->wa()->send($anggota->telepon, $pesan);

            if (isset($res['error'])) {
                $gagal++;
            } else {
                $terkirim++;
            }
        }

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'deskripsi' => 'Mengirim tagihan iuran via WhatsApp: ' . $terkirim . ' terkirim, ' . $gagal . ' gagal, ' . $tanpaNomor . ' tanpa nomor, ' . $lunas . ' sudah lunas',
            'modul' => 'bendahara',
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tagihan terkirim ke ' . $terkirim . ' anggota'
                . ($gagal ? ", {$gagal} gagal" : '')
                . ($tanpaNomor ? ", {$tanpaNomor} tanpa nomor" : '')
                . ($lunas ? ", {$lunas} sudah lunas" : ''),
            'data' => [
                'terkirim' => $terkirim,
                'gagal' => $gagal,
                'tanpa_nomor' => $tanpaNomor,
                'lunas' => $lunas,
            ],
        ]);
    }

    public function kirimKwitansiIuran(Request $request)
    {
        $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
        ]);

        $anggota = Anggota::findOrFail($request->anggota_id);
        $iuran = IuranTahunan::where('anggota_id', $anggota->id)
            ->where('status', 'lunas')
            ->latest('id')
            ->first();

        if (! $iuran) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada riwayat pembayaran iuran untuk anggota ini.',
            ], 422);
        }

        if (! $anggota->telepon) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota tidak memiliki nomor telepon.',
            ], 422);
        }

        if (! $this->wa()->isReady()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp tidak terhubung. Pastikan server WhatsApp aktif & tersambung di menu adminweb.',
            ], 409);
        }

        $rows = IuranTahunan::where('pemasukan_id', $iuran->pemasukan_id)->get();

        $waStatus = $this->kirimKwitansiIuranKe(
            $anggota,
            (int) $rows->min('bulan'),
            (int) $rows->max('bulan'),
            (int) $iuran->tahun,
            $rows->count(),
            (float) $iuran->nominal,
            (int) $iuran->pemasukan_id,
        );

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'deskripsi' => 'Mengirim kwitansi iuran via WhatsApp ke: ' . $anggota->nama,
            'modul' => 'bendahara',
            'referensi_id' => $iuran->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'success' => true,
            'wa' => $waStatus,
        ]);
    }

    public function laporan()
    {
        return view('dashboard.bendahara.laporan');
    }

    public function getLaporanData(Request $request)
    {
        $search = $request->input('search', '');
        $period = $request->input('period', 'semua');
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', '');
        $page = (int) $request->input('page', 1);
        $perPage = 10;

        $pemasukanQuery = Pemasukan::with('kategoriPemasukan');
        $pengeluaranQuery = Pengeluaran::with('kategoriPengeluaran');

        $pemasukanQuery->whereYear('created_at', $year);
        $pengeluaranQuery->whereYear('created_at', $year);

        if ($month && $period === 'bulan') {
            // Filter by specific month selected by user
            $pemasukanQuery->whereMonth('created_at', $month);
            $pengeluaranQuery->whereMonth('created_at', $month);
        } elseif ($period === 'hari') {
            $pemasukanQuery->whereDate('created_at', today());
            $pengeluaranQuery->whereDate('created_at', today());
        } elseif ($period === 'minggu') {
            $pemasukanQuery->whereDate('created_at', '>=', today()->subDays(7));
            $pengeluaranQuery->whereDate('created_at', '>=', today()->subDays(7));
        }

        $pemasukan = $pemasukanQuery->get();
        $pengeluaran = $pengeluaranQuery->get();

        $transaksi = collect()
            ->merge($pemasukan->map(fn($p) => [
                'tanggal' => $p->created_at->format('d-m-Y'),
                'kategori' => $p->kategoriPemasukan?->nama ?? 'Pemasukan',
                'nominal' => $p->jumlah,
                'type' => 'masuk',
            ]))
            ->merge($pengeluaran->map(fn($p) => [
                'tanggal' => $p->created_at->format('d-m-Y'),
                'kategori' => $p->kategoriPengeluaran?->nama ?? 'Pengeluaran',
                'nominal' => $p->jumlah,
                'type' => 'keluar',
            ]))
            ->sortByDesc('tanggal')
            ->values();

        if ($search) {
            $transaksi = $transaksi->filter(fn($t) =>
                str_contains(strtolower($t['tanggal']), strtolower($search)) ||
                str_contains(strtolower($t['kategori']), strtolower($search))
            )->values();
        }

        $totalPemasukan = $transaksi->where('type', 'masuk')->sum('nominal');
        $totalPengeluaran = $transaksi->where('type', 'keluar')->sum('nominal');
        $totalData = $transaksi->count();
        $lastPage = max(1, (int) ceil($totalData / $perPage));
        $currentPage = min(max(1, $page), $lastPage);

        $paged = $transaksi->forPage($currentPage, $perPage)->values();

        return response()->json([
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldo' => $totalPemasukan - $totalPengeluaran,
            'transaksi' => $paged->map(fn($t) => [
                'tanggal' => $t['tanggal'],
                'kategori' => $t['kategori'],
                'nominal' => ($t['type'] === 'masuk' ? '+ ' : '- ') . number_format($t['nominal'], 0, ',', '.'),
                'type' => $t['type'],
            ]),
            'totalData' => $totalData,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'perPage' => $perPage,
        ]);
    }

    public function exportLaporan(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', '');

        $pemasukanQuery = Pemasukan::with('kategoriPemasukan');
        $pengeluaranQuery = Pengeluaran::with('kategoriPengeluaran');

        $pemasukanQuery->whereYear('created_at', $year);
        $pengeluaranQuery->whereYear('created_at', $year);

        if ($month) {
            $pemasukanQuery->whereMonth('created_at', $month);
            $pengeluaranQuery->whereMonth('created_at', $month);
        }

        $pemasukan = $pemasukanQuery->get();
        $pengeluaran = $pengeluaranQuery->get();

        $transaksi = collect()
            ->merge($pemasukan->map(fn($p) => [
                'tanggal' => $p->created_at->format('d-m-Y'),
                'kategori' => $p->kategoriPemasukan?->nama ?? 'Pemasukan',
                'nominal' => $p->jumlah,
                'type' => 'masuk',
            ]))
            ->merge($pengeluaran->map(fn($p) => [
                'tanggal' => $p->created_at->format('d-m-Y'),
                'kategori' => $p->kategoriPengeluaran?->nama ?? 'Pengeluaran',
                'nominal' => $p->jumlah,
                'type' => 'keluar',
            ]))
            ->sortByDesc('tanggal')
            ->values();

        $totalPemasukan = $transaksi->where('type', 'masuk')->sum('nominal');
        $totalPengeluaran = $transaksi->where('type', 'keluar')->sum('nominal');

        $label = $month
            ? date('F', mktime(0, 0, 0, (int) $month, 1)) . " $year"
            : (string) $year;
        $filename = $month
            ? "Laporan-Keuangan-$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . ".xlsx"
            : "Laporan-Keuangan-$year.xlsx";

        $writer = new Writer();
        $writer->openToBrowser($filename);

        $writer->addRow(Row::fromValues(['Laporan Keuangan', $label, '', '']));
        $writer->addRow(Row::fromValues(['Tanggal', 'Kategori', 'Nominal', 'Status']));

        foreach ($transaksi as $t) {
            $nominal = $t['type'] === 'masuk'
                ? '+ ' . number_format($t['nominal'], 0, ',', '.')
                : '- ' . number_format($t['nominal'], 0, ',', '.');
            $status = $t['type'] === 'masuk' ? 'Pemasukan' : 'Pengeluaran';
            $writer->addRow(Row::fromValues([$t['tanggal'], $t['kategori'], $nominal, $status]));
        }

        $writer->addRow(Row::fromValues(['', '', '', '']));
        $writer->addRow(Row::fromValues(['Total Pemasukan', '', 'Rp ' . number_format($totalPemasukan, 0, ',', '.'), '']));
        $writer->addRow(Row::fromValues(['Total Pengeluaran', '', 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'), '']));
        $writer->addRow(Row::fromValues(['Saldo Akhir', '', 'Rp ' . number_format($totalPemasukan - $totalPengeluaran, 0, ',', '.'), '']));

        $writer->close();
        exit;
    }

    // ---------------------------------------------------------------
    //  Import & Template Transaksi
    // ---------------------------------------------------------------

    public function downloadTemplateTransaksi()
    {
        $kategoriPemasukan = KategoriPemasukan::pluck('nama');
        $kategoriPengeluaran = KategoriPengeluaran::pluck('nama');

        $writer = new Writer();
        $writer->openToBrowser('Template-Import-Transaksi.xlsx');

        // ── Header instructions ──
        $writer->addRow(Row::fromValues([
            'PETUNJUK: Baris 1 = petunjuk ini, Baris 2 = header kolom, Baris 3 ke atas = data. Hapus baris contoh sebelum import.',
            '', '', '', '', '', '', '', '',
        ]));
        $writer->addRow(Row::fromValues([
            'NOTE: Nama kategori tidak peka huruf besar/kecil. "IURAN", "Iuran", "iuran" dianggap sama. Jika kategori belum ada, sistem akan membuat baru otomatis.',
            '', '', '', '', '', '', '', '',
        ]));

        // ── Column headers ──
        $writer->addRow(Row::fromValues([
            'tipe', 'tanggal', 'kategori', 'jumlah', 'keterangan',
            'anggota_id', 'tahun', 'bulan_mulai', 'jumlah_bulan',
        ]));

        // ── Example rows ──
        $writer->addRow(Row::fromValues([
            'pemasukan', date('Y-m-d'), $kategoriPemasukan->first() ?? 'Contoh',
            500000, 'Contoh pemasukan', '', '', '', '',
        ]));
        $writer->addRow(Row::fromValues([
            'pengeluaran', date('Y-m-d'), $kategoriPengeluaran->first() ?? 'Contoh',
            100000, 'Contoh pengeluaran', '', '', '', '',
        ]));
        $writer->addRow(Row::fromValues([
            'iuran', date('Y-m-d'), '',
            50000, 'Iuran 6 bulan', '1', date('Y'), '1', '6',
        ]));

        $writer->addRow(Row::fromValues(['', '', '', '', '', '', '', '', '']));

        // ── Available categories list ──
        $writer->addRow(Row::fromValues([
            'DAFTAR KATEGORI YANG TERSEDIA:',
            '', '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'Kategori Pemasukan',
            $kategoriPemasukan->implode(', ') ?: '(belum ada)',
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'Kategori Pengeluaran',
            $kategoriPengeluaran->implode(', ') ?: '(belum ada)',
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'NOTE: Jika kategori belum ada di daftar, sistem akan otomatis membuat kategori baru.',
            '', '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues(['', '', '', '', '', '', '', '', '']));

        // ── Column descriptions ──
        $writer->addRow(Row::fromValues([
            'KETERANGAN KOLOM:',
            '', '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'tipe',
            'Wajib. Isi "pemasukan", "pengeluaran", atau "iuran".',
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'tanggal',
            'Wajib. Format YYYY-MM-DD, contoh: 2024-01-15',
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'kategori',
            'Wajib untuk pemasukan/pengeluaran. Kosongkan untuk iuran. Tidak peka huruf besar/kecil (contoh: "Iuran", "IURAN", "iuran" dianggap sama). Jika kategori belum ada, sistem akan membuat kategori baru otomatis.',
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'jumlah',
            'Wajib. Angka tanpa titik/koma. Untuk iuran: nominal per bulan.',
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'keterangan',
            'Opsional. Deskripsi transaksi.',
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'anggota_id',
            'Wajib untuk iuran. ID anggota dari sistem.',
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'tahun',
            'Wajib untuk iuran. Contoh: 2024',
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'bulan_mulai',
            'Wajib untuk iuran. Bulan mulai (1-12).',
            '', '', '', '', '', '', '',
        ]));

        $writer->addRow(Row::fromValues([
            'jumlah_bulan',
            'Wajib untuk iuran. Jumlah bulan (1-12).',
            '', '', '', '', '', '', '',
        ]));

        $writer->close();
        exit;
    }

    public function importTransaksi(Request $request)
    {
        $request->validate([
            'file_import' => 'required|file|mimes:xlsx,xls,ods|max:5120',
        ]);

        $file = $request->file('file_import');
        $filePath = $file->getRealPath();

        $reader = new Reader();
        $reader->open($filePath);

        // Load existing categories with case-insensitive lookup maps
        // Key = lowercase nama, Value = ['id' => ..., 'nama' => original]
        $kategoriPemasukanAll = KategoriPemasukan::all()->keyBy(fn ($k) => strtolower($k->nama));
        $kategoriPengeluaranAll = KategoriPengeluaran::all()->keyBy(fn ($k) => strtolower($k->nama));

        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $idx => $row) {
                if ($idx <= 2) continue;
                $cells = $row->toArray();
                $tipe = trim((string) ($cells[0] ?? ''));
                if (empty($tipe)) continue;
                $rows[$idx] = [
                    'tipe' => $tipe,
                    'tanggal' => trim((string) ($cells[1] ?? '')),
                    'kategori' => trim((string) ($cells[2] ?? '')),
                    'jumlah' => $cells[3] ?? '',
                    'keterangan' => trim((string) ($cells[4] ?? '')),
                    'anggota_id' => trim((string) ($cells[5] ?? '')),
                    'tahun' => trim((string) ($cells[6] ?? '')),
                    'bulan_mulai' => (int) ($cells[7] ?? 0),
                    'jumlah_bulan' => (int) ($cells[8] ?? 0),
                ];
            }
        }
        $reader->close();

        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'File kosong atau tidak ada data yang bisa diimport.',
                'berhasil' => 0,
                'gagal' => 0,
                'errors' => ['Tidak ada baris data ditemukan. Pastikan file tidak kosong dan baris pertama (header) sudah benar.'],
            ]);
        }

        // ── Phase 1: Validate all rows before importing anything ──
        $errors = [];
        $validatedRows = [];
        $newKategoriPemasukan = []; // nama => true (to track duplicates in file)
        $newKategoriPengeluaran = [];

        foreach ($rows as $idx => $r) {
            $tipe = $r['tipe'];
            $tanggal = $r['tanggal'];
            $kategoriNama = $r['kategori'];
            $jumlah = $r['jumlah'];
            $keterangan = $r['keterangan'];
            $anggotaId = $r['anggota_id'];
            $tahun = $r['tahun'];
            $bulanMulai = $r['bulan_mulai'];
            $jumlahBulan = $r['jumlah_bulan'];

            // Validate tipe
            if (!in_array($tipe, ['pemasukan', 'pengeluaran', 'iuran'])) {
                $errors[] = "Baris $idx: tipe \"$tipe\" tidak valid. Gunakan \"pemasukan\", \"pengeluaran\", atau \"iuran\".";
                continue;
            }

            // Validate pemasukan / pengeluaran
            if ($tipe === 'pemasukan' || $tipe === 'pengeluaran') {
                if (empty($tanggal)) {
                    $errors[] = "Baris $idx: kolom tanggal wajib diisi.";
                } elseif (!\Carbon\Carbon::hasFormat($tanggal, 'Y-m-d')) {
                    $errors[] = "Baris $idx: format tanggal \"$tanggal\" tidak valid. Gunakan format YYYY-MM-DD.";
                }
                if (empty($kategoriNama)) {
                    $errors[] = "Baris $idx: kolom kategori wajib diisi.";
                } else {
                    // Case-insensitive lookup: normalize to lowercase for comparison
                    $kategoriKey = strtolower(trim($kategoriNama));

                    if ($tipe === 'pemasukan') {
                        if (!isset($kategoriPemasukanAll[$kategoriKey])) {
                            // Check if already queued as new in this import batch
                            if (!isset($newKategoriPemasukan[$kategoriKey])) {
                                $newKategoriPemasukan[$kategoriKey] = $kategoriNama;
                            }
                        }
                    } else {
                        if (!isset($kategoriPengeluaranAll[$kategoriKey])) {
                            if (!isset($newKategoriPengeluaran[$kategoriKey])) {
                                $newKategoriPengeluaran[$kategoriKey] = $kategoriNama;
                            }
                        }
                    }
                }
                if ($jumlah === '' || !is_numeric($jumlah) || (float) $jumlah <= 0) {
                    $errors[] = "Baris $idx: jumlah harus angka lebih dari 0.";
                }
                if (!empty($keterangan) && mb_strlen($keterangan) > 1000) {
                    $errors[] = "Baris $idx: keterangan maksimal 1000 karakter.";
                }
            }

            // Validate iuran
            if ($tipe === 'iuran') {
                if (empty($anggotaId)) {
                    $errors[] = "Baris $idx: kolom anggota_id wajib diisi untuk iuran.";
                } elseif (!Anggota::find($anggotaId)) {
                    $errors[] = "Baris $idx: anggota dengan ID $anggotaId tidak ditemukan.";
                }
                if (empty($tahun) || !ctype_digit($tahun) || (int) $tahun < 2020) {
                    $errors[] = "Baris $idx: tahun harus angka valid (minimal 2020).";
                }
                if ($bulanMulai < 1 || $bulanMulai > 12) {
                    $errors[] = "Baris $idx: bulan_mulai harus antara 1-12.";
                }
                if ($jumlahBulan < 1 || $jumlahBulan > 12) {
                    $errors[] = "Baris $idx: jumlah_bulan harus antara 1-12.";
                }
                if ($bulanMulai > 0 && $jumlahBulan > 0 && ($bulanMulai + $jumlahBulan - 1) > 12) {
                    $errors[] = "Baris $idx: kombinasi bulan_mulai ($bulanMulai) + jumlah_bulan ($jumlahBulan) melebihi 12 bulan.";
                }
                if ($jumlah === '' || !is_numeric($jumlah) || (float) $jumlah <= 0) {
                    $errors[] = "Baris $idx: jumlah (nominal per bulan) harus angka lebih dari 0.";
                }

                // Validate tanggungan iuran
                if (isset($anggotaId) && Anggota::find($anggotaId) && !empty($tahun) && ctype_digit($tahun)) {
                    $anggota = Anggota::find($anggotaId);
                    $tahunMulai = max(2025, $anggota->tanggal_aktif_kembali
                        ? (int) \Carbon\Carbon::parse($anggota->tanggal_aktif_kembali)->format('Y')
                        : (int) $anggota->created_at->format('Y'));
                    if ((int) $tahun < $tahunMulai) {
                        $errors[] = "Baris $idx: anggota ini mulai aktif tahun $tahunMulai, tidak bisa bayar iuran tahun $tahun.";
                    }
                    for ($y = $tahunMulai; $y < (int) $tahun; $y++) {
                        $bulanLunas = IuranTahunan::where('anggota_id', $anggotaId)
                            ->where('tahun', $y)
                            ->where('status', 'lunas')
                            ->count();
                        if ($bulanLunas < 12) {
                            $errors[] = "Baris $idx: anggota masih memiliki tanggungan iuran tahun $y yang belum lunas.";
                        }
                    }
                }
            }

            $validatedRows[$idx] = $r;
        }

        // If any validation error, abort — nothing gets imported
        if (!empty($errors)) {
            $total = count($rows);
            $errorCount = count($errors);
            return response()->json([
                'success' => false,
                'message' => "Import dibatalkan: $errorCount error ditemukan dari $total baris data. Tidak ada data yang diimport.",
                'berhasil' => 0,
                'gagal' => $total,
                'errors' => $errors,
            ]);
        }

        // ── Phase 2: All validation passed — import everything in one transaction ──
        \DB::beginTransaction();
        $berhasil = 0;
        $createdCategories = [];

        try {
            // Auto-create new kategori pemasukan
            foreach ($newKategoriPemasukan as $key => $originalNama) {
                $kategori = KategoriPemasukan::create(['nama' => $originalNama]);
                $kategoriPemasukanAll[$key] = $kategori;
                $createdCategories[] = "Kategori pemasukan baru: \"$originalNama\"";
            }

            // Auto-create new kategori pengeluaran
            foreach ($newKategoriPengeluaran as $key => $originalNama) {
                $kategori = KategoriPengeluaran::create(['nama' => $originalNama]);
                $kategoriPengeluaranAll[$key] = $kategori;
                $createdCategories[] = "Kategori pengeluaran baru: \"$originalNama\"";
            }

            foreach ($validatedRows as $idx => $r) {
                $tipe = $r['tipe'];
                $tanggal = $r['tanggal'];
                $kategoriNama = $r['kategori'];
                $jumlah = $r['jumlah'];
                $keterangan = $r['keterangan'];
                $anggotaId = $r['anggota_id'];
                $tahun = $r['tahun'];
                $bulanMulai = $r['bulan_mulai'];
                $jumlahBulan = $r['jumlah_bulan'];

                if ($tipe === 'pemasukan') {
                    $kategoriKey = strtolower(trim($kategoriNama));
                    $kategoriId = $kategoriPemasukanAll[$kategoriKey]->id;
                    Pemasukan::create([
                        'tanggal' => $tanggal,
                        'kategori_pemasukan_id' => $kategoriId,
                        'jumlah' => (float) $jumlah,
                        'keterangan' => $keterangan ?: null,
                        'created_by' => auth()->id(),
                    ]);
                } elseif ($tipe === 'pengeluaran') {
                    $kategoriKey = strtolower(trim($kategoriNama));
                    $kategoriId = $kategoriPengeluaranAll[$kategoriKey]->id;
                    Pengeluaran::create([
                        'tanggal' => $tanggal,
                        'kategori_pengeluaran_id' => $kategoriId,
                        'jumlah' => (float) $jumlah,
                        'keterangan' => $keterangan ?: null,
                        'created_by' => auth()->id(),
                    ]);
                } elseif ($tipe === 'iuran') {
                    $anggota = Anggota::find($anggotaId);
                    $nominalPerBulan = (float) $jumlah;
                    $totalNominal = $nominalPerBulan * $jumlahBulan;
                    $bulanAkhir = min($bulanMulai + $jumlahBulan - 1, 12);

                    $pemasukan = Pemasukan::create([
                        'tanggal' => $tanggal,
                        'kategori_pemasukan_id' => 1,
                        'jumlah' => $totalNominal,
                        'keterangan' => 'Iuran ' . $jumlahBulan . ' bln' . ($keterangan ? ' - ' . $keterangan : ''),
                        'created_by' => auth()->id(),
                    ]);

                    for ($b = $bulanMulai; $b <= $bulanAkhir; $b++) {
                        IuranTahunan::create([
                            'tahun' => (int) $tahun,
                            'anggota_id' => $anggotaId,
                            'bulan' => $b,
                            'nominal' => $nominalPerBulan,
                            'status' => 'lunas',
                            'tanggal_bayar' => $tanggal,
                            'verified_by' => auth()->id(),
                            'pemasukan_id' => $pemasukan->id,
                        ]);
                    }
                }

                $berhasil++;
            }

            \DB::commit();

            $msg = "Import berhasil: $berhasil baris data telah diimport.";
            if (!empty($createdCategories)) {
                $msg .= ' Kategori baru dibuat: ' . implode(', ', $createdCategories) . '.';
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'berhasil' => $berhasil,
                'gagal' => 0,
                'errors' => [],
                'new_categories' => $createdCategories,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import dibatalkan: terjadi error saat menyimpan data. Seluruh data telah di-rollback.',
                'berhasil' => 0,
                'gagal' => count($validatedRows),
                'errors' => ['Error teknis: ' . $e->getMessage()],
            ]);
        }
    }

    // ---------------------------------------------------------------
    //  Export Transaksi
    // ---------------------------------------------------------------

    public function exportTransaksi(Request $request)
    {
        $tipe = $request->input('tipe', '');
        $kategoriType = $request->input('kategori_type', '');
        $kategoriId = $request->input('kategori_id', '');
        $tanggalMulai = $request->input('tanggal_mulai', '');
        $tanggalAkhir = $request->input('tanggal_akhir', '');

        $transaksi = collect();

        if ($tipe === '' || $tipe === 'pemasukan') {
            $query = Pemasukan::with('kategoriPemasukan');

            if ($kategoriId !== '' && ($tipe === 'pemasukan' || $kategoriType === 'pemasukan')) {
                $query->where('kategori_pemasukan_id', $kategoriId);
            }
            if ($tanggalMulai !== '') {
                $query->where('tanggal', '>=', $tanggalMulai);
            }
            if ($tanggalAkhir !== '') {
                $query->where('tanggal', '<=', $tanggalAkhir);
            }

            $transaksi = $transaksi->merge($query->get()->map(fn($p) => [
                'tanggal' => $p->tanggal instanceof \Carbon\Carbon
                    ? $p->tanggal->format('d-m-Y')
                    : \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y'),
                'tipe' => 'Pemasukan',
                'kategori' => $p->kategoriPemasukan?->nama ?? '-',
                'nominal' => $p->jumlah,
                'keterangan' => $p->keterangan ?? '-',
                'created_at' => $p->created_at,
            ]));
        }

        if ($tipe === '' || $tipe === 'pengeluaran') {
            $query = Pengeluaran::with('kategoriPengeluaran');

            if ($kategoriId !== '' && ($tipe === 'pengeluaran' || $kategoriType === 'pengeluaran')) {
                $query->where('kategori_pengeluaran_id', $kategoriId);
            }
            if ($tanggalMulai !== '') {
                $query->where('tanggal', '>=', $tanggalMulai);
            }
            if ($tanggalAkhir !== '') {
                $query->where('tanggal', '<=', $tanggalAkhir);
            }

            $transaksi = $transaksi->merge($query->get()->map(fn($p) => [
                'tanggal' => $p->tanggal instanceof \Carbon\Carbon
                    ? $p->tanggal->format('d-m-Y')
                    : \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y'),
                'tipe' => 'Pengeluaran',
                'kategori' => $p->kategoriPengeluaran?->nama ?? '-',
                'nominal' => $p->jumlah,
                'keterangan' => $p->keterangan ?? '-',
                'created_at' => $p->created_at,
            ]));
        }

        $transaksi = $transaksi->sortByDesc('created_at')->values();

        if ($transaksi->isEmpty()) {
            return redirect()->back()->with('export_error', 'Data transaksi tidak ditemukan berdasarkan filter yang dipilih.');
        }

        $filename = 'data-transaksi-' . now()->format('Y-m-d') . '.xlsx';

        $writer = new Writer();
        $writer->openToBrowser($filename);

        $writer->addRow(Row::fromValues(['Data Transaksi', now()->format('d-m-Y'), '', '', '']));
        $writer->addRow(Row::fromValues(['Tanggal', 'Tipe', 'Kategori', 'Nominal', 'Keterangan']));

        foreach ($transaksi as $t) {
            $nominal = $t['tipe'] === 'Pemasukan'
                ? '+ ' . number_format($t['nominal'], 0, ',', '.')
                : '- ' . number_format($t['nominal'], 0, ',', '.');
            $writer->addRow(Row::fromValues([
                $t['tanggal'],
                $t['tipe'],
                $t['kategori'],
                $nominal,
                $t['keterangan'],
            ]));
        }

        $totalPemasukan = $transaksi->where('tipe', 'Pemasukan')->sum('nominal');
        $totalPengeluaran = $transaksi->where('tipe', 'Pengeluaran')->sum('nominal');

        $writer->addRow(Row::fromValues(['', '', '', '', '']));
        $writer->addRow(Row::fromValues(['Total Pemasukan', '', '', '+ Rp ' . number_format($totalPemasukan, 0, ',', '.'), '']));
        $writer->addRow(Row::fromValues(['Total Pengeluaran', '', '', '- Rp ' . number_format($totalPengeluaran, 0, ',', '.'), '']));
        $writer->addRow(Row::fromValues(['Saldo', '', '', 'Rp ' . number_format($totalPemasukan - $totalPengeluaran, 0, ',', '.'), '']));

        $writer->close();
        exit;
    }

    // ---------------------------------------------------------------
    //  Verifikasi
    // ---------------------------------------------------------------

    public function verifikasi()
    {
        $calonAnggota = CalonAnggota::where('status', 'dalam_pembayaran')->latest()->paginate(20);
        return view('dashboard.bendahara.verifikasi', compact('calonAnggota'));
    }

    public function getVerifikasiData()
    {
        $calon = CalonAnggota::where('status', 'dalam_pembayaran')
            ->latest()
            ->get();

        return response()->json([
            'data' => $calon->map(fn($c, $i) => [
                'no' => $i + 1,
                'id' => $c->id,
                'tanggal' => $c->created_at->format('d/m/Y'),
                'nama' => $c->nama,
                'telepon' => $c->telepon,
                'status' => 'Menunggu Pembayaran',
                'is_lunas' => false,
            ]),
        ]);
    }

    public function verifikasiPembayaran($id)
    {
        $calon = CalonAnggota::findOrFail($id);

        if ($calon->status !== 'dalam_pembayaran') {
            return response()->json(['success' => false, 'message' => 'Status calon anggota tidak valid.'], 400);
        }

        $nominal = 30000;

        // --- Semua perubahan DB dalam transaction ---
        \DB::beginTransaction();

        try {
            // Buat anggota resmi
            $lastId = Anggota::max('id') ?? 0;
            $nomorAnggota = 'RKM-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

            while (Anggota::where('nomor_anggota', $nomorAnggota)->exists()) {
                $lastId++;
                $nomorAnggota = 'RKM-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
            }

            $anggota = Anggota::create([
                'nomor_anggota' => $nomorAnggota,
                'calon_anggota_id' => $calon->id,
                'nama' => $calon->nama,
                'email' => $calon->email,
                'telepon' => $calon->telepon,
                'alamat' => $calon->alamat,
                'status' => 'aktif',
                'created_by' => auth()->id(),
            ]);

            // Catat pemasukan
            $pemasukan = Pemasukan::create([
                'tanggal' => now()->toDateString(),
                'kategori_pemasukan_id' => 3,
                'jumlah' => $nominal,
                'keterangan' => 'Pembayaran pendaftaran - ' . $calon->nama . ' (' . $nomorAnggota . ')',
                'created_by' => auth()->id(),
            ]);

            // Catat pembayaran
            Pembayaran::create([
                'tanggal_daftar' => now()->toDateString(),
                'calon_anggota_id' => $calon->id,
                'nominal_pembayaran' => $nominal,
                'status' => 'sudah_dibayar',
                'verified_by' => auth()->id(),
                'pemasukan_id' => $pemasukan->id,
            ]);

            // Update status calon anggota
            $calon->update(['status' => 'disetujui']);

            LogSuperadmin::create([
                'user_id' => auth()->id(),
                'aksi' => 'create',
                'deskripsi' => 'Verifikasi pembayaran pendaftaran: ' . $calon->nama . ' - ' . $nomorAnggota,
                'modul' => 'bendahara',
                'referensi_id' => $calon->id,
                'ip_address' => request()->ip(),
            ]);

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Gagal verifikasi pembayaran: ' . $e->getMessage(), [
                'calon_id' => $calon->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Gagal memproses verifikasi: ' . $e->getMessage()], 500);
        }

        // --- Kirim kwitansi via WhatsApp (di luar transaction) ---
        $waStatus = 'tidak_aktif';
        if ($calon->telepon && $this->wa()->isReady()) {
            try {
                $no = $this->kwitansiNo(now()->format('Y'), $pemasukan->id);

                $pesan = "Assalamu'alaikum *{$calon->nama}*,\n\n"
                    . "Pembayaran pendaftaran Anda telah kami terima:\n\n"
                    . "• Jenis: Pembayaran pendaftaran anggota baru\n"
                    . "• Nomor Anggota: *{$nomorAnggota}*\n"
                    . '• Nominal: Rp ' . number_format($nominal, 0, ',', '.') . "\n"
                    . '• Tanggal: ' . now()->format('d/m/Y') . "\n\n"
                    . "Status Anda: *AKTIF*\n\n"
                    . "Terlampir kwitansi pembayaran.\n\n"
                    . "Selamat, Anda telah resmi menjadi anggota RKM Al-Jannah! 🎉\n"
                    . 'Terima kasih 🙏' . "\n"
                    . '— Bendahara RKM Al-Jannah';

                $base64 = $this->kwitansi()->pdf([
                    'no' => $no,
                    'nama' => $calon->nama,
                    'peruntukan' => 'Pembayaran pendaftaran anggota baru',
                    'nominal' => $nominal,
                    'tanggal' => now()->format('d/m/Y'),
                    'dibayar_oleh' => auth()->user()->nama ?? 'Bendahara',
                ]);

                $res = $this->wa()->sendDocument($calon->telepon, $base64, 'Kwitansi-' . $no . '.pdf', 'application/pdf', $pesan);

                if (isset($res['error'])) {
                    \Illuminate\Support\Facades\Log::warning('WA kwitansi gagal: ' . $res['error'], [
                        'calon' => $calon->nama,
                        'telepon' => $calon->telepon,
                    ]);

                    // Fallback: kirim teks tanpa lampiran
                    $resText = $this->wa()->send($calon->telepon, $pesan);
                    $waStatus = isset($resText['error']) ? 'gagal' : 'terkirim_teks';
                } else {
                    $waStatus = 'terkirim';
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error kirim kwitansi WA: ' . $e->getMessage(), [
                    'calon' => $calon->nama,
                    'telepon' => $calon->telepon,
                    'trace' => $e->getTraceAsString(),
                ]);
                $waStatus = 'gagal';
            }
        }

        return response()->json(['success' => true, 'wa' => $waStatus, 'nomor_anggota' => $nomorAnggota]);
    }

    // ---------------------------------------------------------------
    //  Permintaan Izin
    // ---------------------------------------------------------------

    public function storePermintaanIzin(Request $request)
    {
        $validated = $request->validate([
            'target_table' => 'required|string',
            'target_id' => 'required|integer',
            'field_name' => 'required|string',
            'old_value' => 'nullable|string',
            'new_value' => 'nullable|string',
            'alasan' => 'required|string|max:2000',
        ]);

        $permintaan = PermintaanIzin::create([
            'user_id' => auth()->id(),
            'target_table' => $validated['target_table'],
            'target_id' => $validated['target_id'],
            'field_name' => $validated['field_name'],
            'old_value' => $validated['old_value'],
            'new_value' => $validated['new_value'],
            'alasan' => $validated['alasan'],
            'status' => 'menunggu',
        ]);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'deskripsi' => 'Mengirim permintaan izin edit: ' . $validated['target_table'] . ' #' . $validated['target_id'] . ' - ' . $validated['field_name'],
            'modul' => 'bendahara',
            'referensi_id' => $permintaan->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan akses edit berhasil dikirim ke ketua.',
            'data' => $permintaan,
        ]);
    }
}
