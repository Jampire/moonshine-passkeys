<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class AuthenticateOptionsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['sometimes', 'email'],
        ];
    }
}
