<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Components;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Jampire\MoonshinePasskeys\Components\Concerns\HasAssets;
use Jampire\MoonshinePasskeys\Concerns\HasInheritanceCheck;
use Jampire\MoonshinePasskeys\Concerns\IsPersonable;
use Jampire\MoonshinePasskeys\Exceptions\{PasskeyException, PasskeyInvalidInheritanceException};
use Jampire\MoonshinePasskeys\Http\Concerns\HasUser;
use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;
use Jampire\MoonshinePasskeys\Models\Passkey;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Crud\Components\Fragment;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\{HttpMethod, JsEvent};
use MoonShine\UI\Components\{ActionButton, Alert, Components, MoonShineComponent, When};
use MoonShine\UI\Components\Layout\{Box, Div, LineBreak};
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\{Date, Text};

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 * @codeCoverageIgnore
 * @todo https://simplewebauthn.dev/docs/packages/browser/#auto-register-conditional-create
 */
final readonly class PasskeyComponent
{
    use HasAssets;
    use HasInheritanceCheck;
    use HasUser;
    use IsPersonable;

    public const SWITCH_CONTAINER = 'passkey-switcher-container';

    public const PASSKEY_CONTAINER = 'passkey-data-container';

    private const TRANS_KEY = 'passkeys::ui';

    private Authenticatable|Model|PasskeyContract|null $user;

    public function __construct()
    {
        $this->user = $this->getMoonShineUser();
    }

    public static function make(): MoonShineComponent
    {
        if (!config('passkeys.enabled')) {
            return LineBreak::make();
        }

        return Components::make([
            LineBreak::make(),
            (new self())->block(),
        ]);
    }

    private function block(): MoonShineComponent
    {
        try {
            $this->checkForRelatedModel($this->user);

            $components = [
                Alert::make(type: 'warning')
                    ->content(trans('passkeys::errors.not-supported'))
                    ->setAttribute('x-show', '!supported')
                    ->setAttribute('x-cloak', ''),
                Div::make([
                    $this->switcher(),
                    LineBreak::make(),
                    $this->passkeys(),
                    LineBreak::make(),
                ])
                    ->setAttribute('x-show', 'supported'),
            ];
        } catch (PasskeyInvalidInheritanceException $e) {
            $components = [
                Alert::make(type: 'error')->content(
                    config('passkeys.debug') ? $e->getMessage() : trans('passkeys::errors.common.not-configured')
                ),
                LineBreak::make(),
            ];
        }

        return Box::make('block', $components)
            ->translatable(self::TRANS_KEY)
            ->setAttribute('x-data', "{ supported: typeof PublicKeyCredential === 'function' }");
    }

    /**
     * @throws PasskeyException
     * @throws \Throwable
     */
    private function switcher(): MoonShineComponent
    {
        return Fragment::make([
            When::make(
                condition: fn (): bool => $this->user->passkeyMeta?->is_active === true,
                components: fn (): array => [
                    $this->switchButton('deactivate', 'deactivate'),
                ],
                default: fn (): array => [
                    $this->switchButton('activate', 'activate'),
                ]
            ),
        ])
            ->name(self::SWITCH_CONTAINER);
    }

    private function passkeys(): MoonShineComponent
    {
        $transKey = self::TRANS_KEY . '.passkey-container';

        return Fragment::make([
            When::make(
                condition: fn (): bool => $this->user->passkeyMeta?->is_active === true,
                components: fn (): array => [
                    Div::make([
                        Text::make('new-passkey-name-label')
                            ->translatable($transKey)
                            ->placeholder(trans($transKey . '.new-passkey-name-placeholder'))
                            ->setAttribute('maxlength', (string)Passkey::NAME_MAX_LENGTH)
                            ->setAttribute('x-model', 'newPasskey')
                            ->setAttribute(
                                '@keydown.enter',
                                '!loading && newPasskey.trim() && createPasskey(newPasskey)',
                            ),
                        LineBreak::make(),
                        Div::make()
                            ->setAttribute('x-show', 'error')
                            ->setAttribute('x-text', 'error')
                            ->class(['alert alert-error']),
                        LineBreak::make(),
                        ActionButton::make('new-passkey-name-button')
                            ->translatable($transKey)
                            ->onClick(fn (): string => 'createPasskey(newPasskey)')
                            ->setAttribute('x-bind:disabled', 'loading || !newPasskey.trim()'),
                        LineBreak::make(),
                    ])
                        ->addAssets($this->attestationAssets())
                        ->xData($this->attestationXData(container: self::PASSKEY_CONTAINER)),
                    ...$this->table(),
                ],
            ),
        ])
            ->name(self::PASSKEY_CONTAINER);
    }

    private function switchButton(string $label, string $route): ActionButton
    {
        return ActionButton::make(
            $label,
            route('moonshine.passkeys.management.' . $route)
        )
            ->translatable(self::TRANS_KEY)
            ->primary()
            ->async(HttpMethod::POST, events: [
                AlpineJs::event(JsEvent::FRAGMENT_UPDATED, self::PASSKEY_CONTAINER),
                AlpineJs::event(JsEvent::FRAGMENT_UPDATED, self::SWITCH_CONTAINER),
            ]);
    }

    /**
     * @return array<int, MoonShineComponent>
     */
    private function table(): array
    {
        $transKey = self::TRANS_KEY . '.passkey-container.table';
        $container = self::PASSKEY_CONTAINER;

        return [
            TableBuilder::make()
                ->async()
                ->fields([
                    Text::make('headers.name', 'name')
                        ->translatable($transKey)
                        ->changePreview(static fn (string $value): string => sprintf( // @codeCoverageIgnoreStart
                            '<span x-show="!editing" x-text="originalName">%s</span>'
                            . '<input type="text" x-show="editing" x-ref="editInput" x-model="editName"'
                            . ' @keydown.enter.prevent="saveEdit()" @keydown.escape.prevent="cancelEdit()"'
                            . ' @blur="$event.relatedTarget && $el.closest(\'[x-data]\').contains($event.relatedTarget) ? null : cancelEdit()"'
                            . ' maxlength="%d" class="form-input" x-cloak>',
                            e($value),
                            Passkey::NAME_MAX_LENGTH,
                        )), // @codeCoverageIgnoreEnd
                    Date::make('headers.updated-at', 'updated_at')->translatable($transKey),
                ])
                ->trAttributes(function (?DataWrapperContract $data) use ($container): array { // @codeCoverageIgnoreStart
                    /** @var Passkey|null $passkey */
                    $passkey = $data?->getOriginal();

                    if ($passkey === null) {
                        return [];
                    }

                    return [
                        'x-data' => 'passkeyRow(' . json_encode([
                            'name'      => $passkey->name,
                            'updateUrl' => route('moonshine.passkeys.update', [$passkey]),
                            'container' => $container,
                            'failedMessage' => trans('passkeys::errors.not-found'),
                        ]) . ')',
                    ];
                }) // @codeCoverageIgnoreEnd
                ->items($this->user->passkeys?->sortByDesc('updated_at'))
                ->buttons([
                    ActionButton::make()
                        ->primary()
                        ->icon('pencil')
                        ->setAttribute('x-show', '!editing')
                        ->setAttribute('x-cloak', '')
                        ->canSee(
                            fn (Passkey $passkey): bool => $this->isPersonable($this->user, $passkey)
                        )
                        ->onClick(fn (): string => 'startEdit()'),
                    ActionButton::make()
                        ->success()
                        ->icon('check')
                        ->setAttribute('x-show', 'editing')
                        ->setAttribute('x-cloak', '')
                        ->canSee(
                            fn (Passkey $passkey): bool => $this->isPersonable($this->user, $passkey)
                        )
                        ->onClick(fn (): string => 'saveEdit()'),
                    ActionButton::make()
                        ->icon('x-mark')
                        ->setAttribute('x-show', 'editing')
                        ->setAttribute('x-cloak', '')
                        ->canSee(
                            fn (Passkey $passkey): bool => $this->isPersonable($this->user, $passkey)
                        )
                        ->onClick(fn (): string => 'cancelEdit()'),
                    ActionButton::make(
                        url: static fn (Passkey $passkey): string => route('moonshine.passkeys.destroy', [$passkey]),
                    )
                        ->error()
                        ->icon('trash')
                        ->setAttribute('x-show', '!editing')
                        ->setAttribute('x-cloak', '')
                        ->canSee(
                            fn (Passkey $passkey): bool => $this->isPersonable($this->user, $passkey)
                        )
                        ->async(
                            method: HttpMethod::DELETE,
                            events: [
                                AlpineJs::event(JsEvent::FRAGMENT_UPDATED, self::PASSKEY_CONTAINER),
                            ],
                        )
                        ->withConfirm(
                            title: trans('passkeys::ui.passkey-container.modal.title'),
                            content: trans('passkeys::ui.passkey-container.modal.content'),
                            button: trans('passkeys::ui.passkey-container.modal.button'),
                        ),
                ])
                ->sticky()
                ->skeleton(true),
            LineBreak::make(),
        ];
    }
}
