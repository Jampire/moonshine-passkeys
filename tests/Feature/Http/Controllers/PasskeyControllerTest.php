<?php

declare(strict_types=1);

// TODO: test Requests params on all controllers

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Testing\TestResponse;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Http\Controllers\PasskeyController;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Services\Result;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses()->group('http');

describe('PasskeyController', function (): void {
    beforeEach(function (): void {
        config([
            'passkeys.enabled' => true,
            'passkeys.debug' => false,
        ]);
    });

    describe('store flow', function (): void {
        function storePost(): TestResponse
        {
            return post(route('moonshine.passkeys.store'), [
                'passkey' => json_encode(['test']),
                'name' => 'test',
            ]);
        }

        it('returns error if user is not authenticated', function (): void {
            $response = storePost();
            $response->assertBadRequest();

            expect($response->json('code'))
                ->toBe('not-created')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::errors.not-created'));
        });

        it('returns error on registration flow', function (): void {
            $user = Admin::factory()->create();

            $action = Mockery::mock(Actionable::class);
            $action->shouldReceive('execute')
                ->once()
                ->with(Mockery::any(), Mockery::any())
                ->andReturn(Result::err('not-created'));

            controllerBind(
                app: $this->app,
                controllerClass: PasskeyController::class,
                action: $action,
                need: '$registerPasskeyAction',
            );

            actingAs($user, 'moonshine');
            $response = storePost();
            $response->assertBadRequest();

            expect($response->json('code'))
                ->toBe('not-created')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::errors.not-created'));
        });

        it('returns passkey-created on success flow', function (): void {
            $user = Admin::factory()->create();

            $action = Mockery::mock(Actionable::class);
            $action->shouldReceive('execute')
                ->once()
                ->with(Mockery::any(), Mockery::any())
                ->andReturn(Result::ok('passkey-created'));

            controllerBind(
                app: $this->app,
                controllerClass: PasskeyController::class,
                action: $action,
                need: '$registerPasskeyAction',
            );

            actingAs($user, 'moonshine');
            $response = storePost();
            $response->assertCreated();

            expect($response->json('code'))
                ->toBe('passkey-created')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::success.passkey-created'));
        });
    });

    describe('update flow', function (): void {
        function updatePatch(Passkey $passkey, ?string $name = null): TestResponse
        {
            return patch(route('moonshine.passkeys.update', ['passkey' => $passkey]), [
                'name' => $name ?? Str::random(10),
            ]);
        }

        it('throws error if user is not authenticated', function (): void {
            $passkey = Passkey::factory()->create();

            $response = updatePatch($passkey);
            $response->json();
        })->throws(AuthorizationException::class);

        it('returns success when update to the same name', function (): void {
            $user = adminWithPasskey();
            $passkey = $user->passkeys()->first();

            actingAs($user, 'moonshine');
            $response = updatePatch($passkey, $passkey->name);
            $response->assertOk();

            expect($response->json('code'))
                ->toBe('passkey-name-updated')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::success.passkey-name-updated'));
        });

        it('updates to the new name', function (): void {
            $user = adminWithPasskey();
            $passkey = $user->passkeys()->first();
            $name = Str::random(10);
            $createdAt = $passkey->created_at->todatetimestring();
            $updatedAt = $passkey->updated_at->todatetimestring();

            actingAs($user, 'moonshine');
            $response = updatePatch($passkey, $name);
            $response->assertOk();

            $passkey->refresh();

            expect($response->json('code'))
                ->toBe('passkey-name-updated')
                ->and($response->json('message'))
                ->toBe(trans('passkeys::success.passkey-name-updated'))
                ->and($passkey->name)
                ->toBe($name)
                ->and($passkey->created_at->todatetimestring())
                ->toBe($createdAt)
                ->and($passkey->updated_at->todatetimestring())
                ->toBe($updatedAt);
        });
    });

    describe('delete flow', function (): void {
        function destroyDelete(Passkey $passkey): TestResponse
        {
            return delete(route('moonshine.passkeys.destroy', ['passkey' => $passkey]));
        }

        it('throws error if user is not authenticated', function (): void {
            $passkey = Passkey::factory()->create();

            $response = destroyDelete($passkey);
            $response->json();
        })->throws(AuthorizationException::class);

        it('returns error if passkey does not exist', function (): void {
            $user = adminWithPasskey();

            actingAs($user, 'moonshine');
            delete(route('moonshine.passkeys.destroy', ['passkey' => 999]))
                ->assertNotFound();
        });

        it('deletes passkey', function (): void {
            $user = adminWithPasskey();
            Passkey::factory()->for($user, 'personable')->create();

            expect($user->passkeys()->count())
                ->toBe(2);

            $passkey = $user->passkeys()->first();
            $id = $passkey->id;

            actingAs($user, 'moonshine');
            $response = destroyDelete($passkey);
            $response->assertOk();

            expect($user->passkeys()->count())
                ->toBe(1)
                ->and(Passkey::find($id))
                ->toBeNull();
        });
    });
});
