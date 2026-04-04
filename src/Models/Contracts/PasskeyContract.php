<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Models\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Models\PasskeyMeta;

/**
 * @property-read Collection<int, Passkey> $passkeys
 * @property-read PasskeyMeta|null $passkeyMeta
 *
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
interface PasskeyContract
{
    /**
     * @return MorphMany<Passkey, $this>
     */
    public function passkeys(): MorphMany;

    /**
     * @return MorphOne<PasskeyMeta, $this>
     */
    public function passkeyMeta(): MorphOne;
}
