<?php

declare(strict_types=1);

use Illuminate\Auth\Access\Response;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Policies\PasskeyPolicy;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;

describe('PasskeyPolicy', function (): void {
    describe('create', function (): void {
        it('allows creation when passkeys are enabled', function (): void {
            config(['passkeys.enabled' => true]);

            $policy = new PasskeyPolicy();
            $user = Admin::factory()->make();

            $response = $policy->create($user);

            expect($response)->toBeInstanceOf(Response::class)
                ->and($response->allowed())->toBeTrue();
        });

        it('denies creation when passkeys are disabled', function (): void {
            config(['passkeys.enabled' => false]);

            $policy = new PasskeyPolicy();
            $user = Admin::factory()->make();

            $response = $policy->create($user);

            expect($response)->toBeInstanceOf(Response::class)
                ->and($response->allowed())->toBeFalse();
        });
    });

    describe('update', function (): void {
        it('allows update when enabled and user owns the passkey', function (): void {
            config(['passkeys.enabled' => true]);

            $user = Admin::factory()->create();
            $passkey = Passkey::factory()->for($user, 'personable')->create();

            $policy = new PasskeyPolicy();
            $response = $policy->update($user, $passkey);

            expect($response->allowed())->toBeTrue();
        });

        it('denies update when passkeys are disabled', function (): void {
            config(['passkeys.enabled' => false]);

            $user = Admin::factory()->create();
            $passkey = Passkey::factory()->for($user, 'personable')->create();

            $policy = new PasskeyPolicy();
            $response = $policy->update($user, $passkey);

            expect($response->allowed())->toBeFalse();
        });

        it('denies update when user does not own the passkey', function (): void {
            config(['passkeys.enabled' => true]);

            $user1 = Admin::factory()->create();
            $user2 = Admin::factory()->create();
            $passkey = Passkey::factory()->for($user1, 'personable')->create();

            $policy = new PasskeyPolicy();
            $response = $policy->update($user2, $passkey);

            expect($response->allowed())->toBeFalse();
        });
    });

    describe('delete', function (): void {
        it('allows delete when enabled and user owns the passkey', function (): void {
            config(['passkeys.enabled' => true]);

            $user = Admin::factory()->create();
            $passkey = Passkey::factory()->for($user, 'personable')->create();

            $policy = new PasskeyPolicy();
            $response = $policy->delete($user, $passkey);

            expect($response->allowed())->toBeTrue();
        });

        it('denies delete when passkeys are disabled', function (): void {
            config(['passkeys.enabled' => false]);

            $user = Admin::factory()->create();
            $passkey = Passkey::factory()->for($user, 'personable')->create();

            $policy = new PasskeyPolicy();
            $response = $policy->delete($user, $passkey);

            expect($response->allowed())->toBeFalse();
        });

        it('denies delete when user does not own the passkey', function (): void {
            config(['passkeys.enabled' => true]);

            $user1 = Admin::factory()->create();
            $user2 = Admin::factory()->create();
            $passkey = Passkey::factory()->for($user1, 'personable')->create();

            $policy = new PasskeyPolicy();
            $response = $policy->delete($user2, $passkey);

            expect($response->allowed())->toBeFalse();
        });
    });
});
