<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use Drakakisgeo\Billit\BillitServiceProvider;
use Drakakisgeo\Billit\Facades\Billit as BillitFacade;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [BillitServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['Billit' => BillitFacade::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('services.billit', [
            'token' => 'laravel-token',
            'sandbox' => 'true',
            'version' => 'v1',
            'timeout' => 3,
        ]);
    }

    #[Test]
    public function it_resolves_a_configured_singleton(): void
    {
        $billit = $this->app->make(Billit::class);

        $this->assertInstanceOf(Billit::class, $billit);
        $this->assertTrue($billit->isSandbox());
        $this->assertSame($billit, resolve('billit'));
    }

    #[Test]
    public function the_facade_proxies_to_the_client(): void
    {
        $this->assertTrue(BillitFacade::isSandbox());
        $this->assertTrue(\Billit::isSandbox());
    }

    #[Test]
    public function the_composer_alias_points_to_the_facade(): void
    {
        $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);

        $this->assertSame(BillitFacade::class, $composer['extra']['laravel']['aliases']['Billit']);
        $this->assertTrue(class_exists($composer['extra']['laravel']['aliases']['Billit']));
    }
}
