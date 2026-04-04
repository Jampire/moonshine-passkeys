<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Webauthn\PublicKeyCredentialSource;
use Jampire\MoonshinePasskeys\Models\Collections\PasskeyCollection;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;

describe('Passkey model', function (): void {
    it('uses the configured table name', function (): void {
        $passkey = new Passkey();

        expect($passkey->getTable())->toBe(config('passkeys.table_names.passkeys'));
    });

    it('has the correct NAME_MAX_LENGTH constant', function (): void {
        expect(Passkey::NAME_MAX_LENGTH)->toBe(50);
    });

    it('uses PasskeyCollection for collections', function (): void {
        $passkey = new Passkey();
        $collection = $passkey->newCollection();

        expect($collection)->toBeInstanceOf(PasskeyCollection::class);
    });

    it('has transports cast to array', function (): void {
        $passkey = new Passkey();
        $casts = $passkey->getCasts();

        expect($casts)->toHaveKey('transports')
            ->and($casts['transports'])->toBe('array');
    });

    it('has personable morphTo relation', function (): void {
        $passkey = new Passkey();
        $relation = $passkey->personable();

        expect($relation)->toBeInstanceOf(MorphTo::class);
    });

    it('creates a passkey via factory', function (): void {
        $user = Admin::factory()->create();
        $passkey = Passkey::factory()
            ->for($user, 'personable')
            ->create();

        expect($passkey)->toBeInstanceOf(Passkey::class)
            ->and($passkey->personable)->toBeInstanceOf(Admin::class);
    });

    it('data attribute deserializes to PublicKeyCredentialSource', function (): void {
        $user = Admin::factory()->create();
        $passkey = Passkey::factory()
            ->for($user, 'personable')
            ->create();

        expect($passkey->data)->toBeInstanceOf(PublicKeyCredentialSource::class);
    });
});
