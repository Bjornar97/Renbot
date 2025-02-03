<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPasskeyRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'type' => ['required', 'string'],
            'rawId' => ['required', 'string'],
            'response' => ['required', 'array'],
            'response.clientDataJSON' => ['required', 'string'],
            'response.attestationObject' => ['required_without:response.authenticatorData', 'string'],
            'response.authenticatorData' => ['required_without:response.attestationObject', 'string'],
            'reponse.signature' => ['required_with:authenticatorData', 'string'],
            'response.userHandle' => ['required_with:authenticatorData', 'string'],
            'response.authenticatorAttachment' => ['string', 'nullable'],
        ];
    }
}
