<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Jampire\MoonshinePasskeys\Models\PasskeyMeta;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;

describe('PasskeyMeta model', function (): void {
    it('uses the configured table name', function (): void {
        $meta = new PasskeyMeta();

        expect($meta->getTable())->toBe(config('passkeys.table_names.metas'));
    });

    it('has is_active cast to boolean', function (): void {
        $meta = new PasskeyMeta();
        $casts = $meta->getCasts();

        expect($casts)->toHaveKey('is_active')
            ->and($casts['is_active'])->toBe('boolean');
    });

    it('auto-generates public_id UUID on creating', function (): void {
        $user = Admin::factory()->create();
        $meta = $user->passkeyMeta;

        expect($meta)->toBeInstanceOf(PasskeyMeta::class)
            ->and($meta->public_id)->toBeString()
            ->and(strlen((string) $meta->public_id))->toBeGreaterThan(0);
    });

    it('has personable morphTo relation', function (): void {
        $meta = new PasskeyMeta();
        $relation = $meta->personable();

        expect($relation)->toBeInstanceOf(MorphTo::class);
    });

    it('creates with is_active false by default', function (): void {
        $user = Admin::factory()->create();
        $meta = $user->passkeyMeta;

        expect($meta->is_active)->toBeFalse();
    });
});
