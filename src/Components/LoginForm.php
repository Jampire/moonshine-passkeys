<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Components;

use Jampire\MoonshinePasskeys\Components\Concerns\HasAssets;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Contracts\UI\FormContract;
use MoonShine\Support\Traits\Makeable;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

final readonly class LoginForm implements FormContract
{
    use HasAssets;
    use Makeable;

    public function __construct(
        private string $action,
        private CoreContract $core,
    ) {
        //
    }

    public function __invoke(): FormBuilderContract
    {
        if (!config('passkeys.enabled')) {
            $class = config('passkeys.backup_login_form');

            return \call_user_func(
                new $class($this->action, $this->core)
            );
        }

        $trans = $this->core->getTranslator();

        return FormBuilder::make()
            ->class('authentication-form')
            ->action($this->action)
            ->errorsAbove(false)
            ->customAttributes(['novalidate' => true])
            ->addAssets($this->assertionAssets())
            ->xData($this->assertionXData(
                authenticateUrl: route('moonshine.passkeys.authenticate'),
                failedMessage: trans('passkeys::errors.authentication-failed'),
            ))
            ->fields([
                Text::make($trans->get('moonshine::ui.login.username'), 'username')
                    ->required()
                    ->setAttribute('x-model', 'username')
                    ->customAttributes([
                        'autofocus' => false,
                        'autocomplete' => 'username webauthn',
                    ]),

                Div::make([
                    Password::make($trans->get('moonshine::ui.login.password'), 'password')
                        ->required(),
                ])
                    ->setAttribute('x-show', 'showPasswordField'),

                Switcher::make($trans->get('moonshine::ui.login.remember_me'), 'remember'),

                LineBreak::make(),

                Div::make()
                    ->setAttribute('x-show', 'error')
                    ->setAttribute('x-text', 'error')
                    ->class(['alert alert-error']),

                LineBreak::make(),
            ])
            ->submit($trans->get('moonshine::ui.login.login'), [
                'class' => 'btn-primary btn-lg w-full',
            ]);
    }
}
