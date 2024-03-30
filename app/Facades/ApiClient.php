<?php

namespace App\Facades;

use AmoCRM\Client\AmoCRMApiClient;
use Illuminate\Support\Facades\Facade;


/**
 * @return AmoCRMApiClient
 */
class ApiClient extends Facade
{


    protected static function getFacadeAccessor()
    {
        return AmoCRMApiClient::class;
    }


}
