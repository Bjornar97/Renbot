<?php

namespace App\Http\Requests;

use App\Models\AutoPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateAutoPostRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['string', 'max:191', Rule::unique(AutoPost::class)->ignore($this->route('auto_post'))],
            'enabled' => ['boolean'],
            'interval' => ['integer'],
            'interval_type' => ['string', 'in:seconds,minutes,hours,days'],
            'min_posts_between' => ['integer'],
        ];
    }
}
