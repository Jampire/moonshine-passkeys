<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }
}
