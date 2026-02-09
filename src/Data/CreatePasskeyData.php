<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Data;

use Illuminate\Support\Str;
use Jampire\MoonshinePasskeys\Exceptions\PasskeyException;
use Jampire\MoonshinePasskeys\Http\Requests\CreatePasskeyRequest;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Services\Options\Contracts\OptionsStoreContract;
use Jampire\MoonshinePasskeys\Services\Serializers\Contracts\SerializerContract;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class CreatePasskeyData
{
    public AuthenticatorAttestationResponse $attestationResponse;

    public string $name;

    /**
     * @throws PasskeyException
     */
    private function __construct(
        private PublicKeyCredential $publicKeyCredential,
        public PublicKeyCredentialCreationOptions $registrationOptions,
        string $name,
        public string $host,
    ) {
        if (!$this->publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            throw PasskeyException::invalidAuthenticatorAttestationResponse();
        }

        // @codeCoverageIgnoreStart
        $this->attestationResponse = $this->publicKeyCredential->response;
        $this->name = Str::limit($name, Passkey::NAME_MAX_LENGTH);
        // @codeCoverageIgnoreEnd
    }

    public static function fromRequest(CreatePasskeyRequest $request): self|null
    {
        $data = $request->validated();

        $serializer = app(SerializerContract::class);
        $optionsStore = app(OptionsStoreContract::class);

        $options = $optionsStore->getRegistrationOptions();

        if ($options === null) {
            captureException(PasskeyException::invalidAuthenticatorAttestationResponse(), self::class);

            return null;
        }

        try {
            return new self(
                publicKeyCredential: $serializer->deserialize(
                    data: $data['passkey'],
                    type: PublicKeyCredential::class,
                ),
                registrationOptions: $serializer->deserialize(
                    data: $options,
                    type: PublicKeyCredentialCreationOptions::class,
                ),
                name: $data['name'],
                host: $request->getHost(),
            );
        } catch (\Throwable $e) {
            captureException($e, self::class);

            return null;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    private function __clone(): void
    {
        //
    }
}
