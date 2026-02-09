<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Exceptions\PasskeyException;
use Jampire\MoonshinePasskeys\Services\AuthenticatorCounterChecker;
use Jampire\MoonshinePasskeys\Services\Serializers\DefaultSerializerService;
use Webauthn\PublicKeyCredentialSource;

describe('AuthenticatorCounterChecker', function (): void {
    it('can be created via make()', function (): void {
        $checker = AuthenticatorCounterChecker::make();

        expect($checker)->toBeInstanceOf(AuthenticatorCounterChecker::class);
    });

    it('passes when currentCounter is greater than source counter', function (): void {
        $serializer = new DefaultSerializerService();
        $source = $serializer->deserialize(
            '{"publicKeyCredentialId":"M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3","type":"public-key","transports":["internal","hybrid"],"attestationType":"none","trustPath":[],"aaguid":"b84e4048-15dc-4dd0-8640-f4f60813c8af","credentialPublicKey":"pQECAyYgASFYIIB5b_Zb-ilUvaV816Ido09uFsExs2Qui1g7dbUNdSPRIlggfq6xdwwBWA90EooGg_cCsTSF3-zoBDo73SsUXbpUl08","userHandle":"MQ","counter":5,"backupEligible":true,"backupStatus":true,"uvInitialized":false}',
            PublicKeyCredentialSource::class,
        );

        $checker = AuthenticatorCounterChecker::make();

        // Should not throw — currentCounter (10) > source counter (5)
        expect(fn () => $checker->check($source, 10))->not->toThrow(PasskeyException::class);
    });

    it('throws PasskeyException when currentCounter is equal to source counter', function (): void {
        $serializer = new DefaultSerializerService();
        $source = $serializer->deserialize(
            '{"publicKeyCredentialId":"M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3","type":"public-key","transports":["internal","hybrid"],"attestationType":"none","trustPath":[],"aaguid":"b84e4048-15dc-4dd0-8640-f4f60813c8af","credentialPublicKey":"pQECAyYgASFYIIB5b_Zb-ilUvaV816Ido09uFsExs2Qui1g7dbUNdSPRIlggfq6xdwwBWA90EooGg_cCsTSF3-zoBDo73SsUXbpUl08","userHandle":"MQ","counter":5,"backupEligible":true,"backupStatus":true,"uvInitialized":false}',
            PublicKeyCredentialSource::class,
        );

        $checker = AuthenticatorCounterChecker::make();

        expect(fn () => $checker->check($source, 5))->toThrow(PasskeyException::class);
    });

    it('throws PasskeyException when currentCounter is less than source counter', function (): void {
        $serializer = new DefaultSerializerService();
        $source = $serializer->deserialize(
            '{"publicKeyCredentialId":"M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3","type":"public-key","transports":["internal","hybrid"],"attestationType":"none","trustPath":[],"aaguid":"b84e4048-15dc-4dd0-8640-f4f60813c8af","credentialPublicKey":"pQECAyYgASFYIIB5b_Zb-ilUvaV816Ido09uFsExs2Qui1g7dbUNdSPRIlggfq6xdwwBWA90EooGg_cCsTSF3-zoBDo73SsUXbpUl08","userHandle":"MQ","counter":5,"backupEligible":true,"backupStatus":true,"uvInitialized":false}',
            PublicKeyCredentialSource::class,
        );

        $checker = AuthenticatorCounterChecker::make();

        expect(fn () => $checker->check($source, 3))->toThrow(PasskeyException::class);
    });
});
