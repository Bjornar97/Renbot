<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateBotSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('moderate');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'announceRestart' => ['boolean'],
            'punishableBansEnabled' => ['boolean'],
            'punishableTimeoutsEnabled' => ['boolean'],
            'punishDebugEnabled' => ['boolean'],
            'autoCapsEnabled' => ['boolean'],
            'autoBanBots' => ['boolean'],
            'autoCapsCommand' => ['nullable', 'integer', 'exists:commands,id', 'required_if:autoCapsEnabled,true'],
            'autoCapsTotalCapsThreshold' => ['numeric', 'min:0', 'max:1'],
            'autoCapsTotalLengthThreshold' => ['integer', 'min:0'],
            'autoCapsWordCapsThreshold' => ['numeric', 'min:0', 'max:1'],
            'autoCapsWordLengthThreshold' => ['integer', 'min:0'],

            'autoMaxEmotesEnabled' => ['boolean'],
            'autoMaxEmotes' => ['integer', 'min:0', 'max:30'],
            'autoMaxEmotesCommand' => ['nullable', 'integer', 'exists:commands,id', 'required_if:autoMaxEmotesEnabled,true'],
        ];
    }
}
