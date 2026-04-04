<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Tests;

use Jampire\MoonshinePasskeys\PasskeyServiceProvider;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;
use MoonShine\Laravel\Providers\MoonShineServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    protected function getPackageProviders($app): array
    {
        return [
            MoonShineServiceProvider::class,
            PasskeyServiceProvider::class,
        ];
    }

    #[\Override]
    protected function defineEnvironment($app): void
    {
        tap($app['config'], function (mixed $config): void {
            $config->set('moonshine.auth.model', Admin::class);
            $config->set('moonshine.prefix', 'admin');
        });
    }
}
