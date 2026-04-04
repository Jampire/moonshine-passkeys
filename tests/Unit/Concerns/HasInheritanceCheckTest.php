<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Actions\Options\AuthenticateOptionsAction;
use Jampire\MoonshinePasskeys\Actions\PasskeySwitchAction;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;
use MoonShine\Laravel\Models\MoonshineUser;

uses()->group('core');

describe('HasInheritanceCheck trait', function (): void {
    describe('instance path ($model instanceof PasskeyContract)', function (): void {
        it('passes for instance implementing PasskeyContract', function (): void {
            $user = Admin::factory()->create();

            $action = new PasskeySwitchAction();
            $result = $action->execute($user, true);

            expect($result->isOk())
                ->toBeTrue();
        });

        it('returns error for instance not implementing PasskeyContract', function (): void {
            $user = MoonshineUser::factory()->create();

            $action = new PasskeySwitchAction();
            $result = $action->execute($user, true);

            expect($result->isErr())->toBeTrue();
        });
    });

    describe('string path (is_subclass_of)', function (): void {
        it('passes for class string implementing PasskeyContract', function (): void {
            $action = new AuthenticateOptionsAction();
            $result = $action->execute(modelClass: Admin::class);

            expect($result->isOk())
                ->toBeTrue()
                ->and($result->val)
                ->not->toBeNull();
        });
    });
});
