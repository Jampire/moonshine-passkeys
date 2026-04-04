<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Actions\Options\RegisterUserOptionsAction;
use Jampire\MoonshinePasskeys\Data\AuthenticatorSelectionData;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;
use MoonShine\Laravel\Models\MoonshineUser;
use Webauthn\PublicKeyCredentialCreationOptions;

uses()->group('actions');

describe('RegisterUserOptionsAction', function (): void {
    function makeAction(): RegisterUserOptionsAction
    {
        return new RegisterUserOptionsAction(
            new AuthenticatorSelectionData(
                authenticatorAttachment: 'platform',
                userVerification: 'preferred',
                residentKey: 'preferred',
            )
        );
    }

    it('returns options for a valid user with passkey meta', function (): void {
        $user = Admin::factory()->create();

        $action = makeAction();
        $result = $action->execute($user);

        expect($result->isOk())
            ->toBeTrue()
            ->and($result->val)
            ->toBeInstanceOf(PublicKeyCredentialCreationOptions::class);
    });

    it('creates passkeyMeta if not present', function (): void {
        $user = Admin::factory()->create();
        $user->passkeyMeta()->delete(); // Remove auto-created meta

        $action = makeAction();
        $result = $action->execute($user);

        expect($result->isOk())
            ->toBeTrue()
            ->and($user->refresh()->passkeyMeta)
            ->not->toBeNull();
    });

    it('excludes existing passkeys from options', function (): void {
        $user = adminWithPasskey();

        $action = makeAction();
        $result = $action->execute($user);

        expect($result->isOk())
            ->toBeTrue()
            ->and($result->val)
            ->toBeInstanceOf(PublicKeyCredentialCreationOptions::class)
            ->and($result->val->excludeCredentials)
            ->not->toBeEmpty();
    });

    it('returns error for user not implementing PasskeyContract', function (): void {
        $user = MoonshineUser::factory()->create();

        $action = makeAction();
        $result = $action->execute($user);

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('options.register');
    });
});
