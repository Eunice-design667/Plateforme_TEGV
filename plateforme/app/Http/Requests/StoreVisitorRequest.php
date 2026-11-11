<?php

namespace App\Http\Requests;

use App\Rules\WhatsAppNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisitorRequest extends FormRequest
{
    /**
     * The visitor form is public: anyone who scans the QR code may fill it in.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['has_no_church' => $this->boolean('has_no_church')]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'has_no_church' => ['boolean'],
            'origin_church' => ['nullable', 'required_unless:has_no_church,true', 'string', 'max:150'],
            'whatsapp' => ['required', 'string', 'max:25', new WhatsAppNumber],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ];
    }
}
