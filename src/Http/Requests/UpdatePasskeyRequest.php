<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Jampire\MoonshinePasskeys\Http\Concerns\HasNameRule;
use Jampire\MoonshinePasskeys\Http\Concerns\HasUser;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class UpdatePasskeyRequest extends FormRequest
{
    use HasUser;
    use HasNameRule;

    // TODO: return Response here and in all other requests?
    public function authorize(): bool
    {
        return $this->getMoonShineUser()?->can('update', $this->route('passkey')) ?? false;
    }

    /**
     * @return array<string, mixed[]>
     */
    public function rules(): array
    {
        return [
            'name' => $this->getNameRule($this->getMoonShineUser(), $this->route('passkey')),
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
