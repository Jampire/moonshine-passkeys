<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Jampire\MoonshinePasskeys\Actions\Passkeys\UpdatePasskeyAction;
use Jampire\MoonshinePasskeys\Models\Passkey;

uses()->group('actions');

describe('UpdatePasskeyAction', function (): void {
    function getPasskey(): Passkey
    {
        return adminWithPasskey()->passkeys()->first();
    }

    beforeEach(function (): void {
        $this->action = new UpdatePasskeyAction();
    });

    it('returns ok immediately when name is unchanged', function (): void {
        $passkey = getPasskey();

        $result = $this->action->execute($passkey, $passkey->name);

        expect($result->isOk())
            ->toBeTrue()
            ->and($result->val)
            ->toBe('passkey-name-updated');
    });

    it('updates passkey name with new name', function (): void {
        $passkey = getPasskey();
        $name = Str::random(10);

        $result = $this->action->execute($passkey, $name);

        expect($result->isOk())
            ->toBeTrue()
            ->and($result->val)
            ->toBe('passkey-name-updated')
            ->and($passkey->refresh()->name)
            ->toBe($name);
    });

    it('does not update timestamps when name changes', function (): void {
        $passkey = getPasskey();
        $originalUpdatedAt = $passkey->updated_at;

        $this->travel(5)->seconds();

        $this->action->execute($passkey, Str::random(10));

        $passkey->refresh();
        expect($passkey->refresh()->updated_at->toDateTimeString())
            ->toBe($originalUpdatedAt->toDateTimeString());
    });
});
