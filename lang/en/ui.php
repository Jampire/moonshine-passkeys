<?php

declare(strict_types=1);

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */

return [
    'block' => 'Passkeys',
    'activate' => 'Activate',
    'deactivate' => 'Deactivate',
    'passkey-container' => [
        'new-passkey-name-label' => 'Name',
        'new-passkey-name-placeholder' => 'My iPhone, Home PC, etc.',
        'new-passkey-name-button' => 'Create',
        'table' => [
            'headers' => [
                'name' => 'Name',
                'updated-at' => 'Last Used At',
            ],
        ],
        'modal' => [
            'title' => 'Confirmation',
            'content' => 'Are you sure you want to delete this passkey?',
            'button' => 'Delete',
        ],
    ],
];
