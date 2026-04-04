<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Controllers;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Actions\Passkeys\AuthenticatePasskeyAction;
use Jampire\MoonshinePasskeys\Data\UpdatePasskeyData;
use Jampire\MoonshinePasskeys\Http\Requests\AuthenticatePasskeyRequest;
use MoonShine\Contracts\Core\DependencyInjection\RouterContract;
use MoonShine\Laravel\MoonShineAuth;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 * @todo MoonShine authentication, getAuthPipelines
 */
final class PasskeyAuthController extends Controller
{
    /**
     * @param AuthenticatePasskeyAction $action
     */
    public function __construct(
        private readonly Actionable $action,
        private readonly RouterContract $router,
    ) {
        //
    }

    public function __invoke(AuthenticatePasskeyRequest $request): RedirectResponse|JsonResponse
    {
        $throttleKey = $request->validated('answer') . '|' . $request->ip();

        $result = $this->action->execute($throttleKey, UpdatePasskeyData::fromRequest($request));

        if ($result->isErr()) {
            RateLimiter::hit($throttleKey, config('passkeys.rate_limits.decay_seconds'));

            if ($result->err === 'exceptions.rate-limit-exceeded') {
                event(new Lockout($request));
            }

            if ($request->expectsJson()) {
                // @codeCoverageIgnoreStart
                return Response::result($result);
                // @codeCoverageIgnoreEnd
            }

            throw ValidationException::withMessages([
                'username' => trans('passkeys::errors.' . $result->errorCode(), $result->params),
            ]);
        }

        MoonShineAuth::getGuard()->login($result->val->personable, $request->validated('remember'));
        $request->session()->regenerate();

        RateLimiter::clear($throttleKey);

        return redirect()->intended(
            $this->router->getEndpoints()->home()
        );
    }
}
