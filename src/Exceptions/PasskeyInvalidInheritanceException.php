<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Exceptions;

use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class PasskeyInvalidInheritanceException extends PasskeyException
{
    public function __construct()
    {
        parent::__construct(trans('passkeys::errors.exceptions.related-model', [
            'user' => config('moonshine.auth.model'),
            'contract' => PasskeyContract::class,
        ]));
    }
}
