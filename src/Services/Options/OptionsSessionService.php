<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Services\Options;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Jampire\MoonshinePasskeys\Services\Options\Contracts\OptionsStoreContract;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 * @experimental
 */
final readonly class OptionsSessionService implements OptionsStoreContract
{
    public function saveRegistrationOptions(string $options): void
    {
        $this->saveOptions(self::REGISTRATION_KEY, $options);
    }

    public function getRegistrationOptions(): ?string
    {
        return $this->getOptions(self::REGISTRATION_KEY);
    }

    public function saveAuthenticationOptions(string $options): void
    {
        $this->saveOptions(self::AUTHENTICATION_KEY, $options);
    }

    public function getAuthenticationOptions(): ?string
    {
        return $this->getOptions(self::AUTHENTICATION_KEY);
    }

    private function saveOptions(string $key, string $options): void
    {
        Session::put($key, base64_encode($options));
        $this->setTimeout($key);
    }

    private function getOptions(string $key): ?string
    {
        $value = base64_decode((string) Session::pull($key));

        return $this->isValid($key) ? $value : null;
    }

    private function setTimeout(string $key): void
    {
        Session::put(
            $key . '-timeout',
            Date::now()->addSeconds(config('passkeys.options_store.ttl'))->toDateTimeString()
        );
    }

    private function isValid(string $key): bool
    {
        $timeout = Session::pull($key . '-timeout');

        if (empty($timeout)) {
            return false;
        }

        $timeout = Date::parse($timeout);

        return $timeout instanceof Carbon && Date::now()->lessThanOrEqualTo($timeout);
    }
}
