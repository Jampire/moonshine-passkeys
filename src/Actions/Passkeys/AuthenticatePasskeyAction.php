<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Actions\Passkeys;

use Illuminate\Support\Facades\RateLimiter;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Concerns\HasLogger;
use Jampire\MoonshinePasskeys\Data\UpdatePasskeyData;
use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Services\AuthenticatorCounterChecker;
use Jampire\MoonshinePasskeys\Services\Result;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class AuthenticatePasskeyAction implements Actionable
{
    use HasLogger;

    public function execute(string $throttleKey, ?UpdatePasskeyData $data = null): Result
    {
        if (($seconds = $this->ensureIsNotRateLimited($throttleKey)) !== null) {
            return Result::err('exceptions.rate-limit-exceeded', [
                'seconds' => $seconds,
            ]);
        }

        if (!config('passkeys.enabled') || !$data instanceof UpdatePasskeyData) {
            return Result::err('authentication-failed');
        }

        try {
            /** @var Passkey $passkey */
            $passkey = Passkey::query()
                ->where('credential_id', $data->publicKeyCredential->rawId)
                ->first();

            if ($passkey === null) {
                return Result::err('authentication-failed');
            }

            /** @var PasskeyContract $user */
            $user = $passkey->personable;

            if (!$user->passkeyMeta?->is_active) {
                return Result::err('authentication-failed');
            }

            $csmFactory = new CeremonyStepManagerFactory();

            if (config('passkeys.counter_checker')) {
                $csmFactory->setCounterChecker(AuthenticatorCounterChecker::make());
            }

            $requestCeremony = $csmFactory->requestCeremony();
            $validator = AuthenticatorAssertionResponseValidator::create($requestCeremony);

            $this->setLoggerIfRequired($validator);

            // validates passkey
            $pKCredentialSource = $validator->check(
                publicKeyCredentialSource: $passkey->data,
                authenticatorAssertionResponse: $data->assertionResponse,
                publicKeyCredentialRequestOptions: $data->authOptions,
                host: $data->host,
                userHandle: $user?->passkeyMeta?->public_id,
            );

            // @codeCoverageIgnoreStart
            $passkey->data = $pKCredentialSource;
            $passkey->save();

            return Result::ok($passkey);
            // @codeCoverageIgnoreEnd
        } catch (\Throwable $e) {
            captureException($e, self::class);

            return Result::err(error: 'authentication-failed', debug: $e->getMessage());
        }
    }

    private function ensureIsNotRateLimited(string $throttleKey): ?int
    {
        return app()->isProduction()
        && RateLimiter::tooManyAttempts($throttleKey, config('passkeys.rate_limits.attempts'))
            ? RateLimiter::availableIn($throttleKey)
            : null;
    }
}
