<?php

declare(strict_types=1);

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */

return [
    'common' => [
        'not-configured' => 'Пакет Passkeys не настроен. Пожалуйста сообщите вашему администратору.',
        'operation-failed' => 'Операция не удалась.',
        'passkey-not-deleted' => 'Ключ доступа не был удален.',
    ],
    'name-already-exists' => 'Это имя уже занято.',
    'not-created' => 'Ключ доступа не был создан.',
    'registration-failed' => 'Регистрация не удалась.',
    'authentication-failed' => 'Аутентификация не удалась.',
    'not-allowed' => 'Операция отменена или время истекло.',
    'invalid-state' => 'Аутентификатор уже зарегистрирован.',
    'not-supported' => 'В этом браузере не поддерживается WebAuthn.',
    'aborted' => 'Операция была прервана.',
    'not-found' => 'Ключ доступа не найден.',
    'exceptions' => [
        'prefix' => 'Исключение ключа доступа: :msg.',
        'related-model' => 'Модель :user должна реализовывать контракт :contract.',
        'attestation' => 'Ответ аутентификатора недействителен.',
        'assertion' => 'Ответ утверждения недействителен.',
        'invalid-passkey' => 'Предоставленный ключ доступа недействителен.',
        'fake-device' => 'Обнаружено поддельное устройство.',
        'rate-limit-exceeded' => 'Слишком много попыток входа. Пожалуйста, попробуйте еще раз через :seconds секунд.',
    ],
    'options' => [
        'register' => 'Регистрация параметров не удалась.',
        'authenticate' => 'Аутентификация параметров не удалась.',
    ],
];
