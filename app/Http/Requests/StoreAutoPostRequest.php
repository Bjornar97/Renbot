<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutoPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['string', 'required', 'max:191', 'unique:auto_posts,title'],
            'interval' => ['integer', 'nullable'],
            'interval_type' => ['string', 'nullable', 'in:seconds,minutes,hours,days'],
            'min_posts_between' => ['integer', 'nullable'],
        ];
    }
}
