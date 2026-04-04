<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Actions\Options;

use Illuminate\Support\Str;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Concerns\HasInheritanceCheck;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Services\Result;
use MoonShine\Laravel\MoonShineAuth;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class AuthenticateOptionsAction implements Actionable
{
    use HasInheritanceCheck;

    public function execute(
        ?string $value = null,
        ?string $modelClass = null,
        string $column = 'email',
    ): Result {
        if (!config('passkeys.enabled')) {
            return Result::err('authentication-failed');
        }

        $userVerification = config('passkeys.authenticator_selection_criteria.authentication.user_verification');

        try {
            $modelClass ??= MoonShineAuth::getModel()::class;

            $this->checkForRelatedModel($modelClass);

            $allowedCredentials = [];

            if ($value !== null) {
                $passkeys = Passkey::query()
                    ->whereMorphRelation('personable', $modelClass, $column, $value)
                    ->get();

                if (!$passkeys->isUserPasskeyActive()) {
                    return Result::err('authentication-failed');
                }

                $allowedCredentials = $passkeys->credentialDescriptors()->all();
            }

            $options = new PublicKeyCredentialRequestOptions(
                challenge: Str::random(32),
                rpId: config('passkeys.rp_id'),
                allowCredentials: $allowedCredentials,
                userVerification: $value === null ? $userVerification : null,
                timeout: $value === null ? $this->getTimeout($userVerification) : null,
            );

            return Result::ok($options);
        } catch (\Throwable $e) {
            captureException($e, self::class, [
                'username' => $value,
                'model_class' => $modelClass,
                'column' => $column,
            ]);

            // Enumeration protection
            $options = new PublicKeyCredentialRequestOptions(
                challenge: Str::random(32),
                rpId: config('passkeys.rp_id'),
                userVerification: $userVerification,
                timeout: $this->getTimeout($userVerification),
            );

            return Result::ok($options);
        }
    }

    /**
     * @return positive-int|null
     */
    private function getTimeout(string $expression): ?int
    {
        $config = config('passkeys.ttl.auth');

        return match ($expression) {
            AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED => $config['preferred'],
            AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_DISCOURAGED => $config['discourage'],
            default => null,
        };
    }
}
