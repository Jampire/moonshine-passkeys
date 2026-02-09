<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Models\Collections;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Webauthn\PublicKeyCredentialDescriptor as PKCDescriptor;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class PasskeyCollection extends EloquentCollection
{
    /**
     * @return Collection<int, PKCDescriptor>
     * @author Dzianis Kotau <me@dzianiskotau.com>
     */
    public function credentialDescriptors(): Collection
    {
        return $this
            ->map(fn (Passkey $passkey): PKCDescriptor => $passkey->data?->getPublicKeyCredentialDescriptor())
            ->filter();
    }

    public function isUserPasskeyActive(): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return $this->first()?->personable?->passkeyMeta?->is_active ?? false;
    }
}
