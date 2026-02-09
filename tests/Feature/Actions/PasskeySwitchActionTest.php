<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Actions\PasskeySwitchAction;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;
use MoonShine\Laravel\Models\MoonshineUser;

uses()->group('actions');

describe('PasskeySwitchAction', function (): void {
    beforeEach(function (): void {
        config([
            'passkeys.enabled' => true,
            'passkeys.debug' => false,
        ]);

        $this->action = new PasskeySwitchAction();
    });

    it('activates passkeys for a valid user', function (): void {
        $user = Admin::factory()->create();
        $user->passkeyMeta()->update(['is_active' => false]);

        $result = $this->action->execute($user, true);

        expect($result->isOk())
            ->toBeTrue()
            ->and($result->val)
            ->toBe('success')
            ->and($user->refresh()->passkeyMeta->is_active)
            ->toBeTrue();
    });

    it('deactivates passkeys for a valid user', function (): void {
        $user = Admin::factory()->create();
        $user->passkeyMeta()->update(['is_active' => true]);

        $result = $this->action->execute($user, false);

        expect($result->isOk())
            ->toBeTrue()
            ->and($user->refresh()->passkeyMeta->is_active)
            ->toBeFalse();
    });

    it('returns error for user not implementing PasskeyContract', function (): void {
        $user = MoonshineUser::factory()->create();

        $result = $this->action->execute($user, true);

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('common.operation-failed');
    });

    it('creates passkeyMeta via updateOrCreate', function (): void {
        $user = Admin::factory()->create();
        // Delete the auto-created meta to test updateOrCreate
        $user->passkeyMeta()->delete();

        $result = $this->action->execute($user, true);

        expect($result->isOk())
            ->toBeTrue()
            ->and($user->refresh()->passkeyMeta?->is_active)
            ->toBeTrue();
    });
});
