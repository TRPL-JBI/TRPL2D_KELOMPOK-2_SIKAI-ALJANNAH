<?php

namespace App\Http\Controllers;

use App\Services\KwitansiService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected WhatsAppService $service;

    public function __construct(WhatsAppService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $server = $this->service->serverStatus();
        $serviceUp = $server['reachable'];
        $status = $serviceUp ? $this->service->status() : [];

        return view('dashboard.adminweb.whatsapp', compact('server', 'serviceUp', 'status'));
    }

    public function refresh()
    {
        return response()->json($this->service->status());
    }

    public function serverStatusJson()
    {
        return response()->json($this->service->serverStatus());
    }

    public function startServer()
    {
        $result = $this->service->startServer();

        return response()->json([
            'error' => $result['error'] ?? null,
            'message' => ! empty($result['error']) ? null : 'Server WhatsApp berhasil dimulai.',
        ]);
    }

    public function stopServer()
    {
        $result = $this->service->stopServer();

        return response()->json([
            'error' => $result['error'] ?? null,
            'message' => ! empty($result['error']) ? null : 'Server WhatsApp berhasil dihentikan.',
        ]);
    }

    public function connectQr()
    {
        $result = $this->service->connectQr();

        return response()->json(['error' => $result['error'] ?? null]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'text' => 'required|string',
        ]);

        $result = $this->service->send($request->input('to'), $request->input('text'));

        if (!empty($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Pesan berhasil dikirim');
    }

    public function sendTestFile(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
        ]);

        if (! $this->service->isReady()) {
            return response()->json(['error' => 'WhatsApp belum terhubung.']);
        }

        try {
            $kwitansi = app(KwitansiService::class)->pdf([
                'no' => 'KWT-UJI-'.date('YmdHis'),
                'nama' => 'Anggota Uji Coba',
                'peruntukan' => 'Iuran Bulan '.now()->translatedFormat('F Y'),
                'tanggal' => now()->translatedFormat('d F Y'),
                'nominal' => 10000,
                'dibayar_oleh' => 'Anggota Uji Coba',
            ]);

            $result = $this->service->sendDocument(
                $request->input('to'),
                $kwitansi,
                'Kwitansi-Uji-Coba.pdf',
                'application/pdf',
                'Uji kirim file kwitansi dari AdminWeb.'
            );
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        if (! empty($result['error'])) {
            return response()->json(['error' => $result['error']]);
        }

        return response()->json(['message' => 'File kwitansi berhasil dikirim.']);
    }

    public function logout()
    {
        $result = $this->service->logout();

        if (!empty($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Koneksi WhatsApp berhasil diputus');
    }
}