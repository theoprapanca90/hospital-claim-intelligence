<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | HCI memakai token Bearer untuk API. Daftar ini hanya relevan bila nanti
    | ada SPA first-party yang memakai cookie. Isi lewat .env, jangan hardcode
    | domain produksi di sini.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
    ))),

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | Token tidak boleh berlaku selamanya. Default 480 menit (8 jam). Menyetel
    | nilai kosong/null akan membuat token tidak pernah kedaluwarsa, jadi
    | jangan lakukan itu untuk data klinis.
    |
    */

    'expiration' => (int) env('SANCTUM_EXPIRATION', 480),

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
