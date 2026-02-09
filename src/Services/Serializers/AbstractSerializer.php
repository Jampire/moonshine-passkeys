<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Services\Serializers;

use Jampire\MoonshinePasskeys\Services\Serializers\Contracts\SerializerContract;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
abstract class AbstractSerializer implements SerializerContract
{
    protected SerializerInterface $serializer;

    /**
     * @throws ExceptionInterface
     */
    public function serialize(mixed $data): string
    {
        return $this->serializer->serialize(
            data: $data,
            format: 'json',
            context: [
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                JsonEncode::OPTIONS => JSON_THROW_ON_ERROR,
            ],
        );
    }

    /**
     * @inheritDoc
     * @throws ExceptionInterface
     */
    public function deserialize(string $data, string $type): mixed
    {
        return $this->serializer->deserialize(
            data: $data,
            type: $type,
            format: 'json',
            context: [
                JsonEncode::OPTIONS => JSON_THROW_ON_ERROR,
            ],
        );
    }
}
