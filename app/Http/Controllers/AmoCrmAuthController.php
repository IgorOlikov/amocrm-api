<?php

namespace App\Http\Controllers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use App\Http\Requests\CreateIntegrationRequest;
use App\Services\AmoAuthService;
use Illuminate\Http\Request;

class AmoCrmAuthController extends Controller
{

    /**
     * @throws AmoCRMoAuthApiException
     */
    public function createIntegration(CreateIntegrationRequest $request)
    {
        $integrationData = array_values($request->validated());

        [$clientId,$clientSecret,$redirectUri,$authCode,$accountDomain] = $integrationData;

        $apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);

        $apiClient->setAccountBaseDomain($accountDomain);

        $responseTokens = $apiClient->getOAuthClient()->getAccessTokenByCode($authCode);

        $ownerDetails = $apiClient->getOAuthClient()->getResourceOwner($responseTokens);

        $resourceOwnerId = $ownerDetails->getId();

        $responseTokensArray = [
            'access_token' => $responseTokens->getToken(),
            'refresh_token' => $responseTokens->getRefreshToken(),
            'expires_in' => $responseTokens->getExpires(),
            'base_domain' => $apiClient->getAccountBaseDomain(),
            'resource_owner_id' => $resourceOwnerId,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
        ];

        (new AmoAuthService())->storeTokensInFile($responseTokensArray);

        return response($responseTokens,201);
    }

}
