<?php

declare(strict_types=1);

use Cose\Algorithms;
use Jampire\MoonshinePasskeys\Services\Options\OptionsSessionService;
use Jampire\MoonshinePasskeys\Policies\PasskeyPolicy;
use Jampire\MoonshinePasskeys\Services\Serializers\DefaultSerializerService;
use MoonShine\Crud\Forms\LoginForm;
use Webauthn\AuthenticatorSelectionCriteria;

return [

    'enabled' => (bool)env('PASSKEYS_ENABLED', true),

    // this will increase a log file in size
    'debug' => (bool)env('PASSKEYS_DEBUG', config('app.debug')),

    'table_names' => [
        'passkeys' => (string)env('PASSKEYS_DB_PASSKEYS_TABLE', 'moonshine_passkeys'),
        'metas' => (string)env('PASSKEYS_DB_METAS_TABLE', 'moonshine_passkey_metas'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Relying Party ID
    |--------------------------------------------------------------------------
    |
    | The Relying Party (RP) corresponds to the application that will ask
    | for the user to interact with the authenticator.
    | The rp ID shall be the domain of the application without the scheme,
    | userinfo, port, path, user...
    | For example, a web site is located at https://(www.)site1.host.com
    | and another at https://(www.)site2.host.com, then the Relying Party IDs
    | should be site1.host.com and site2.host.com respectively. If you set
    | host.com, there is a risk that users from site1.host.com can log in
    | at site2.host.com.
    |
    */

    'rp_id' => (string)env(
        'PASSKEYS_RELYING_PARTY_ID',
        parse_url((string) config('app.url'), PHP_URL_HOST)
    ),

    /*
    |--------------------------------------------------------------------------
    | Relying Party Icon
    |--------------------------------------------------------------------------
    |
    | Application logo. For safety reason this icon is a priori
    | authenticated URL i.e. an image that uses the data scheme.
    | @see https://webauthn-doc.spomky-labs.com/prerequisites/the-relying-party#relying-party-icon
    | The icon may be ignored by browsers, especially if its length
    | is greater than 128 bytes.
    |
    */

    'rp_icon' => (string)env('PASSKEYS_RELYING_PARTY_ICON', config('moonshine.logo_small')),

    /*
    |--------------------------------------------------------------------------
    | Algorithms
    |--------------------------------------------------------------------------
    |
    | Algorithms that are used to register new options
    |
    */

    'algos' => [
        Algorithms::COSE_ALGORITHM_ES256,
        Algorithms::COSE_ALGORITHM_RS256,
        Algorithms::COSE_ALGORITHM_PS256,
        Algorithms::COSE_ALGORITHM_ED256,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authenticator Selection Criteria
    |--------------------------------------------------------------------------
    |
    | It regulates how the passkey will be created and how the user will be
    | authenticated with it.
    | Please see
    | https://webauthn-doc.spomky-labs.com/v5.3/pure-php/advanced-behaviours/authenticator-selection-criteria#authenticator-attachment-modality
    | https://webauthn-doc.spomky-labs.com/v5.3/webauthn-in-a-nutshell/user-verification#best-practices
    | https://webauthn-doc.spomky-labs.com/v5.3/pure-php/advanced-behaviours/authenticator-selection-criteria#authenticator-attachment-modality
    |
    */

    'authenticator_selection_criteria' => [

        // login with passkey
        'authentication' => [
            'authenticator_attachment' => AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM,
            'user_verification' => AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            'resident_key' => AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
        ],

        // passkey registration
        'authorization' => [
            'authenticator_attachment' => AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM,
            'user_verification' => AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            'resident_key' => AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Entity
    |--------------------------------------------------------------------------
    |
    | A User Entity object represents a user in the Webauthn context.
    |
    */

    'user_entity' => [
        // It should be defined as unique column in DB.
        // For privacy reasons, it is not recommended using the e-mail as name_column.
        // But by default `moonshine_users` table has uniqueness for `email` column only.
        // Change this to something else unique column if you have one.
        // @see https://webauthn-doc.spomky-labs.com/v5.3/prerequisites/user-entity-repository
        'name_column' => 'email',
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets (JS) configuration
    |--------------------------------------------------------------------------
    |
    | Allows to configure assets component.
    |
    */

    'assets' => [
        // Enables conditional UI (https://simplewebauthn.dev/docs/packages/browser/#browser-autofill-conditional-ui).
        'conditional_ui' => (bool)env('PASSKEYS_CONDITIONAL_UI', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets (JS) configuration
    |--------------------------------------------------------------------------
    |
    | Login form to use when passkeys are disabled.
    |
    */

    'backup_login_form' => (string)env('PASSKEY_BACKUP_LOGIN_FORM', LoginForm::class),

    /*
    |--------------------------------------------------------------------------
    | Options Store configuration
    |--------------------------------------------------------------------------
    |
    */

    'options_store' => [
        'ttl' => (int)env('PASSKEY_OPTIONS_STORE_TTL', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | TTL configuration
    |--------------------------------------------------------------------------
    |
    | How long relying party will wait for the response from the client.
    | Null means no restrictions.
    |
    */

    'ttl' => [
        'auth' => [
            'preferred' => env('PASSKEY_TTL_AUTH_PREFERRED'), // 600000 is recommended
            'discourage' => env('PASSKEY_TTL_AUTH_DISCOURAGED'), // 180000 is recommended
        ],
        'register' => env('PASSKEY_TTL_REGISTER'), // 60 is recommended
    ],

    /*
    |--------------------------------------------------------------------------
    | Fake Device configuration
    |--------------------------------------------------------------------------
    |
    | This is experimental feature.
    |
    */

    'counter_checker' => (bool)env('PASSKEYS_COUNTER_CHECKER', false),

    /*
    |--------------------------------------------------------------------------
    | Authentication endpoint rate limits
    |--------------------------------------------------------------------------
    |
    */

    'rate_limits' => [
        'attempts' => (int)env('PASSKEYS_RATE_LIMIT_ATTEMPTS', 5),
        'decay_seconds' => (int)env('PASSKEYS_RATE_LIMIT_DECAY_SECONDS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Passkey logging
    |--------------------------------------------------------------------------
    |
    | Channel to log passkey events.
    |
    */

    'log_channel' => (string)env('PASSKEYS_LOG_CHANNEL', config('logging.default')),

    // below is an internal configuration

    'serializer' => DefaultSerializerService::class,

    'options_service' => OptionsSessionService::class,

    'policy_class' => PasskeyPolicy::class,
];
