<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Services\Serializers\Contracts;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
interface SerializerContract
{
    public function serialize(mixed $data): string;

    /**
     * @template T
     * @param class-string<T> $type
     * @return T
     */
    public function deserialize(string $data, string $type): mixed;
}
