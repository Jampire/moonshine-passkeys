<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Components\PasskeyComponent;
use MoonShine\UI\Components\MoonShineComponent;

uses()->group('components');

describe('PasskeyComponent', function (): void {
    it('returns FormBuilderContract', function (bool $passkeyEnabled): void {
        config(['passkeys.enabled' => $passkeyEnabled]);

        $component = PasskeyComponent::make();

        expect($component)
            ->toBeInstanceOf(MoonShineComponent::class);
    })->with([
        'disabled' => [false],
        'enabled' => [true],
    ]);
});
