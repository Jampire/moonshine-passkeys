<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Http\Controllers\OptionsController;
use Jampire\MoonshinePasskeys\Services\Options\Contracts\OptionsStoreContract;
use Jampire\MoonshinePasskeys\Services\Result;
use Jampire\MoonshinePasskeys\Services\Serializers\Contracts\SerializerContract;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses()->group('http');

describe('OptionsController', function (): void {
    beforeEach(function (): void {
        config([
            'passkeys.enabled' => true,
            'passkeys.debug' => false,
        ]);

        $this->testOptions = ['credential_id' => 'credential_id'];

        $serializerService = Mockery::mock(SerializerContract::class);
        $serializerService->shouldReceive('serialize')
            ->with(Mockery::any())
            ->andReturn(json_encode($this->testOptions));
        $this->app->instance(SerializerContract::class, $serializerService);

        $optionsStore = Mockery::mock(OptionsStoreContract::class);
        $optionsStore->shouldReceive('saveRegistrationOptions')
            ->with(json_encode($this->testOptions));
        $optionsStore->shouldReceive('saveAuthenticationOptions')
            ->with(json_encode($this->testOptions));
        $this->app->instance(OptionsStoreContract::class, $optionsStore);
    });

    describe('registerUserOptions flow', function (): void {
        it("register user's options on success flow", function (): void {
            $user = adminWithPasskey();

            $action = Mockery::mock(Actionable::class);
            $action->shouldReceive('execute')
                ->once()
                ->with($user)
                ->andReturn(Result::ok('success'));

            controllerBind(
                app: $this->app,
                controllerClass: OptionsController::class,
                action: $action,
                need: '$registerUserOptionsAction',
            );

            actingAs($user, 'moonshine');
            $response = post(route('moonshine.passkeys.register-user-options'), ['name' => Str::random(10)])
                ->assertOk();

            expect($response->json())
                ->toBe($this->testOptions);
        });

        it('returns error for wrong options register flow', function (): void {
            $user = adminWithPasskey();

            $action = Mockery::mock(Actionable::class);
            $action->shouldReceive('execute')
                ->once()
                ->with($user)
                ->andReturn(Result::err('options.register'));

            controllerBind(
                app: $this->app,
                controllerClass: OptionsController::class,
                action: $action,
                need: '$registerUserOptionsAction',
            );

            actingAs($user, 'moonshine');
            $response = post(route('moonshine.passkeys.register-user-options'), ['name' => Str::random(10)])
                ->assertBadRequest();

            expect($response->json('code'))
                ->toBe('options.register')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::errors.options.register'));
        });
    });

    describe('authenticateOptions flow', function (): void {
        it('authenticates options on success flow', function (): void {
            $action = Mockery::mock(Actionable::class);
            $action->shouldReceive('execute')
                ->once()
                ->with(Mockery::any())
                ->andReturn(Result::ok('success'));

            controllerBind(
                app: $this->app,
                controllerClass: OptionsController::class,
                action: $action,
                need: '$authenticateOptionsAction',
            );

            $response = post(route('moonshine.passkeys.authenticate-options'))
                ->assertOk();

            expect($response->json())
                ->toBe($this->testOptions);
        });

        it('returns error for wrong options authenticate flow', function (): void {
            $action = Mockery::mock(Actionable::class);
            $action->shouldReceive('execute')
                ->once()
                ->with(Mockery::any())
                ->andReturn(Result::err('authentication-failed'));

            controllerBind(
                app: $this->app,
                controllerClass: OptionsController::class,
                action: $action,
                need: '$authenticateOptionsAction',
            );

            $response = post(route('moonshine.passkeys.authenticate-options'))
                ->assertNotFound();

            expect($response->json('code'))
                ->toBe('authentication-failed')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::errors.authentication-failed'));
        });
    });
});
