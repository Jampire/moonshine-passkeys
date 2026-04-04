<?php

declare(strict_types=1);

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */

return [
    'common' => [
        'not-configured' => 'Passkeys package is not configured. Please contact your administrator.',
        'operation-failed' => 'Operation failed.',
        'passkey-not-deleted' => 'Passkey was not deleted.',
    ],
    'name-already-exists' => 'The name has already been taken.',
    'not-created' => 'Passkey was not created.',
    'registration-failed' => 'Registration failed.',
    'authentication-failed' => 'Authentication failed.',
    'not-allowed' => 'Operation cancelled or timed out.',
    'invalid-state' => 'Authenticator already registered.',
    'not-supported' => 'WebAuthn not supported in this browser.',
    'aborted' => 'Operation was aborted.',
    'not-found' => 'Passkey not found.',
    'exceptions' => [
        'prefix' => 'Passkey exception: :msg.',
        'related-model' => ':user model should implement :contract contract.',
        'attestation' => 'Authenticator Attestation Response is invalid.',
        'assertion' => 'Authenticator Assertion Response is invalid.',
        'invalid-passkey' => 'The given passkey is invalid.',
        'fake-device' => 'The fake device has been detected.',
        'rate-limit-exceeded' => 'Too many login attempts. Please try again in :seconds seconds.',
    ],
    'options' => [
        'register' => 'Options registration is failed.',
        'authenticate' => 'Options authentication is failed.',
    ],
];
