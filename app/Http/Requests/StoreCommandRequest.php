<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'command' => ['required', 'string', 'alpha_num', 'unique:commands,command', 'max:50'],
            'response' => ['required', 'string', 'max:500'],
            'enabled' => ['boolean'],
            'cooldown' => ['numeric', 'nullable'],
            'global_cooldown' => ['numeric', 'nullable'],
            'usable_by' => ['required', 'string'],
            'type' => ['required', 'string', 'in:regular,punishable,special'],
            'severity' => ['required_if:type,punishable', 'integer', 'min:1', 'max:10'],
            'punish_reason' => ['required_if:type,punishable', 'string', 'nullable', 'max:500'],
            'action' => ['string', 'nullable', 'max:255'],
        ];
    }
}
