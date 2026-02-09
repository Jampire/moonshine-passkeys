<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Services;

use Jampire\MoonshinePasskeys\Exceptions\PasskeyException;
use Webauthn\Counter\CounterChecker;
use Webauthn\PublicKeyCredentialSource;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class AuthenticatorCounterChecker implements CounterChecker
{
    public static function make(): self
    {
        return new self();
    }

    /**
     * @throws PasskeyException
     */
    public function check(PublicKeyCredentialSource $publicKeyCredentialSource, int $currentCounter): void
    {
        if ($currentCounter > $publicKeyCredentialSource->counter) {
            return;
        }

        $exception = PasskeyException::invalidCounter();

        captureException($exception, self::class, [
            'source_counter' => $publicKeyCredentialSource->counter,
            'current_counter' => $currentCounter,
        ]);

        throw $exception;
    }
}
