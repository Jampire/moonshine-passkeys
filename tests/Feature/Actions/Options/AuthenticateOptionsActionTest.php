<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Actions\Options\AuthenticateOptionsAction;
use MoonShine\Laravel\Models\MoonshineUser;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialRequestOptions;

uses()->group('actions');

describe('AuthenticateOptionsAction', function (): void {
    beforeEach(function (): void {
        config([
            'passkeys.enabled' => true,
            'passkeys.rp_id' => 'localhost',
            'passkeys.authenticator_selection_criteria.authentication.user_verification' =>
                AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
        ]);
    });

    it('returns error when passkeys are disabled', function (): void {
        config(['passkeys.enabled' => false]);

        $action = new AuthenticateOptionsAction();
        $result = $action->execute();

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('authentication-failed');
    });

    it('returns error when email provided but user has inactive passkeys', function (): void {
        $user = adminWithPasskey(isActive: false);

        $action = new AuthenticateOptionsAction();
        $result = $action->execute(value: $user->email);

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('authentication-failed');
    });

    it('returns options without credentials when no email provided', function (): void {
        $action = new AuthenticateOptionsAction();
        $result = $action->execute();

        expect($result->isOk())
            ->toBeTrue()
            ->and($result->val)
            ->toBeInstanceOf(PublicKeyCredentialRequestOptions::class)
            ->and($result->val->allowCredentials)
            ->toBeEmpty();
    });

    it('returns options with credentials when email provided and passkeys active', function (): void {
        $user = adminWithPasskey();

        $action = new AuthenticateOptionsAction();
        $result = $action->execute(value: $user->email);

        expect($result->isOk())
            ->toBeTrue()
            ->and($result->val)
            ->toBeInstanceOf(PublicKeyCredentialRequestOptions::class)
            ->and($result->val->allowCredentials)
            ->not->toBeEmpty()
            ->and($result->val->userVerification)
            ->toBeNull()
            ->and($result->val->timeout)
            ->toBeNull();
    });

    it('returns options when modelClass does not implement PasskeyContract', function (): void {
        config(['passkeys.authenticator_selection_criteria.authentication.user_verification' =>
            AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
        ]);

        $action = new AuthenticateOptionsAction();
        $result = $action->execute(modelClass: MoonshineUser::class);

        expect($result->isOk())
            ->toBeTrue()
            ->and($result->val)
            ->toBeInstanceOf(PublicKeyCredentialRequestOptions::class)
            ->and($result->val->userVerification)
            ->toBe(AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED)
            ->and($result->val->timeout)
            ->toBe(config('passkeys.ttl.auth.preferred'));
    });

    test('user verification and timeout are set correctly', function (?string $userVerification, ?int $expected): void {
        config([
            'passkeys.authenticator_selection_criteria.authentication.user_verification' => $userVerification,
            'passkeys.ttl.auth.preferred' => 600000,
            'passkeys.ttl.auth.discourage' => 180000,
        ]);

        $action = new AuthenticateOptionsAction();
        $result = $action->execute();

        expect($result->isOk())
            ->toBeTrue()
            ->and($result->val->userVerification)
            ->toBe($userVerification)
            ->and($result->val->timeout)
            ->toBe($expected);
    })->with([
        [
            AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            600000,
        ],
        [
            AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            600000,
        ],
        [
            AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_DISCOURAGED,
            180000,
        ],
    ]);
});
