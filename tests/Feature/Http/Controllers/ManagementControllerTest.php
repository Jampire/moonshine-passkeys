<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Http\Controllers\ManagementController;
use Jampire\MoonshinePasskeys\Services\Result;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses()->group('http');

describe('ManagementController', function (): void {
    beforeEach(function (): void {
        config([
            'passkeys.enabled' => true,
            'passkeys.debug' => false,
        ]);
    });

    describe('activation flow', function (): void {
        it('activates passkeys on success flow', function (): void {
            $user = adminWithPasskey(isActive: false);

            $action = Mockery::mock(Actionable::class);
            $action->shouldReceive('execute')
                ->once()
                ->with($user, true)
                ->andReturn(Result::ok('success'));

            controllerBind($this->app, ManagementController::class, $action);

            actingAs($user, 'moonshine');
            $response = post(route('moonshine.passkeys.management.activate'))
                ->assertOk();

            expect($response->json('code'))
                ->toBe('success')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::success.success'));
        });

        it('returns error for wrong activation', function (): void {
            $user = adminWithPasskey(isActive: false);

            $action = Mockery::mock(Actionable::class);
            $action->shouldReceive('execute')
                ->once()
                ->with($user, true)
                ->andReturn(Result::err(error: 'common.operation-failed'));

            controllerBind($this->app, ManagementController::class, $action);

            actingAs($user, 'moonshine');
            $response = post(route('moonshine.passkeys.management.activate'))
                ->assertNotFound();

            expect($response->json('code'))
                ->toBe('common.operation-failed')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::errors.common.operation-failed'));
        });
    });

    describe('deactivation flow', function (): void {
        it('deactivates passkeys on success flow', function (): void {
            $user = adminWithPasskey();

            $action = Mockery::mock(Actionable::class);
            $action->shouldReceive('execute')
                ->once()
                ->with($user, false)
                ->andReturn(Result::ok('success'));

            controllerBind($this->app, ManagementController::class, $action);

            actingAs($user, 'moonshine');
            $response = post(route('moonshine.passkeys.management.deactivate'))
                ->assertOk();

            expect($response->json('code'))
                ->toBe('success')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::success.success'));
        });

        it('returns error for wrong deactivation', function (): void {
            $user = adminWithPasskey();

            $action = Mockery::mock(Actionable::class);
            $action->shouldReceive('execute')
                ->once()
                ->with($user, false)
                ->andReturn(Result::err(error: 'common.operation-failed'));

            controllerBind($this->app, ManagementController::class, $action);

            actingAs($user, 'moonshine');
            $response = post(route('moonshine.passkeys.management.deactivate'))
                ->assertNotFound();

            expect($response->json('code'))
                ->toBe('common.operation-failed')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::errors.common.operation-failed'));
        });
    });
});
