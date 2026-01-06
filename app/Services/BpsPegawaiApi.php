<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BpsPegawaiApi
{
    private function base(): string
    {
        // README pakai: https://sso.bps.go.id/auth/
        return rtrim((string) config('services.bps_api.base_url'), '/') . '/auth';
    }

    private function realm(): string
    {
        return (string) config('services.bps_api.realm');
    }

    private function tokenUrl(): string
    {
        return $this->base() . "/realms/{$this->realm()}/protocol/openid-connect/token";
    }

    private function apiUrl(): string
    {
        // ini sesuai README: realms/pegawai-bps/api-pegawai
        return $this->base() . "/realms/{$this->realm()}/api-pegawai";
    }

    private function getClientToken(): string
    {
        $clientId = (string) config('services.bps_api.client_id');
        $clientSecret = (string) config('services.bps_api.client_secret');

        if (!$clientId || !$clientSecret) {
            throw new \RuntimeException("client_id/client_secret kosong. Cek .env");
        }

        $resp = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post($this->tokenUrl(), [
                'grant_type' => 'client_credentials',
            ]);

        if (!$resp->successful()) {
            throw new \RuntimeException("Token gagal: HTTP {$resp->status()} - {$resp->body()}");
        }

        $token = $resp->json('access_token');
        if (!$token) {
            throw new \RuntimeException("access_token kosong. Response: {$resp->body()}");
        }

        return $token;
    }

    public function byUsername(string $username): array
    {
        $token = $this->getClientToken();
        $url = $this->apiUrl() . "/username/" . urlencode($username);

        $resp = Http::withToken($token)->get($url);

        if (!$resp->successful()) {
            throw new \RuntimeException("API /username gagal: HTTP {$resp->status()} - {$resp->body()}");
        }

        $json = $resp->json();
        return is_array($json) ? $json : [];
    }

    public function byEmail(string $email): array
    {
        $token = $this->getClientToken();
        $url = $this->apiUrl() . "/email/" . urlencode($email);

        $resp = Http::withToken($token)->get($url);

        if (!$resp->successful()) {
            throw new \RuntimeException("API /email gagal: HTTP {$resp->status()} - {$resp->body()}");
        }

        $json = $resp->json();
        return is_array($json) ? $json : [];
    }

    public function getPegawai(string $username, ?string $email = null): ?array
    {
        $res = $this->byUsername($username);
        if (count($res) > 0) return $res[0];

        if ($email) {
            $res2 = $this->byEmail($email);
            if (count($res2) > 0) return $res2[0];
        }

        return null;
    }
}
