<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Lockout;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Http\Controllers\PasskeyAuthController;
use Jampire\MoonshinePasskeys\Services\Result;
use MoonShine\Laravel\MoonShineAuth;

use function Pest\Laravel\post;

uses()->group('http');

describe('PasskeyAuthController', function (): void {
    beforeEach(function (): void {
        config([
            'passkeys.enabled' => true,
            'passkeys.debug' => false,
        ]);

        Event::fake();

        expect(MoonShineAuth::getGuard()->user())
            ->toBeNull();
    });

    it('returns error on rate limit exception', function (): void {
        $action = Mockery::mock(Actionable::class);
        $action->shouldReceive('execute')
            ->once()
            ->with(Mockery::any(), Mockery::any())
            ->andReturn(Result::err('exceptions.rate-limit-exceeded'));

        controllerBind(
            app: $this->app,
            controllerClass: PasskeyAuthController::class,
            action: $action,
        );

        post(route('moonshine.passkeys.authenticate'), ['answer' => json_encode(['test' => 'test'])])
           ->assertRedirect();

        expect(MoonShineAuth::getGuard()->user())
            ->toBeNull();

        expect(session('error'));

        Event::assertDispatched(Lockout::class);
    });

    it('returns error on wrong flow', function (): void {
        $action = Mockery::mock(Actionable::class);
        $action->shouldReceive('execute')
            ->once()
            ->with(Mockery::any(), Mockery::any())
            ->andReturn(Result::err('authentication-failed'));

        controllerBind(
            app: $this->app,
            controllerClass: PasskeyAuthController::class,
            action: $action,
        );

        post(route('moonshine.passkeys.authenticate'), ['answer' => json_encode(['test' => 'test'])])
            ->assertRedirect();

        expect(MoonShineAuth::getGuard()->user())
            ->toBeNull();

        expect(session('error'));

        Event::assertNotDispatched(Lockout::class);
    });

    it('logins user on success flow', function (): void {
        $user = adminWithPasskey();

        $action = Mockery::mock(Actionable::class);
        $action->shouldReceive('execute')
            ->once()
            ->with(Mockery::any(), Mockery::any())
            ->andReturn(Result::ok($user->passkeys()->first()));

        controllerBind(
            app: $this->app,
            controllerClass: PasskeyAuthController::class,
            action: $action,
        );

        post(route('moonshine.passkeys.authenticate'), ['answer' => json_encode(['test' => 'test'])])
            ->assertRedirect();

        expect(MoonShineAuth::getGuard()->user()->email)
            ->toBe($user->email);
    });
});
