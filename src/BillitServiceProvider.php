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
            $token = $app['config']['services']['billit']['token'];
            $baseUrl = $app['config']['services']['billit']['baseUrl'];
            return new Billit($token, new Client([
                'base_uri' => $baseUrl,
                'timeout' => 2.0,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json'
                ]
            ]));
        });
    }

}