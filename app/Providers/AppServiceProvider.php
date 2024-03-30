<?php

namespace App\Providers;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AmoAuthService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AmoCRMApiClient::class, function () {

            $apiClient = new AmoCRMApiClient(
                config('app.client_id'),
                config('app.client_secret'),
                config('app.redirect_uri')
            );

            $tokens = new AccessToken([
                'access_token' => config('app.access_token'),
                'expires_in' => config('app.expires_in'),
                'resource_owner_id' => config('app.resource_owner_id'),
                'refresh_token' => config('app.refresh_token')
            ]);

           $apiClient->setAccessToken($tokens)
                ->setAccountBaseDomain(config('app.base_domain'))
                ->onAccessTokenRefresh(function (AccessTokenInterface $tokens, string $baseDomain) {

                    (new AmoAuthService())
                        ->overwriteTokensInFile([
                            'access_token' => $tokens->getToken(),
                            'refresh_token' => $tokens->getRefreshToken(),
                            'expires_in' => $tokens->getExpires(),
                            'base_domain' => $baseDomain,
                        ]);
                });

            return $apiClient;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
