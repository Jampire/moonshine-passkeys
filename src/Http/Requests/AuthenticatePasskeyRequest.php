<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use MoonShine\Laravel\MoonShineAuth;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class AuthenticatePasskeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return MoonShineAuth::getGuard()->guest();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'answer' => ['required', 'json'],
            'remember' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'remember' => (bool)$this->input('remember', false),
        ]);
    }
}
