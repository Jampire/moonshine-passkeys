<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Tests\Fixtures;

use Jampire\MoonshinePasskeys\Models\Concerns\HasPasskeys;
use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;
use MoonShine\Laravel\Models\MoonshineUser;

/**
 * @@author Dzianis Kotau <me@dzianiskotau.com>
 */
final class Admin extends MoonshineUser implements PasskeyContract
{
    use HasPasskeys;

    protected $table = 'moonshine_users';

    protected static function newFactory(): AdminFactory
    {
        return AdminFactory::new();
    }
}
