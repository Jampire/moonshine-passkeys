<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Data\UpdatePasskeyData;
use Jampire\MoonshinePasskeys\Http\Requests\AuthenticatePasskeyRequest;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;

uses()->group('data');

describe('UpdatePasskeyData', function (): void {
    function getUpdateData(bool $isOpt = true): ?UpdatePasskeyData
    {
        if ($isOpt) {
            storeFakeAuthOptions();
        }

        $request = new AuthenticatePasskeyRequest();
        $request->setMethod('POST');
        $request->merge([
            'answer' => fakeAssertionJson(),
            'remember' => false,
        ]);
        $request->setContainer(app());
        $request->validateResolved();

        return UpdatePasskeyData::fromRequest($request);
    }

    beforeEach(function (): void {
        config([
            'passkeys.enabled' => true,
            'passkeys.rp_id' => 'localhost',
            'passkeys.debug' => false,
        ]);

        Session::flush();
    });

    test('fromRequest returns null when no options in session', function (): void {
        $result = getUpdateData(isOpt: false);

        expect($result)
            ->toBeNull();
    });

    it('fromRequest returns null when passkey response is assertion type', function (): void {
        storeFakeRegOptions();

        // Build a valid assertion-type (webauthn.get) JSON.
        // After deserialization, PublicKeyCredential->response will be AuthenticatorAssertionResponse,
        // which causes CreatePasskeyData::__construct() to throw (caught by fromRequest's catch block).
        $rpIdHash = hash('sha256', 'localhost', true);
        $authData = $rpIdHash . chr(0x01) . pack('N', 1); // UP flag only, no AT
        $clientData = json_encode([
            'type' => 'webauthn.get',
            'challenge' => 'AAAAAAAAAAAAAAAAAAAAAA',
            'origin' => 'https://localhost',
        ]);
        $credB64 = 'M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3';
        $assertionJson = json_encode([
            'id' => $credB64,
            'rawId' => $credB64,
            'response' => [
                'clientDataJSON' => rtrim(strtr(base64_encode($clientData), '+/', '-_'), '='),
                'authenticatorData' => base64_encode($authData),
                'signature' => base64_encode('fakesig'),
                'userHandle' => base64_encode('1'),
            ],
            'type' => 'public-key',
        ]);

        $request = new AuthenticatePasskeyRequest();
        $request->setMethod('POST');
        $request->merge([
            'answer' => fakeAssertionJson(),
            'remember' => false,
        ]);
        $request->setContainer(app());
        $request->validateResolved();

        $data = UpdatePasskeyData::fromRequest($request);

        expect($data)
            ->toBeNull();
    });

    it('covers fromRequest success path with valid data', function (): void {
        $data = getUpdateData();

        expect($data)
            ->toBeInstanceOf(UpdatePasskeyData::class)
            ->and($data->assertionResponse)
            ->toBeInstanceOf(AuthenticatorAssertionResponse::class)
            ->and($data->authOptions)
            ->toBeInstanceOf(PublicKeyCredentialRequestOptions::class)
            ->and($data->publicKeyCredential)
            ->toBeInstanceOf(PublicKeyCredential::class);
    });
});
