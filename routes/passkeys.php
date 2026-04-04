<?php

declare(strict_types=1);

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */

use Illuminate\Support\Facades\Route;
use Jampire\MoonshinePasskeys\Http\Controllers\ManagementController;
use Jampire\MoonshinePasskeys\Http\Controllers\OptionsController;
use Jampire\MoonshinePasskeys\Http\Controllers\PasskeyAuthController;
use Jampire\MoonshinePasskeys\Http\Controllers\PasskeyController;
use MoonShine\Laravel\Http\Middleware\ChangeLocale;

Route::moonshine(static function (): void {
    Route::as('passkeys.')
        ->prefix('/passkeys')
        ->middleware(ChangeLocale::class)
        ->group(static function (): void {
            Route::controller(ManagementController::class)
                ->as('management.')
                ->group(static function (): void {
                    Route::post('activate', 'activate')->name('activate');
                    Route::post('deactivate', 'deactivate')->name('deactivate');
                });

            Route::controller(OptionsController::class)
                ->group(static function (): void {
                    Route::post('/register-user-options', 'registerUserOptions')
                        ->name('register-user-options');

                    Route::post('/authenticate-options', 'authenticateOptions')
                        ->withoutMiddleware(moonshineConfig()->getAuthMiddleware())
                        ->name('authenticate-options');
                });

            Route::post('/authenticate', PasskeyAuthController::class)
                ->withoutMiddleware(moonshineConfig()->getAuthMiddleware())
                ->name('authenticate');
        });
}, withAuthenticate: true);

Route::moonshine(static function (): void {
    Route::resource('passkeys', PasskeyController::class)
        ->middleware(ChangeLocale::class)
        ->only(['store', 'update', 'destroy']);
}, withAuthenticate: true);
