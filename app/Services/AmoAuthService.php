<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;


class AmoAuthService
{
    const ACCESS_TOKEN_KEY = "ACCESS_TOKEN=";
    const REFRESH_TOKEN_KEY = "REFRESH_TOKEN=";
    const EXPIRES_IN_KEY = "EXPIRES_IN=";
    const BASE_DOMAIN_KEY = "BASE_DOMAIN=";
    const RESOURCE_OWNER_ID_KEY = "RESOURCE_OWNER_ID=";
    const CLIENT_ID_KEY = "CLIENT_ID=";
    const CLIENT_SECRET_KEY = "CLIENT_SECRET=";
    const REDIRECT_URI_KEY =  "REDIRECT_URI=";


    public function storeTokensInFile(array $tokens): void
    {
        $file  = fopen(base_path() . '/.env', 'a', true);

        fwrite($file,"\n");
        fwrite($file, self::ACCESS_TOKEN_KEY      . $tokens['access_token'] . "\n");
        fwrite($file, self::REFRESH_TOKEN_KEY     . $tokens['refresh_token'] . "\n");
        fwrite($file, self::EXPIRES_IN_KEY        . $tokens['expires_in'] . "\n");
        fwrite($file, self::BASE_DOMAIN_KEY       . $tokens['base_domain'] . "\n");
        fwrite($file, self::RESOURCE_OWNER_ID_KEY . $tokens['resource_owner_id'] . "\n");
        fwrite($file, self::CLIENT_ID_KEY         . $tokens['client_id'] . "\n");
        fwrite($file, self::CLIENT_SECRET_KEY     . $tokens['client_secret'] . "\n");
        fwrite($file, self::REDIRECT_URI_KEY      . $tokens['redirect_uri'] . "\n");

        fclose($file);
    }

    public function overwriteTokensInFile(array $tokens): void
    {
        Artisan::call('config:clear');

        $filePath = base_path() . '/.env';

        $old = [
            self::ACCESS_TOKEN_KEY  .   config('app.access_token'),
            self::REFRESH_TOKEN_KEY .   config('app.refresh_token'),
            self::EXPIRES_IN_KEY    .   config('app.expires_in'),
            self::BASE_DOMAIN_KEY   .   config('app.base_domain'),
        ];

        $new = [
            self::ACCESS_TOKEN_KEY  .   $tokens['access_token'],
            self::REFRESH_TOKEN_KEY .   $tokens['refresh_token'],
            self::EXPIRES_IN_KEY    .   $tokens['expires_in'],
            self::BASE_DOMAIN_KEY   .   $tokens['base_domain'],
        ];

        file_put_contents($filePath, str_replace(
            $old, $new, file_get_contents($filePath)));

        Artisan::call('config:cache');
    }
}
