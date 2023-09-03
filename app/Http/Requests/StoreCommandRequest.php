<?php

namespace App\Http\Requests;

use App\Models\Command;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'command' => ['required', 'string', 'alpha_num', Rule::unique(Command::class, "command")->whereNull("deleted_at"), 'max:50'],
            'response' => ['required', 'string', 'max:500'],
            'enabled' => ['boolean'],
            'cooldown' => ['numeric', 'nullable'],
            'global_cooldown' => ['numeric', 'nullable'],
            'usable_by' => ['required', 'string'],
            'type' => ['required', 'string', 'in:regular,punishable,special'],
            'severity' => ['required_if:type,punishable', 'integer', 'min:1', 'max:10'],
            'punish_reason' => ['required_if:type,punishable', 'string', 'nullable', 'max:500'],
            'action' => ['string', 'nullable', 'max:255'],
            'prepend_sender' => ['boolean'],
            'auto_post_enabled' => ['boolean'],
            'auto_post_id' => ['integer', 'exists:auto_posts,id'],
            'auto_post' => ['array', 'nullable'],
            'auto_post.interval' => ['integer', 'nullable'],
            'auto_post.interval_type' => ['string', 'nullable', 'in:seconds,minutes,hours,days'],
            'auto_post.min_posts_between' => ['integer', 'nullable'],
        ];
    }
}
