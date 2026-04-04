<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Models\Collections\PasskeyCollection;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;
use Webauthn\PublicKeyCredentialDescriptor;

describe('PasskeyCollection', function (): void {
    it('returns false for isUserPasskeyActive on empty collection', function (): void {
        $collection = new PasskeyCollection([]);

        expect($collection->isUserPasskeyActive())->toBeFalse();
    });

    it('returns true for isUserPasskeyActive when user has active passkey', function (): void {
        $user = Admin::factory()->create();
        $user->passkeyMeta()->update(['is_active' => true]);

        Passkey::factory()->for($user, 'personable')->create();

        $passkeys = $user->passkeys;

        expect($passkeys->isUserPasskeyActive())->toBeTrue();
    });

    it('returns false for isUserPasskeyActive when user has inactive passkey', function (): void {
        $user = Admin::factory()->create();
        // is_active defaults to false

        Passkey::factory()->for($user, 'personable')->create();

        $passkeys = $user->passkeys;

        expect($passkeys->isUserPasskeyActive())->toBeFalse();
    });

    it('returns credential descriptors for passkeys', function (): void {
        $user = Admin::factory()->create();
        Passkey::factory()->for($user, 'personable')->create();

        $passkeys = $user->passkeys;
        $descriptors = $passkeys->credentialDescriptors();

        expect($descriptors)->not->toBeEmpty()
            ->and($descriptors->first())->toBeInstanceOf(PublicKeyCredentialDescriptor::class);
    });

    it('returns empty credential descriptors for empty collection', function (): void {
        $collection = new PasskeyCollection([]);

        $descriptors = $collection->credentialDescriptors();

        expect($descriptors)->toBeEmpty();
    });
});
