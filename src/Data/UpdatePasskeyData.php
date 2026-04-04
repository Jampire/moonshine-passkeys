<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Data;

use Jampire\MoonshinePasskeys\Exceptions\PasskeyException;
use Jampire\MoonshinePasskeys\Http\Requests\AuthenticatePasskeyRequest;
use Jampire\MoonshinePasskeys\Services\Options\Contracts\OptionsStoreContract;
use Jampire\MoonshinePasskeys\Services\Serializers\Contracts\SerializerContract;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class UpdatePasskeyData
{
    public AuthenticatorAssertionResponse $assertionResponse;

    /**
     * @throws PasskeyException
     */
    private function __construct(
        public PublicKeyCredential $publicKeyCredential,
        public PublicKeyCredentialRequestOptions $authOptions,
        public string $host,
    ) {
        if (!$this->publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            // @codeCoverageIgnoreStart
            throw PasskeyException::invalidAuthenticatorAssertionResponse();
            // @codeCoverageIgnoreEnd
        }

        $this->assertionResponse = $this->publicKeyCredential->response;
    }

    public static function fromRequest(AuthenticatePasskeyRequest $request): self|null
    {
        $data = $request->validated();

        $serializer = app(SerializerContract::class);
        $optionsStore = app(OptionsStoreContract::class);

        $options = $optionsStore->getAuthenticationOptions();

        if ($options === null) {
            captureException(PasskeyException::invalidPasskey(), self::class);

            return null;
        }

        try {
            return new self(
                publicKeyCredential: $serializer->deserialize(
                    data: $data['answer'],
                    type: PublicKeyCredential::class,
                ),
                authOptions: $serializer->deserialize(
                    data: $options,
                    type: PublicKeyCredentialRequestOptions::class,
                ),
                host: $request->getHost(),
            );
            // @codeCoverageIgnoreStart
        } catch (\Throwable $e) {
            captureException($e, self::class);

            return null;
        }

        // @codeCoverageIgnoreEnd
    }

    /**
     * @codeCoverageIgnore
     */
    private function __clone(): void
    {
        //
    }
}
