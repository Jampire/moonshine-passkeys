<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Services\Result;

describe('Result', function (): void {
    it('creates an ok result with value', function (): void {
        $result = Result::ok('test-value');

        expect($result->isOk())->toBeTrue()
            ->and($result->isErr())->toBeFalse()
            ->and($result->val)->toBe('test-value')
            ->and($result->err)->toBeNull();
    });

    it('creates an ok result with params', function (): void {
        $result = Result::ok('code', ['key' => 'value']);

        expect($result->isOk())->toBeTrue()
            ->and($result->params)->toBe(['key' => 'value']);
    });

    it('creates an err result', function (): void {
        $result = Result::err('error-code');

        expect($result->isErr())->toBeTrue()
            ->and($result->isOk())->toBeFalse()
            ->and($result->err)->toBe('error-code')
            ->and($result->val)->toBeNull();
    });

    it('creates an err result with params and debug', function (): void {
        $result = Result::err('error-code', ['k' => 'v'], 'debug message');

        expect($result->err)->toBe('error-code')
            ->and($result->params)->toBe(['k' => 'v'])
            ->and($result->debug)->toBe('debug message');
    });

    it('errorCode returns err when debug is disabled', function (): void {
        config(['passkeys.debug' => false]);

        $result = Result::err('error-code', debug: 'debug info');

        expect($result->errorCode())->toBe('error-code');
    });

    it('errorCode returns debug string when debug is enabled and debug is set', function (): void {
        config(['passkeys.debug' => true]);

        $result = Result::err('error-code', debug: 'debug info');

        expect($result->errorCode())->toBe('debug info');
    });

    it('errorCode falls back to err when debug is enabled but debug is null', function (): void {
        config(['passkeys.debug' => true]);

        $result = Result::err('error-code');

        expect($result->errorCode())->toBe('error-code');
    });

    it('creates an ok result with null value', function (): void {
        $result = Result::ok();

        expect($result->val)->toBeNull()
            ->and($result->isOk())->toBeTrue();
    });
});
