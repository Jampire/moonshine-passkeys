<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Models\PasskeyMeta;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
trait HasPasskeys
{
    /**
     * @return MorphMany<Passkey, $this>
     */
    public function passkeys(): MorphMany
    {
        return $this->morphMany(Passkey::class, 'personable');
    }

    /**
     * @return MorphOne<PasskeyMeta, $this>
     */
    public function passkeyMeta(): MorphOne
    {
        return $this->morphOne(PasskeyMeta::class, 'personable');
    }

    protected static function bootHasPasskeys(): void
    {
        static::created(function (self $model): void {
            $model->passkeyMeta()->create();
        });

        static::deleting(function (self $model): void {
            $model->passkeyMeta()->delete();
            $model->passkeys()->delete();
        });
    }
}
