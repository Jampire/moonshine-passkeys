<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\Rule;
use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;
use Jampire\MoonshinePasskeys\Models\Passkey;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
trait HasNameRule
{
    /**
     * @return array<int, string>
     */
    private function getNameRule(Authenticatable|PasskeyContract $user, ?Passkey $ignoring = null): array
    {
        $uniqueRule = Rule::unique(Passkey::class, 'name')
            ->where('personable_type', $user::class)
            ->where('personable_id', $user->id);

        if ($ignoring instanceof Passkey) {
            $uniqueRule->ignore($ignoring);
        }

        return [
            'required',
            'string',
            'max:' . Passkey::NAME_MAX_LENGTH,
            $uniqueRule,
        ];
    }

    private function getNameMessage(): string
    {
        return trans('passkeys::errors.name-already-exists');
    }
}
