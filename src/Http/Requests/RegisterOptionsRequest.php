<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Jampire\MoonshinePasskeys\Http\Concerns\HasNameRule;
use Jampire\MoonshinePasskeys\Http\Concerns\HasUser;
use Jampire\MoonshinePasskeys\Models\Passkey;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class RegisterOptionsRequest extends FormRequest
{
    use HasUser;
    use HasNameRule;

    public function authorize(): bool
    {
        return $this->getMoonShineUser()?->can('create', Passkey::class) ?? false;
    }

    /**
     * @return array<string, mixed[]>
     */
    public function rules(): array
    {
        return [
            'name' => $this->getNameRule($this->getMoonShineUser()),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name' => $this->getNameMessage(),
        ];
    }
}
