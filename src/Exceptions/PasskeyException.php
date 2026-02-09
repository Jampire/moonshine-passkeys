<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Exceptions;

use Exception;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
class PasskeyException extends Exception
{
    public static function invalidAuthenticatorAttestationResponse(): self
    {
        return new self(trans('passkeys::errors.exceptions.attestation'));
    }

    public static function invalidAuthenticatorAssertionResponse(): self
    {
        return new self(trans('passkeys::errors.exceptions.assertion'));
    }

    public static function invalidPasskey(): self
    {
        return new self(trans('passkeys::errors.exceptions.invalid-passkey'));
    }

    public static function invalidCounter(): self
    {
        return new self(trans('passkeys::errors.exceptions.fake-device'));
    }
}
