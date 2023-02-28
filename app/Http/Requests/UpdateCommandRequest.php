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
            'command' => ['string', 'alpha_num', 'max:50', Rule::unique(Command::class, "command")->whereNull("deleted_at")->ignore($command->id)],
            'response' => ['string', 'max:500'],
            'enabled' => ['boolean'],
            'cooldown' => ['numeric', 'nullable'],
            'global_cooldown' => ['numeric', 'nullable'],
            'usable_by' => ['string'],
            'type' => ['string', 'in:regular,punishable,special'],
            'severity' => ['integer', 'min:1', 'max:10'],
            'punish_reason' => ['nullable', 'string', 'max:500'],
            'action' => ['nullable', 'string', 'max:255'],
        ];
    }
}
