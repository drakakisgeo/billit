<?php

namespace Drakakisgeo\Billit;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class BillitServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(Billit::class, function (Application $app) {
            $config = (array) $app->make(Repository::class)->get('services.billit', []);
            $sandbox = filter_var($config['sandbox'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $version = $config['version'] ?? 'v1';

            return new Billit(
                (string) ($config['token'] ?? ''),
                $sandbox,
                $version,
                Billit::httpClient(
                    $sandbox,
                    $version,
                    (float) ($config['timeout'] ?? Billit::DEFAULT_TIMEOUT),
                    (float) ($config['connect_timeout'] ?? Billit::DEFAULT_CONNECT_TIMEOUT),
                ),
            );
        });

        $this->app->alias(Billit::class, 'billit');
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [Billit::class, 'billit'];
    }
}
