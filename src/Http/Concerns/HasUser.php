<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;
use MoonShine\Laravel\MoonShineAuth;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
trait HasUser
{
    private function getMoonShineUser(): Authenticatable|Model|PasskeyContract|null
    {
        return MoonShineAuth::getGuard()->user() ?? MoonShineAuth::getModel();
    }
}
