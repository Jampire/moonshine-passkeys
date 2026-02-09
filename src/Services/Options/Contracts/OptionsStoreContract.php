<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Services\Options\Contracts;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
interface OptionsStoreContract
{
    public const REGISTRATION_KEY = 'passkey-registration-options';

    public const AUTHENTICATION_KEY = 'passkey-authentication-options';

    public function saveRegistrationOptions(string $options): void;

    public function getRegistrationOptions(): ?string;

    public function saveAuthenticationOptions(string $options): void;

    public function getAuthenticationOptions(): ?string;
}
