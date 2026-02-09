<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Concerns;

use Illuminate\Support\Facades\Log;
use Webauthn\MetadataService\CanLogData;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
trait HasLogger
{
    private function setLoggerIfRequired(CanLogData $object): void
    {
        if (config('passkeys.debug')) {
            $object->setLogger(Log::getLogger());
        }
    }
}
