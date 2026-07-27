<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('whatsapp.service_url', 'http://127.0.0.1:3001');
        $this->token = config('whatsapp.api_token', 'aljannah-wa-secret');
    }

    public function status(): array
    {
        return $this->get('/api/status');
    }

    public function connect(string $phone): array
    {
        return $this->post('/api/connect', ['phone' => $phone]);
    }

    public function connectQr(): array
    {
        return $this->post('/api/connect-qr');
    }

    public function send(string $to, string $text): array
    {
        return $this->post('/api/send', ['to' => $to, 'text' => $text]);
    }

    public function logout(): array
    {
        return $this->post('/api/logout');
    }

    public function health(): bool
    {
        try {
            $res = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $res->successful();
        } catch (\Exception) {
            return false;
        }
    }

    protected function get(string $path): array
    {
        try {
            $res = Http::timeout(10)->withHeaders([
                'Authorization' => "Bearer {$this->token}",
            ])->get("{$this->baseUrl}{$path}");

            return $res->successful() ? $res->json() : [
                'error' => $res->json()['error'] ?? 'Service unavailable',
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function post(string $path, array $data = []): array
    {
        try {
            $res = Http::timeout(30)->withHeaders([
                'Authorization' => "Bearer {$this->token}",
            ])->post("{$this->baseUrl}{$path}", $data);

            return $res->successful() ? $res->json() : [
                'error' => $res->json()['error'] ?? 'Service error',
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
