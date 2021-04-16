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
            $isSandbox = $app['config']['services']['billit']['sandbox'];
            $version = $app['config']['services']['billit']['version'];
            return new Billit($token, new Client([
                'base_uri' => $isSandbox ? "https://api.sandbox-billit.xyz/{$version}" : "https://api.billit.io/{$version}",
                'timeout' => 2.0,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json'
                ]
            ]));
        });
    }

}