<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */

if (!function_exists('captureException')) {
    /**
     * @param array<string, mixed> $context
     */
    function captureException(\Throwable $exception, string $class, array $context = []): void
    {
        $context['exception'] = $exception::class;
        $context['file'] = $exception->getFile();
        $context['line'] = $exception->getLine();
        $context['class'] = $class;

        Log::channel(config('passkeys.log_channel'))->error(
            trans('passkeys::errors.exceptions.prefix', ['msg' => $exception->getMessage()]),
            $context,
        );
    }
}
