<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\File;

class UpdateCreatorRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'youtube_url' => ['nullable', 'string', 'max:191'],
            'twitch_url' => ['nullable', 'string', 'max:191'],
            'x_url' => ['nullable', 'string', 'max:191'],
            'image' => ['nullable', File::image()->max('4mb')],
        ];
    }
}
