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
            'command' => ['required', 'string', 'unique:commands,command'],
            'response' => ['required', 'string'],
            'enabled' => ['boolean'],
            'cooldown' => ['numeric', 'nullable'],
            'global_cooldown' => ['numeric', 'nullable'],
            'usable_by' => ['string'],
        ];
    }
}
