<?php

declare(strict_types=1);

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Jampire\MoonshinePasskeys\Actions\Passkeys\AuthenticatePasskeyAction;
use Jampire\MoonshinePasskeys\Data\UpdatePasskeyData;
use Jampire\MoonshinePasskeys\Http\Requests\AuthenticatePasskeyRequest;
use Jampire\MoonshinePasskeys\Services\Result;

uses()->group('actions');

describe('AuthenticatePasskeyAction', function (): void {
    function getAuthResult(bool $isPasskeyActive = true, bool $createUser = true): Result
    {
        if ($createUser) {
            adminWithPasskey(isActive: $isPasskeyActive);
        }

        storeFakeAuthOptions();

        $request = new AuthenticatePasskeyRequest();
        $request->setMethod('POST');
        $request->merge([
            'answer' => fakeAssertionJson(),
            'remember' => false,
        ]);
        $request->setContainer(app());
        $request->validateResolved();

        $data = UpdatePasskeyData::fromRequest($request);

        expect($data)
            ->not->toBeNull();

        $action = new AuthenticatePasskeyAction();

        return $action->execute('throttle-key', $data);
    }

    beforeEach(function (): void {
        $this->throttleKey = 'throttle-key';

        config([
            'passkeys.enabled' => true,
            'passkeys.rp_id' => 'localhost',
            'passkeys.counter_checker' => false,
            'passkeys.debug' => false,
        ]);

        Session::flush();
    });

    it('returns error when passkeys are disabled', function (): void {
        config(['passkeys.enabled' => false]);

        $action = new AuthenticatePasskeyAction();
        $result = $action->execute($this->throttleKey);

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('authentication-failed');
    });

    it('returns error when data is null', function (): void {
        $action = new AuthenticatePasskeyAction();
        $result = $action->execute($this->throttleKey);

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('authentication-failed');
    });

    it('returns rate limit error when production and rate limited', function (): void {
        $this->app['env'] = 'production';
        $maxAttempts = config('passkeys.rate_limits.attempts');

        // Exceed the rate limit
        for ($i = 0; $i <= $maxAttempts; $i++) {
            RateLimiter::hit($this->throttleKey);
        }

        $action = new AuthenticatePasskeyAction();
        $result = $action->execute($this->throttleKey);
        $this->app['env'] = 'testing';

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('exceptions.rate-limit-exceeded')
            ->and($result->params['seconds'])
            ->toBeGreaterThanOrEqual(0);
    });

    it('returns error when passkey not found in database', function (): void {
        $result = getAuthResult(createUser: false);

        // No passkey in DB with that credential_id -> authentication-failed
        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('authentication-failed');
    });

    it('returns errors on validations', function (bool $isPasskeyActive): void {
        $result = getAuthResult(isPasskeyActive: $isPasskeyActive);

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->err)
            ->toBe('authentication-failed');
    })->with([
        'passkey-active' => [true],
        'passkey-inactive' => [false],
    ]);

    it('covers counter_checker branch when active passkey with fake assertion', function (): void {
        config(['passkeys.counter_checker' => true]);

        $result = getAuthResult();

        expect($result->isErr())
            ->toBeTrue();
    });

    it('covers debug=true branch', function (): void {
        config(['passkeys.debug' => true]);

        $result = getAuthResult();

        expect($result->isErr())
            ->toBeTrue()
            ->and($result->debug)
            ->not->toBeNull();
    });
});
