<?php

declare(strict_types=1);

use CBOR\ByteStringObject;
use CBOR\MapObject;
use CBOR\NegativeIntegerObject;
use CBOR\TextStringObject;
use CBOR\UnsignedIntegerObject;
use Illuminate\Foundation\Application;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Services\Options\OptionsSessionService;
use Jampire\MoonshinePasskeys\Services\Serializers\Contracts\SerializerContract;
use Jampire\MoonshinePasskeys\Tests\Fixtures\Admin;
use Jampire\MoonshinePasskeys\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function adminWithPasskey(bool $isActive = true): Admin
{
    /** @var Admin $user */
    $user = Admin::factory()->create();
    $user->passkeyMeta()->update([
        'is_active' => $isActive,
    ]);
    Passkey::factory()->for($user, 'personable')->create();

    return $user->refresh();
}

function controllerBind(Application $app, string $controllerClass, Actionable $action, ?string $need = null): void
{
    $app->when($controllerClass)
        ->needs($need ?? Actionable::class)
        ->give(fn (): Actionable => $action);
}

/**
 * Build a fake but structurally valid WebAuthn attestation JSON (registration response).
 * The credentials are fake so validator.check() will throw, but deserialization succeeds.
 */
function passkeyJson(): string
{
    $rpId = config('passkeys.rp_id');
    // Same credential ID used in PasskeyFactory
    $credentialIdB64 = 'M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3';
    $credentialId = base64_decode($credentialIdB64);

    // Minimal COSE EC2/P-256 public key — must be a CBOR MapObject for webauthn-lib to accept it
    $coseKey = MapObject::create()
        ->add(UnsignedIntegerObject::create(1), UnsignedIntegerObject::create(2)) // kty: EC2
        ->add(UnsignedIntegerObject::create(3), NegativeIntegerObject::create(-7)) // alg: ES256
        ->add(NegativeIntegerObject::create(-1), UnsignedIntegerObject::create(1)) // crv: P-256
        ->add(NegativeIntegerObject::create(-2), ByteStringObject::create(str_repeat("\x00", 32)))  // x: 32 bytes
        ->add(NegativeIntegerObject::create(-3), ByteStringObject::create(str_repeat("\x00", 32))); // y: 32 bytes

    // authData: rpIdHash(32) + flags(1) + signCount(4) + AAGUID(16) + credIdLen(2) + credId + coseKey
    $rpIdHash  = hash('sha256', (string) $rpId, true);
    $flags     = chr(0x41);                        // UP(0x01) | AT(0x40) — attested credential data present
    $signCount = pack('N', 0);
    $aaguid    = str_repeat("\x00", 16);
    $credIdLen = pack('n', strlen($credentialId)); // big-endian uint16
    $authData  = $rpIdHash . $flags . $signCount . $aaguid . $credIdLen . $credentialId . $coseKey;

    // CBOR attestation object: {fmt: "none", attStmt: {}, authData: <bytes>}
    $attestationObject = MapObject::create()
        ->add(TextStringObject::create('fmt'), TextStringObject::create('none'))
        ->add(TextStringObject::create('attStmt'), MapObject::create())
        ->add(TextStringObject::create('authData'), ByteStringObject::create($authData));

    $clientData = json_encode([
        'type'      => 'webauthn.create',
        'challenge' => 'AAAAAAAAAAAAAAAAAAAAAA',
        'origin'    => 'https://' . $rpId,
    ]);

    return json_encode([
        'id'       => $credentialIdB64,
        'rawId'    => $credentialIdB64,
        'response' => [
            'clientDataJSON'    => rtrim(strtr(base64_encode($clientData), '+/', '-_'), '='),
            'attestationObject' => base64_encode((string) $attestationObject),
        ],
        'type' => 'public-key',
    ]);
}

/**
 * Build a fake-but-validly-structured WebAuthn assertion JSON.
 * The signature is fake so validator.check() will throw, but deserialization succeeds.
 */
function fakeAssertionJson(string $rpId = 'localhost'): string
{
    $rpIdHash = hash('sha256', $rpId, true); // 32 bytes
    $flags = chr(0x01); // UP flag
    $signCount = pack('N', 1); // 4 bytes big-endian
    $authData = $rpIdHash . $flags . $signCount; // 37 bytes

    $clientData = json_encode([
        'type' => 'webauthn.get',
        'challenge' => 'AAAAAAAAAAAAAAAAAAAAAA',
        'origin' => 'https://' . $rpId,
    ]);

    // The factory credential ID: M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3
    // This is the same in base64url and base64 (no - or _ chars in this string)
    $credentialIdB64 = 'M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3';

    return json_encode([
        'id' => $credentialIdB64,
        'rawId' => $credentialIdB64,
        'response' => [
            'clientDataJSON' => rtrim(strtr(base64_encode($clientData), '+/', '-_'), '='),
            'authenticatorData' => base64_encode($authData),
            'signature' => base64_encode('fakesignature'),
            'userHandle' => base64_encode('1'),
        ],
        'type' => 'public-key',
    ]);
}

/**
 * Store fake authentication options in session so UpdatePasskeyData::fromRequest() can proceed.
 */
function storeFakeAuthOptions(): void
{
    $serializer = app(SerializerContract::class);

    $options = new PublicKeyCredentialRequestOptions(
        challenge: 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
        rpId: config('passkeys.rp_id'),
        allowCredentials: [],
    );

    $store = new OptionsSessionService();
    $store->saveAuthenticationOptions($serializer->serialize($options));
}

/**
 * Store fake registration options in session so CreatePasskeyData::fromRequest() can proceed.
 */
function storeFakeRegOptions(): void
{
    $serializer = app(SerializerContract::class);

    $options = new PublicKeyCredentialCreationOptions(
        rp: new PublicKeyCredentialRpEntity(name: 'Test', id: config('passkeys.rp_id')),
        user: new PublicKeyCredentialUserEntity(name: 'test', id: '1', displayName: 'Test User'),
        challenge: 'AAAAAAAAAAAAAAAAAAAAAA',
    );

    $store = new OptionsSessionService();
    $store->saveRegistrationOptions($serializer->serialize($options));
}
