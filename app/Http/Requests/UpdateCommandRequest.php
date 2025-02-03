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
        $command = $this->route('command');

        return [
            'command' => ['string', 'alpha_num', 'max:50', Rule::unique(Command::class, 'command')->whereNull('deleted_at')->ignore($command->id)],
            'aliases' => ['array', 'nullable'],
            'aliases.*' => ['string'],
            'response' => ['string', 'max:500'],
            'enabled' => ['boolean'],
            'cooldown' => ['numeric', 'nullable'],
            'global_cooldown' => ['numeric', 'nullable'],
            'usable_by' => ['string'],
            'type' => ['string', 'in:regular,punishable,special'],
            'severity' => ['integer', 'min:1', 'max:10'],
            'punish_reason' => ['nullable', 'string', 'max:500'],
            'action' => ['nullable', 'string', 'max:255'],
            'prepend_sender' => ['boolean'],
            'auto_post_enabled' => ['boolean'],
            'auto_post_id' => ['nullable', 'required_if:auto_post_enabled,true', 'integer', 'exists:auto_posts,id'],
            'auto_post' => ['array', 'nullable', 'required_if:auto_post_enabled,true'],
            'auto_post.interval' => ['integer', 'nullable', 'required_if:auto_post_enabled,true'],
            'auto_post.interval_type' => ['string', 'nullable', 'in:seconds,minutes,hours,days', 'required_if:auto_post_enabled,true'],
            'auto_post.min_posts_between' => ['integer', 'nullable', 'required_if:auto_post_enabled,true'],
        ];
    }
}
