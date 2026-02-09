<?php

declare(strict_types=1);

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Jampire\MoonshinePasskeys\Actions\Passkeys\RegisterPasskeyAction;
use Jampire\MoonshinePasskeys\Data\CreatePasskeyData;
use Jampire\MoonshinePasskeys\Http\Requests\CreatePasskeyRequest;
use Jampire\MoonshinePasskeys\Services\Result;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;
use MoonShine\Laravel\Models\MoonshineUser;

uses()->group('actions');

describe('RegisterPasskeyAction', function (): void {
    function getRegResult(Authenticatable $user, ?string $name = null): Result
    {
        storeFakeRegOptions();

        $request = new CreatePasskeyRequest();
        $request->setMethod('POST');
        $request->merge([
            'passkey' => passkeyJson(),
            'name' => $name ?? Str::random(10),
        ]);
        $request->setContainer(app());
        $request->validateResolved();

        $data = CreatePasskeyData::fromRequest($request);

        expect($data)
            ->not->toBeNull();

        $action = new RegisterPasskeyAction();

        return $action->execute($user, $data);
    }

    beforeEach(function (): void {
        config([
            'passkeys.enabled' => true,
            'passkeys.rp_id' => 'localhost',
            'passkeys.debug' => false,
        ]);

        Session::flush();
    });

    it('returns error when passkeys are disabled', function (): void {
        config(['passkeys.enabled' => false]);

        $user = Admin::factory()->create();
        $action = new RegisterPasskeyAction();
        $result = $action->execute($user);

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('not-created');
    });

    it('returns error when data is null', function (): void {
        $user = Admin::factory()->create();
        $action = new RegisterPasskeyAction();
        $result = $action->execute($user);

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('not-created');
    });

    it('returns error when person does not implement PasskeyContract', function (): void {
        $result = getRegResult(MoonshineUser::factory()->create());

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('not-created');
    });

    it('creates passkey with correct data', function (): void {
        $name = 'Test Passkey';
        $result = getRegResult(user: Admin::factory()->create(), name: $name);

        expect($result->isOk())
            ->toBeTrue()
            ->and($result->val)
            ->toBe('passkey-created');
    })->skip('Cannot be tested yet.');
});
