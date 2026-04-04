<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Components\Concerns;

use Illuminate\Support\Str;
use MoonShine\AssetManager\Js;
use MoonShine\Contracts\AssetManager\AssetElementContract;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 * @codeCoverageIgnore
 */
trait HasAssets
{
    /**
     * @return list<AssetElementContract>
     */
    private function attestationAssets(): array
    {
        return [
            $this->webauthnAsset(),
            Js::make('vendor/moonshine-passkeys/js/attestation.js')
                ->version($this->libraryVersion()),
        ];
    }

    private function attestationXData(string $container): string
    {
        return 'passkeyAttestation(' . json_encode([
                'optionsUrl' => route('moonshine.passkeys.register-user-options'),
                'storeUrl' => route('moonshine.passkeys.store'),
                'container' => $container,
                'messages' => [
                    'registrationFailed' => trans('passkeys::errors.registration-failed'),
                    'notAllowed' => trans('passkeys::errors.not-allowed'),
                    'invalidState' => trans('passkeys::errors.invalid-state'),
                    'notSupported' => trans('passkeys::errors.not-supported'),
                    'aborted' => trans('passkeys::errors.aborted'),
                ],
            ]) . ')';
    }

    /**
     * @return list<AssetElementContract>
     */
    private function assertionAssets(): array
    {
        return [
            $this->webauthnAsset(),
            Js::make('vendor/moonshine-passkeys/js/assertion.js')
                ->version($this->libraryVersion()),
        ];
    }

    private function assertionXData(string $authenticateUrl, string $failedMessage): string
    {
        return 'passkeyAssertion(' . json_encode([
                'optionsUrl' => route('moonshine.passkeys.authenticate-options'),
                'authenticateUrl' => $authenticateUrl,
                'useBrowserAutofill' => config('passkeys.assets.conditional_ui'),
                'messages' => [
                    'failed' => $failedMessage,
                ],
            ]) . ')';
    }

    private function webauthnAsset(): Js
    {
        return Js::make('vendor/moonshine-passkeys/js/simplewebauthn_browser.js')
            ->version('13.2.2');
    }

    private function libraryVersion(): string
    {
        return config('passkeys.debug') ? Str::random(10) : config('passkeys.version');
    }
}
