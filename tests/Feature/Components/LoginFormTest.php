<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Components\LoginForm;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Core\Core;

uses()->group('components');

describe('LoginForm', function (): void {
    beforeEach(function (): void {
        config([
            'moonshine.forms.login' => LoginForm::class,
        ]);

        $this->loginForm = new LoginForm('action', Core::getInstance());
    });

    it('returns FormBuilderContract', function (bool $passkeyEnabled): void {
        config(['passkeys.enabled' => $passkeyEnabled]);

        $component = $this->loginForm;

        expect($component())
            ->toBeInstanceOf(FormBuilderContract::class);
    })->with([
        'disabled' => [false],
        'enabled' => [true],
    ]);
});
