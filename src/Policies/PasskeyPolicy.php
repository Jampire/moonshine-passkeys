<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Jampire\MoonshinePasskeys\Concerns\IsPersonable;
use Jampire\MoonshinePasskeys\Models\Passkey;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class PasskeyPolicy
{
    use HandlesAuthorization;
    use IsPersonable;

    public function create(Authenticatable $user): Response
    {
        return config('passkeys.enabled')
            ? Response::allow()
            : Response::denyAsNotFound(trans('passkeys::errors.not-found'));
    }

    public function update(Authenticatable $user, Passkey $passkey): Response
    {
        return $this->check($user, $passkey);
    }

    public function delete(Authenticatable $user, Passkey $passkey): Response
    {
        return $this->check($user, $passkey);
    }

    private function check(Authenticatable $user, Passkey $passkey): Response
    {
        return config('passkeys.enabled') && $this->isPersonable($user, $passkey)
            ? Response::allow()
            : Response::denyAsNotFound(trans('passkeys::errors.not-found'));
    }
}
