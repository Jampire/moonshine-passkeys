<?php

declare(strict_types=1);

use Jampire\MoonshinePasskeys\Services\Serializers\DefaultSerializerService;
use Webauthn\PublicKeyCredentialSource;

describe('DefaultSerializerService', function (): void {
    it('can serialize a PublicKeyCredentialSource to JSON', function (): void {
        $service = new DefaultSerializerService();

        $json = '{"publicKeyCredentialId":"M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3","type":"public-key","transports":["internal","hybrid"],"attestationType":"none","trustPath":[],"aaguid":"b84e4048-15dc-4dd0-8640-f4f60813c8af","credentialPublicKey":"pQECAyYgASFYIIB5b_Zb-ilUvaV816Ido09uFsExs2Qui1g7dbUNdSPRIlggfq6xdwwBWA90EooGg_cCsTSF3-zoBDo73SsUXbpUl08","userHandle":"MQ","counter":0,"backupEligible":true,"backupStatus":true,"uvInitialized":false}';

        $source = $service->deserialize($json, PublicKeyCredentialSource::class);
        $serialized = $service->serialize($source);

        expect($serialized)->toBeString()
            ->and(json_decode($serialized, true))->toBeArray();
    });

    it('can deserialize a JSON string to PublicKeyCredentialSource', function (): void {
        $service = new DefaultSerializerService();

        $json = '{"publicKeyCredentialId":"M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3","type":"public-key","transports":["internal","hybrid"],"attestationType":"none","trustPath":[],"aaguid":"b84e4048-15dc-4dd0-8640-f4f60813c8af","credentialPublicKey":"pQECAyYgASFYIIB5b_Zb-ilUvaV816Ido09uFsExs2Qui1g7dbUNdSPRIlggfq6xdwwBWA90EooGg_cCsTSF3-zoBDo73SsUXbpUl08","userHandle":"MQ","counter":0,"backupEligible":true,"backupStatus":true,"uvInitialized":false}';

        $source = $service->deserialize($json, PublicKeyCredentialSource::class);

        expect($source)->toBeInstanceOf(PublicKeyCredentialSource::class)
            ->and($source->counter)->toBe(0)
            ->and($source->transports)->toContain('internal');
    });

    it('round-trips serialize and deserialize correctly', function (): void {
        $service = new DefaultSerializerService();

        $json = '{"publicKeyCredentialId":"M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3","type":"public-key","transports":["internal","hybrid"],"attestationType":"none","trustPath":[],"aaguid":"b84e4048-15dc-4dd0-8640-f4f60813c8af","credentialPublicKey":"pQECAyYgASFYIIB5b_Zb-ilUvaV816Ido09uFsExs2Qui1g7dbUNdSPRIlggfq6xdwwBWA90EooGg_cCsTSF3-zoBDo73SsUXbpUl08","userHandle":"MQ","counter":0,"backupEligible":true,"backupStatus":true,"uvInitialized":false}';

        $source = $service->deserialize($json, PublicKeyCredentialSource::class);
        $serialized = $service->serialize($source);
        $deserialized = $service->deserialize($serialized, PublicKeyCredentialSource::class);

        expect($deserialized)->toBeInstanceOf(PublicKeyCredentialSource::class)
            ->and($deserialized->counter)->toBe($source->counter);
    });
});
