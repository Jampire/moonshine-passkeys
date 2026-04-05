<?php

declare(strict_types=1);

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */

return [
    'common' => [
        'not-configured' => 'Пакет Passkeys не наладжаны. Калі ласка, паведаміце вашаму адміністратару.',
        'operation-failed' => 'Аперацыя не ўдалася.',
        'passkey-not-deleted' => 'Ключ доступу не быў выдалены.',
    ],
    'name-already-exists' => 'Гэтае імя ўжо занята.',
    'not-created' => 'Ключ доступу не быў створаны.',
    'registration-failed' => 'Рэгістрацыя не ўдалася.',
    'authentication-failed' => 'Аўтэнтыфікацыя не ўдалася.',
    'not-allowed' => 'Аперацыя адменена або час скончыўся.',
    'invalid-state' => 'Аўтэнтыфікатар ужо зарэгістраваны.',
    'not-supported' => 'У гэтым браўзэры не падтрымліваецца WebAuthn.',
    'aborted' => 'Аперацыя была спынена.',
    'not-found' => 'Ключ доступу не знойдзены.',
    'exceptions' => [
        'prefix' => 'Выключэнне ключа доступу: :msg.',
        'related-model' => 'Мадэль :user павінна рэалізоўваць кантракт :contract.',
        'attestation' => 'Адказ аўтэнтыфікатара несапраўдны.',
        'assertion' => 'Адказ сцвярджэння несапраўдны.',
        'invalid-passkey' => 'Наданы ключ доступу несапраўдны.',
        'fake-device' => 'Выяўлена падробленая прылада.',
        'rate-limit-exceeded' => 'Занадта шмат спроб уваходу. Калі ласка, паспрабуйце яшчэ раз праз :seconds секунд.',
    ],
    'options' => [
        'register' => 'Рэгістрацыя параметраў не ўдалася.',
        'authenticate' => 'Аўтэнтыфікацыя параметраў не ўдалася.',
    ],
];
