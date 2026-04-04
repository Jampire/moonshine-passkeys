<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Services\Serializers;

use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class DefaultSerializerService extends AbstractSerializer
{
    public function __construct()
    {
        $supportManager = AttestationStatementSupportManager::create();
        $supportManager->add(NoneAttestationStatementSupport::create());

        $this->serializer = (new WebauthnSerializerFactory($supportManager))->create();
    }
}
