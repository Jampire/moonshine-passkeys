<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Actions\Passkeys;

use Illuminate\Contracts\Auth\Authenticatable;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Concerns\HasInheritanceCheck;
use Jampire\MoonshinePasskeys\Concerns\HasLogger;
use Jampire\MoonshinePasskeys\Data\CreatePasskeyData;
use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;
use Jampire\MoonshinePasskeys\Services\Result;
use Throwable;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class RegisterPasskeyAction implements Actionable
{
    use HasLogger;
    use HasInheritanceCheck;

    public function execute(Authenticatable|PasskeyContract $person, ?CreatePasskeyData $data = null): Result
    {
        if (!config('passkeys.enabled') || !$data instanceof CreatePasskeyData) {
            return Result::err('not-created');
        }

        // @codeCoverageIgnoreStart
        try {
            $this->checkForRelatedModel($person);

            $creationCeremony = (new CeremonyStepManagerFactory())->creationCeremony();
            $validator = AuthenticatorAttestationResponseValidator::create($creationCeremony);

            $this->setLoggerIfRequired($validator);

            // validates passkey
            $pKCredentialSource = $validator->check(
                authenticatorAttestationResponse: $data->attestationResponse,
                publicKeyCredentialCreationOptions: $data->registrationOptions,
                host: $data->host,
            );

            $person->passkeys()->create([
                'name' => $data->name,
                'data' => $pKCredentialSource,
            ]);

            return Result::ok('passkey-created');
        } catch (Throwable $e) {
            captureException($e, self::class, [
                'user_id' => $person->id,
                'user_class' => $person::class,
                'passkey_name' => $data->name,
            ]);

            return Result::err('not-created');
        }

        // @codeCoverageIgnoreEnd
    }
}
