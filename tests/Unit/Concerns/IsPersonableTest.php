<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Policies\PasskeyPolicy;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;

uses()->group('core');

describe('IsPersonable trait', function (): void {
    it('returns true when user owns the passkey', function (): void {
        $user = adminWithPasskey();
        $passkey = $user->passkeys()->first();

        $policy = new PasskeyPolicy();

        expect($policy->isPersonable($user, $passkey))
            ->toBeTrue();
    });

    it('returns false when user does not own the passkey', function (): void {
        $passkey = adminWithPasskey()->passkeys()->first();

        $policy = new PasskeyPolicy();

        expect($policy->isPersonable(Admin::factory()->create(), $passkey))
            ->toBeFalse();
    });
});
