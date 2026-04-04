<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Concerns;

use Jampire\MoonshinePasskeys\Exceptions\PasskeyInvalidInheritanceException;
use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
trait HasInheritanceCheck
{
    /**
     * @throws PasskeyInvalidInheritanceException
     */
    private function checkForRelatedModel(mixed $model = null): void
    {
        $check = is_string($model)
            ? is_subclass_of($model, PasskeyContract::class)
            : $model instanceof PasskeyContract;

        if (!$check) {
            throw new PasskeyInvalidInheritanceException();
        }
    }
}
