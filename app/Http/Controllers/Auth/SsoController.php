<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BpsPegawaiApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use JKD\SSO\Client\Provider\Keycloak;

class SsoController extends Controller
{
    private function provider(): Keycloak
    {
        return new Keycloak([
            'authServerUrl' => config('services.bps_sso.base_url'),
            'realm'         => config('services.bps_sso.realm'),
            'clientId'      => config('services.bps_sso.client_id'),
            'clientSecret'  => config('services.bps_sso.client_secret'),
            'redirectUri'   => config('services.bps_sso.redirect_uri'),
        ]);
    }

    public function redirect(Request $request)
    {
        $provider = $this->provider();

        $authUrl = $provider->getAuthorizationUrl([
            'scope' => config('services.bps_sso.scope'),
        ]);

        $request->session()->put('oauth2state', $provider->getState());
        return redirect()->away($authUrl);
    }

    public function callback(Request $request, BpsPegawaiApi $api)
    {
        $state = $request->session()->pull('oauth2state');

        if (!$request->filled('state') || $request->string('state')->toString() !== (string) $state) {
            abort(403, 'Invalid OAuth state');
        }

        if (!$request->filled('code')) {
            abort(400, 'Missing authorization code');
        }

        $provider = $this->provider();

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $request->string('code')->toString(),
        ]);

        $owner = $provider->getResourceOwner($token);

        $username = $owner->getUsername();
        $name     = $owner->getName() ?? $username;
        $nip      = method_exists($owner, 'getNip') ? $owner->getNip() : null;
        $email    = $owner->getEmail();

        $user = User::where('username', $username)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $user->fill([
                'username' => $username,
                'name'     => $name,
                'email'    => $email,
                'nip'      => $nip ?: $user->nip,
            ])->save();
        } else {
            $user = User::create([
                'username' => $username,
                'name'     => $name,
                'email'    => $email,
                'nip'      => $nip,
                'password' => bcrypt(Str::random(32)),
            ]);
        }

        $pegawai = $api->getUserByUsername($username);
        if (is_array($pegawai)) {
            $attr = $pegawai['attributes'] ?? [];

            $user->fill([
                'jabatan'     => $attr['jabatan'][0] ?? $attr['jabatan_fungsional'][0] ?? $user->jabatan,
                'eselon'      => $attr['eselon'][0] ?? $user->eselon,
                'unit_kerja'  => $attr['unit_kerja'][0] ?? $attr['unitKerja'][0] ?? $user->unit_kerja,
                'satker'      => $attr['satker'][0] ?? $user->satker,
                'golongan'    => $attr['golongan'][0] ?? $user->golongan,
                'pangkat'     => $attr['pangkat'][0] ?? $user->pangkat,
                'foto_url'    => $attr['foto'][0] ?? $attr['photo'][0] ?? $user->foto_url,
                'pegawai_raw' => json_encode($pegawai, JSON_UNESCAPED_UNICODE),
            ])->save();
        }

        if (method_exists($user, 'hasAnyRole') && !$user->hasAnyRole(['Admin', 'Kepala BPS', 'Bagian Umum', 'Pegawai'])) {
            $user->assignRole('Pegawai');
        }

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('sso.redirect');
    }
}
