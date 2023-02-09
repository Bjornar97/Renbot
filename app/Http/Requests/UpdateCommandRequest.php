<?php

namespace App\Http\Requests;

use App\Models\Command;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommandRequest extends FormRequest
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
        $command = $this->route("command");

        return [
            'command' => ['string', Rule::unique(Command::class, "command")->ignore($command->id)],
            'response' => ['string'],
            'enabled' => ['boolean'],
            'cooldown' => ['numeric', 'nullable'],
            'global_cooldown' => ['numeric', 'nullable'],
            'usable_by' => ['string'],
        ];
    }
}
