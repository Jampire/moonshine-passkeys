<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class PasskeyMeta extends Model
{
    protected $fillable = [
        'is_active',
        'personable_type',
        'personable_id',
    ];

    #[\Override]
    public function getTable(): string
    {
        return (string)config('passkeys.table_names.metas');
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
            'is_active' => 'boolean',
        ];
    }

    #[\Override]
    protected static function booted(): void
    {
        self::creating(function (self $passkeyMeta): void {
            $passkeyMeta->public_id = (string)Str::uuid();
        });
    }
}
