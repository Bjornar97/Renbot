<?php

namespace App\Http\Requests;

use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(EventType::class)],
            'title' => ['string', 'required', 'max:255'],
            'description' => ['string', 'nullable', 'max:16000'],
            'is_teams' => ['boolean'],
            'start' => ['date', 'required'],
            'end' => ['date', 'required'],
        ];
    }
}
