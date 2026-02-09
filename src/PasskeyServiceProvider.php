<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Actions\Options\AuthenticateOptionsAction;
use Jampire\MoonshinePasskeys\Actions\Options\RegisterUserOptionsAction;
use Jampire\MoonshinePasskeys\Actions\Passkeys\AuthenticatePasskeyAction;
use Jampire\MoonshinePasskeys\Actions\Passkeys\RegisterPasskeyAction;
use Jampire\MoonshinePasskeys\Actions\Passkeys\UpdatePasskeyAction;
use Jampire\MoonshinePasskeys\Actions\PasskeySwitchAction;
use Jampire\MoonshinePasskeys\Components\PasskeyComponent;
use Jampire\MoonshinePasskeys\Data\AuthenticatorSelectionData;
use Jampire\MoonshinePasskeys\Http\Controllers\ManagementController;
use Jampire\MoonshinePasskeys\Http\Controllers\OptionsController;
use Jampire\MoonshinePasskeys\Http\Controllers\PasskeyAuthController;
use Jampire\MoonshinePasskeys\Http\Controllers\PasskeyController;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Services\Options\Contracts\OptionsStoreContract;
use Jampire\MoonshinePasskeys\Services\Result;
use Jampire\MoonshinePasskeys\Services\Serializers\Contracts\SerializerContract;
use MoonShine\Laravel\Pages\ProfilePage;
use MoonShine\UI\Components\MoonShineComponent;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 * @todo Check for https during installation
 * @todo Note that is mandatory to use the HTTPS scheme to use
 * @todo Webauthn otherwise it will not work. This is also mandatory for localhost.
 * @todo message to disable 2fa if installed or select priority
 */
final class PasskeyServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/passkeys.php', 'passkeys');

        $this->app->singleton(SerializerContract::class, function (): SerializerContract {
            $classname = config('passkeys.serializer');

            // @phpstan-ignore-next-line
            return new $classname();
        });

        $this->app->bind(OptionsStoreContract::class, function (): OptionsStoreContract {
            $classname = config('passkeys.options_service');

            // @phpstan-ignore-next-line
            return new $classname();
        });

        $authorizationCriteria = config('passkeys.authenticator_selection_criteria.authorization');
        $this->app->when(RegisterUserOptionsAction::class)
            ->needs(AuthenticatorSelectionData::class)
            ->give(fn (): AuthenticatorSelectionData => new AuthenticatorSelectionData(
                authenticatorAttachment: $authorizationCriteria['authenticator_attachment'],
                userVerification: $authorizationCriteria['user_verification'],
                residentKey: $authorizationCriteria['resident_key'],
            ));

        $this->bindActions();
        $this->responseMacros();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'passkeys');
        $this->loadRoutesFrom(__DIR__ . '/../routes/passkeys.php');

        Gate::policy(Passkey::class, config('passkeys.policy_class'));

        $this->publishAssets();
        $this->publishResources();

        $profile = config('moonshine.pages.profile', ProfilePage::class);
        $profile::pushComponent(fn (): MoonShineComponent => PasskeyComponent::make());
    }

    private function bindActions(): void
    {
        $this->app->when(ManagementController::class)
            ->needs(Actionable::class)
            ->give(fn (): PasskeySwitchAction => app(PasskeySwitchAction::class));

        $this->app->when(OptionsController::class)
            ->needs('$registerUserOptionsAction')
            ->give(fn (): RegisterUserOptionsAction => app(RegisterUserOptionsAction::class));

        $this->app->when(OptionsController::class)
            ->needs('$authenticateOptionsAction')
            ->give(fn (): AuthenticateOptionsAction => app(AuthenticateOptionsAction::class));

        $this->app->when(PasskeyAuthController::class)
            ->needs(Actionable::class)
            ->give(fn (): AuthenticatePasskeyAction => app(AuthenticatePasskeyAction::class));

        $this->app->when(PasskeyController::class)
            ->needs('$registerPasskeyAction')
            ->give(fn (): RegisterPasskeyAction => app(RegisterPasskeyAction::class));

        $this->app->when(PasskeyController::class)
            ->needs('$updatePasskeyAction')
            ->give(fn (): UpdatePasskeyAction => app(UpdatePasskeyAction::class));
    }

    private function responseMacros(): void
    {
        Response::macro(
            'result',
            function (
                Result $result,
                int $successStatus = Response::HTTP_OK,
                int $errorStatus = Response::HTTP_BAD_REQUEST,
            ): JsonResponse {
                return $result->isOk()
                    ? Response::success(code: $result->val, parameters: $result->params, status: $successStatus)
                    : Response::error(code: $result->errorCode(), parameters: $result->params, status: $errorStatus);
            }
        );

        Response::macro(
            'success',
            function (
                ?string $code = null,
                array $parameters = [],
                int $status = Response::HTTP_OK,
            ): JsonResponse {
                $message = trans('passkeys::success.' . ($code ?? 'ok'), $parameters);

                return response()->json(
                    data: [
                        'code' => $code ?? 'ok',
                        'message' => $message,
                    ],
                    status: $status,
                );
            },
        );

        Response::macro(
            'error',
            function (
                string $code,
                array $parameters = [],
                int $status = Response::HTTP_BAD_REQUEST,
            ): JsonResponse {
                $message = trans('passkeys::errors.' . $code, $parameters);

                return response()->json(
                    data: [
                        'code' => $code,
                        'message' => $message,
                    ],
                    status: $status,
                );
            },
        );
    }

    private function publishResources(): void
    {
        if (!$this->app->runningInConsole()) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $this->publishes(
            [
                __DIR__ . '/../config/passkeys.php' => config_path('passkeys.php'),
            ],
            [
                'passkeys',
                'config',
            ]
        );

        $this->publishes(
            [
                __DIR__.'/../lang' => $this->app->langPath('vendor/passkeys')
            ],
            [
                'passkeys',
                'lang',
            ]
        );

        $this->publishAssets();
    }

    private function publishAssets(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../resources/assets' => public_path('vendor/moonshine-passkeys'),
            ],
            [
                'passkeys-assets',
                'laravel-assets',
                'assets',
            ]
        );
    }
}
