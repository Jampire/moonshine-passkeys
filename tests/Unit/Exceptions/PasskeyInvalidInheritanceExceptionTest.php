<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Exceptions\PasskeyException;
use Jampire\MoonshinePasskeys\Exceptions\PasskeyInvalidInheritanceException;
use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;

uses()->group('exceptions');

describe('PasskeyInvalidInheritanceException', function (): void {
    it('extends PasskeyException', function (): void {
        $exception = new PasskeyInvalidInheritanceException();

        expect($exception)
            ->toBeInstanceOf(PasskeyException::class);
    });

    it('shows correct message', function (): void {
        config(['moonshine.auth.model' => 'App\\Models\\User']);

        $exception = new PasskeyInvalidInheritanceException();

        expect($exception->getMessage())
            ->toBe(trans('passkeys::errors.exceptions.related-model', [
                'user' => config('moonshine.auth.model'),
                'contract' => PasskeyContract::class,
            ]));
    });
});
