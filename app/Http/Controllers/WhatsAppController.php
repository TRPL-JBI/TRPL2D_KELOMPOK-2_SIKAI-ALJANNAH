<?php

namespace App\Http\Controllers;

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

    public function logout()
    {
        $result = $this->service->logout();

        if (!empty($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Koneksi WhatsApp berhasil diputus');
    }
}