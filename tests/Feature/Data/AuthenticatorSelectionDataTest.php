<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Data\AuthenticatorSelectionData;

uses()->group('data');

describe('AuthenticatorSelectionData', function (): void {
    it('stores all constructor properties', function (): void {
        $data = new AuthenticatorSelectionData(
            authenticatorAttachment: 'platform',
            userVerification: 'preferred',
            residentKey: 'required',
        );

        expect($data->authenticatorAttachment)
            ->toBe('platform')
            ->and($data->userVerification)
            ->toBe('preferred')
            ->and($data->residentKey)
            ->toBe('required');
    });

    it('is immutable (readonly)', function (): void {
        $data = new AuthenticatorSelectionData(
            authenticatorAttachment: 'cross-platform',
            userVerification: 'discouraged',
            residentKey: 'preferred',
        );

        expect(fn (): string => $data->authenticatorAttachment = 'platform')
            ->toThrow(\Error::class);
    });
});
