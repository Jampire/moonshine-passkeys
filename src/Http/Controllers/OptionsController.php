<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Actions\Options\AuthenticateOptionsAction;
use Jampire\MoonshinePasskeys\Actions\Options\RegisterUserOptionsAction;
use Jampire\MoonshinePasskeys\Http\Concerns\HasUser;
use Jampire\MoonshinePasskeys\Http\Requests\AuthenticateOptionsRequest;
use Jampire\MoonshinePasskeys\Http\Requests\RegisterOptionsRequest;
use Jampire\MoonshinePasskeys\Services\Options\Contracts\OptionsStoreContract;
use Jampire\MoonshinePasskeys\Services\Serializers\Contracts\SerializerContract;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class OptionsController extends Controller
{
    use HasUser;

    public function __construct(
        private readonly SerializerContract $serializerService,
        private readonly OptionsStoreContract $optionsStore,
        private readonly Actionable|RegisterUserOptionsAction $registerUserOptionsAction,
        private readonly Actionable|AuthenticateOptionsAction $authenticateOptionsAction,
    ) {
        //
    }

    public function registerUserOptions(RegisterOptionsRequest $request): JsonResponse
    {
        $result = $this->registerUserOptionsAction->execute($this->getMoonShineUser());

        if ($result->isErr()) {
            return Response::result($result);
        }

        /** @var PublicKeyCredentialCreationOptions $options */
        $options = $result->val;

        $serializedOptions = $this->serializerService->serialize($options);
        $this->optionsStore->saveRegistrationOptions($serializedOptions);

        return JsonResponse::fromJsonString($serializedOptions);
    }

    public function authenticateOptions(AuthenticateOptionsRequest $request): JsonResponse
    {
        $result = $this->authenticateOptionsAction->execute($request->validated('email'));

        if ($result->isErr()) {
            return Response::result($result, errorStatus: Response::HTTP_NOT_FOUND);
        }

        /** @var PublicKeyCredentialRequestOptions $options */
        $options = $result->val;

        $serializedOptions = $this->serializerService->serialize($options);
        $this->optionsStore->saveAuthenticationOptions($serializedOptions);

        return JsonResponse::fromJsonString($serializedOptions);
    }
}
