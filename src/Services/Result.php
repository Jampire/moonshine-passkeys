<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Services;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class Result
{
    /**
     * @param array<string, string|int> $params
     */
    private function __construct(
        private bool $ok,
        public mixed $val = null,
        public mixed $err = null,
        public array $params = [],
        public ?string $debug = null,
    ) {
        //
    }

    /**
     * @param array<string, string|int> $params
     */
    public static function ok(mixed $value = null, array $params = []): self
    {
        return new self(ok: true, val: $value, params: $params);
    }

    /**
     * @param array<string, string|int> $params
     */
    public static function err(mixed $error, array $params = [], ?string $debug = null): self
    {
        return new self(ok: false, err: $error, params: $params, debug: $debug);
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function isErr(): bool
    {
        return !$this->ok;
    }

    public function errorCode(): string
    {
        return config('passkeys.debug')
            ? ($this->debug ?? $this->err)
            : $this->err;
    }
}
