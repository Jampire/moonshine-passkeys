<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jampire\MoonshinePasskeys\Models\Passkey;
use Jampire\MoonshinePasskeys\Services\Serializers\DefaultSerializerService;
use MoonShine\Laravel\Models\MoonshineUser;
use Webauthn\PublicKeyCredentialSource;

/**
 * @extends Factory<Passkey>
 */
final class PasskeyFactory extends Factory
{
    protected $model = Passkey::class;

    public function definition(): array
    {
        $data = $this->getData();

        return [
            'personable_type' => MoonshineUser::class,
            'personable_id' => MoonshineUser::factory(),
            'name' => fake()->unique()->words(3, true),
            'credential_id' => fn (array $attrs): mixed => $attrs['data']->publicKeyCredentialId,
            'data' => $data,
            'counter' => fake()->numberBetween(int2: 100),
            'transports' => $data->transports,
        ];
    }

    private function getData(): PublicKeyCredentialSource
    {
        $value = '{"publicKeyCredentialId":"M2FiNzc4NjAtNmQ2ZC0xMWVmLTk5NTctNWMzMjU0NjE5NjY3","type":"public-key","transports":["internal","hybrid"],"attestationType":"none","trustPath":[],"aaguid":"b84e4048-15dc-4dd0-8640-f4f60813c8af","credentialPublicKey":"pQECAyYgASFYIIB5b_Zb-ilUvaV816Ido09uFsExs2Qui1g7dbUNdSPRIlggfq6xdwwBWA90EooGg_cCsTSF3-zoBDo73SsUXbpUl08","userHandle":"MQ","counter":0,"backupEligible":true,"backupStatus":true,"uvInitialized":false}';

        return app(DefaultSerializerService::class)->deserialize(
            data: $value,
            type: PublicKeyCredentialSource::class,
        );
    }
}
