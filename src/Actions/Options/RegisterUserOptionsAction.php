<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Actions\Options;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Concerns\HasInheritanceCheck;
use Jampire\MoonshinePasskeys\Data\AuthenticatorSelectionData;
use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;
use Jampire\MoonshinePasskeys\Models\PasskeyMeta;
use Jampire\MoonshinePasskeys\Services\Result;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class RegisterUserOptionsAction implements Actionable
{
    use HasInheritanceCheck;

    public function __construct(private AuthenticatorSelectionData $authenticatorSelectionData)
    {
        //
    }

    public function execute(Authenticatable|PasskeyContract $person): Result
    {
        try {
            $this->checkForRelatedModel($person);

            /** @var PasskeyMeta $passkeyMeta */
            $passkeyMeta = $person->passkeyMeta;
            if ($passkeyMeta === null) {
                $passkeyMeta = $person->passkeyMeta()->create();
            }

            $options = new PublicKeyCredentialCreationOptions(
                rp: new PublicKeyCredentialRpEntity(
                    name: config('app.name'),
                    id: config('passkeys.rp_id'),
                    icon: config('passkeys.rp_icon'),
                ),
                user: new PublicKeyCredentialUserEntity(
                    name: $person->{config('passkeys.user_entity.name_column')},
                    id: $passkeyMeta->public_id,
                    displayName: $person->name,
                ),
                challenge: Str::random(32),
                pubKeyCredParams: collect(config('passkeys.algos'))
                    ->map(fn (int $alg): PublicKeyCredentialParameters => PublicKeyCredentialParameters::createPk($alg))
                    ->all(),
                authenticatorSelection: new AuthenticatorSelectionCriteria(
                    authenticatorAttachment: $this->authenticatorSelectionData->authenticatorAttachment,
                    userVerification: $this->authenticatorSelectionData->userVerification,
                    residentKey: $this->authenticatorSelectionData->residentKey,
                ),
                excludeCredentials: $person->passkeys?->credentialDescriptors()?->all() ?? [],
                timeout: config('passkeys.ttl.register'),
            );

            return Result::ok($options);
        } catch (\Throwable $e) {
            captureException($e, self::class, [
                'user_id' => $person->id,
                'user_class' => $person::class,
            ]);

            return Result::err(error: 'options.register', debug: $e->getMessage());
        }
    }
}
