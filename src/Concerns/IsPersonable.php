<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Concerns;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Jampire\MoonshinePasskeys\Models\Passkey;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
trait IsPersonable
{
    public function isPersonable(Authenticatable $user, Passkey $passkey): bool
    {
        return $user::class === $passkey->personable_type && $user->id === $passkey->personable_id;
    }
}
