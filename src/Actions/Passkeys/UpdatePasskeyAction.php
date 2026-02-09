<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Actions\Passkeys;

use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Services\Result;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class UpdatePasskeyAction implements Actionable
{
    public function execute(Passkey $passkey, string $name): Result
    {
        if ($passkey->name === $name) {
            return Result::ok('passkey-name-updated');
        }

        try {
            Passkey::withoutTimestamps(fn (): bool => $passkey->update(['name' => $name]));

            return Result::ok('passkey-name-updated');
            // @codeCoverageIgnoreStart
        } catch (\Throwable $e) {
            captureException($e, self::class, [
                'passkey_id' => $passkey->id,
                'name' => $name,
            ]);

            return Result::err('not-found');
            // @codeCoverageIgnoreEnd
        }
    }
}
