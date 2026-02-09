<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;
use MoonShine\Laravel\Database\Factories\MoonshineUserFactory;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 * @extends Factory<Admin>
 */
final class AdminFactory extends MoonshineUserFactory
{
    protected $model = Admin::class;
}
