<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Models\PasskeyMeta;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;

describe('HasPasskeys trait', function (): void {
    it('auto-creates passkeyMeta when user is created', function (): void {
        $user = Admin::factory()->create();

        expect($user->passkeyMeta)->toBeInstanceOf(PasskeyMeta::class)
            ->and($user->passkeyMeta->public_id)->toBeString()
            ->and($user->passkeyMeta->is_active)->toBeFalse();
    });

    it('provides passkeys() morphMany relation', function (): void {
        $user = Admin::factory()->create();
        $relation = $user->passkeys();

        expect($relation)->toBeInstanceOf(MorphMany::class);
    });

    it('provides passkeyMeta() morphOne relation', function (): void {
        $user = Admin::factory()->create();
        $relation = $user->passkeyMeta();

        expect($relation)->toBeInstanceOf(MorphOne::class);
    });

    it('deletes passkeys and meta when user is deleted', function (): void {
        $user = Admin::factory()->create();
        $passkey = Passkey::factory()->for($user, 'personable')->create();
        $metaId = $user->passkeyMeta->id;
        $passkeyId = $passkey->id;

        $user->delete();

        expect(PasskeyMeta::find($metaId))->toBeNull()
            ->and(Passkey::find($passkeyId))->toBeNull();
    });
});
