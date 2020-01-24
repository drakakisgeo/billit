<?php

namespace Drakakisgeo\Billit;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class BillitServiceProvider extends ServiceProvider
{

    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind('billit', function ($app) {
            $token = $app['config']['services']['billit'];
            return new Billit($token, new Client([
                'base_uri' => 'https://api.billit.io',
                'verify' => $this->app->environment('local','testing') ? false : true,
                'timeout' => 2.0,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json'
                ]
            ]));
        });
    }

}