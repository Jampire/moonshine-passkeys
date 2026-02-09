<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Jampire\MoonshinePasskeys\Database\Factories\PasskeyFactory;
use Jampire\MoonshinePasskeys\Models\Collections\PasskeyCollection;
use Jampire\MoonshinePasskeys\Services\Serializers\Contracts\SerializerContract;
use Webauthn\PublicKeyCredentialSource;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class Passkey extends Model
{
    use HasFactory;

    public const NAME_MAX_LENGTH = 50;

    protected $fillable = [
        'name',
        'credential_id',
        'data',
        'counter',
        'transports',
    ];

    #[\Override]
    public function getTable(): string
    {
        return (string)config('passkeys.table_names.passkeys');
    }

    #[\Override]
    public function newCollection(array $models = []): PasskeyCollection
    {
        return new PasskeyCollection($models);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function personable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'transports' => 'array',
        ];
    }

    protected function data(): Attribute
    {
        $serializer = app(SerializerContract::class);

        return Attribute::make(
            get: fn (string $value): PublicKeyCredentialSource => $serializer->deserialize(
                data: $value,
                type: PublicKeyCredentialSource::class,
            ),
            set: fn (PublicKeyCredentialSource $source): array => [
                'credential_id' => $source->publicKeyCredentialId,
                'transports' => json_encode($source->transports),
                'counter' => $source->counter,
                'data' => $serializer->serialize($source),
            ],
        );
    }

    protected static function newFactory(): PasskeyFactory
    {
        return PasskeyFactory::new();
    }
}
