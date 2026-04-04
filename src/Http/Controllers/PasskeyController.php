<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Actions\Passkeys\RegisterPasskeyAction;
use Jampire\MoonshinePasskeys\Actions\Passkeys\UpdatePasskeyAction;
use Jampire\MoonshinePasskeys\Data\CreatePasskeyData;
use Jampire\MoonshinePasskeys\Http\Concerns\HasUser;
use Jampire\MoonshinePasskeys\Http\Requests\CreatePasskeyRequest;
use Jampire\MoonshinePasskeys\Http\Requests\UpdatePasskeyRequest;
use Jampire\MoonshinePasskeys\Models\Passkey;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 * @todo https://getmoonshine.app/en/docs/4.x/advanced/notifications
 */
final class PasskeyController extends Controller
{
    use HasUser;

    public function __construct(
        private readonly Actionable|RegisterPasskeyAction $registerPasskeyAction,
        private readonly Actionable|UpdatePasskeyAction $updatePasskeyAction,
    ) {
        //
    }

    public function store(CreatePasskeyRequest $request): JsonResponse
    {
        $result = $this->registerPasskeyAction->execute(
            $this->getMoonShineUser(),
            CreatePasskeyData::fromRequest($request),
        );

        return Response::result($result, successStatus: Response::HTTP_CREATED);
    }

    public function update(
        UpdatePasskeyRequest $request,
        Passkey $passkey,
    ): JsonResponse {
        $result = $this->updatePasskeyAction->execute($passkey, $request->validated('name'));

        return Response::result($result, errorStatus: Response::HTTP_NOT_FOUND);
    }

    public function destroy(Passkey $passkey): JsonResponse
    {
        Gate::forUser($this->getMoonShineUser())->authorize('delete', $passkey);

        return $passkey->delete()
            ? Response::success(code: 'passkey-deleted')
            : Response::error(code: 'not-found', status: Response::HTTP_NOT_FOUND);
    }
}
