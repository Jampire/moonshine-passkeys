<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\PasskeyServiceProvider;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Concerns\HasLogger;
use Jampire\MoonshinePasskeys\Exceptions\PasskeyException;
use Jampire\MoonshinePasskeys\Http\Controllers\Controller;
use Jampire\MoonshinePasskeys\Services\Serializers\AbstractSerializer;

arch('Strict Types')
    ->expect('Jampire\MoonshinePasskeys')
    ->toUseStrictTypes()
    ->not->toUse(['die', 'dd', 'dump']);

arch('Final Classes')
    ->expect('Jampire\MoonshinePasskeys')
    ->classes()
    ->toBeFinal()
    ->ignoring([
        AbstractSerializer::class,
        Controller::class,
        PasskeyException::class,
    ]);

arch('HTTP')
    ->expect('Jampire\MoonshinePasskeys\Http')
    ->toOnlyBeUsedIn([
        'Jampire\MoonshinePasskeys\Http',
        PasskeyServiceProvider::class,
        'Jampire\MoonshinePasskeys\Data',
        'Jampire\MoonshinePasskeys\Components',
    ]);

arch('Actions')
    ->expect('Jampire\MoonshinePasskeys\Actions')
    ->toImplement(Actionable::class)
    ->ignoring([
        HasLogger::class,
    ]);
