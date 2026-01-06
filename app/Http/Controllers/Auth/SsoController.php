<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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

    public function callback(Request $request)
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
        $name     = $owner->getName();
        $nip      = $owner->getNip();
        $email    = $owner->getEmail();

        $user = User::updateOrCreate(
            ['username' => $username],
            [
                'name'  => $name ?? $username,
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
            ]
        );

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
