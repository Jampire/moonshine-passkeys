<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Exceptions\PasskeyException;

uses()->group('exceptions');

describe('PasskeyException', function (): void {
    it('creates invalidAuthenticatorAttestationResponse exception', function (): void {
        $exception = PasskeyException::invalidAuthenticatorAttestationResponse();

        expect($exception)
            ->toBeInstanceOf(PasskeyException::class)
            ->and($exception->getMessage())
            ->toBe(trans('passkeys::errors.exceptions.attestation'));
    });

    it('creates invalidAuthenticatorAssertionResponse exception', function (): void {
        $exception = PasskeyException::invalidAuthenticatorAssertionResponse();

        expect($exception)
            ->toBeInstanceOf(PasskeyException::class)
            ->and($exception->getMessage())
            ->toBe(trans('passkeys::errors.exceptions.assertion'));
    });

    it('creates invalidPasskey exception', function (): void {
        $exception = PasskeyException::invalidPasskey();

        expect($exception)
            ->toBeInstanceOf(PasskeyException::class)
            ->and($exception->getMessage())
            ->toBe(trans('passkeys::errors.exceptions.invalid-passkey'));
    });

    it('creates invalidCounter exception with counter values', function (): void {
        $exception = PasskeyException::invalidCounter();

        expect($exception)
            ->toBeInstanceOf(PasskeyException::class)
            ->and($exception->getMessage())
            ->toBe(trans('passkeys::errors.exceptions.fake-device'));
    });
});
