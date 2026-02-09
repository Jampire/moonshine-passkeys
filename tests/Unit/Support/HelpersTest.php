<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;

describe('Helper functions', function (): void {
    it('captureException logs the error', function (): void {
        Log::shouldReceive('channel')->once()->andReturnSelf();
        Log::shouldReceive('error')->once();

        $exception = new RuntimeException('Test error');

        captureException($exception, 'TestClass', ['extra' => 'context']);
    });

    it('captureException exists as a function', function (): void {
        expect(function_exists('captureException'))->toBeTrue();
    });
});
