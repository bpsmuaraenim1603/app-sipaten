<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BpsPegawaiApi
{
    public function getUserByUsername(string $username): ?array
    {
        $base = rtrim(config('services.bps_api.base_url'), '/');
        $realm = config('services.bps_api.realm');

        $tokenUrl = "{$base}/auth/realms/{$realm}/protocol/openid-connect/token";
        $apiUrl   = "{$base}/auth/admin/realms/{$realm}/users";

        $tokenResp = Http::asForm()->post($tokenUrl, [
            'grant_type'    => 'client_credentials',
            'client_id'     => config('services.bps_api.client_id'),
            'client_secret' => config('services.bps_api.client_secret'),
        ]);

        if (!$tokenResp->successful()) {
            return null;
        }

        $accessToken = $tokenResp->json('access_token');
        if (!$accessToken) {
            return null;
        }

        $usersResp = Http::withToken($accessToken)->get($apiUrl, [
            'username' => $username,
        ]);

        if (!$usersResp->successful()) {
            return null;
        }

        $users = $usersResp->json();
        return is_array($users) && count($users) ? $users[0] : null;
    }
}
