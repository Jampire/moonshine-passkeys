<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Data;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class AuthenticatorSelectionData
{
    public function __construct(
        public string $authenticatorAttachment,
        public string $userVerification,
        public string $residentKey,
    ) {
        //
    }
}
