<?php

namespace App\Http\Requests;

use App\Enums\ChurchRole;
use App\Rules\WhatsAppNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $clean = fn (mixed $values): array => collect(is_array($values) ? $values : [])
            ->map(fn (mixed $value) => trim((string) $value))
            ->filter()
            ->unique(fn (string $value) => mb_strtolower($value))
            ->values()
            ->all();

        $roles = $this->input('church_roles', []);

        $this->merge([
            'secondary_profiles' => $clean($this->input('secondary_profiles')),
            // Free-text roles only count when "Autre" is ticked.
            'other_roles' => in_array(ChurchRole::Autre->value, (array) $roles, true) ? $clean($this->input('other_roles')) : [],
            'church_roles' => $roles,
            'is_tegv_christian' => $this->boolean('is_tegv_christian'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'primary_profile' => ['required', 'string', 'max:100'],
            'secondary_profiles' => ['array', 'max:5'],
            'secondary_profiles.*' => ['string', 'max:100'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:70'],
            'company' => ['nullable', 'string', 'max:120'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'birth_day' => ['nullable', 'integer', 'min:1', 'max:31', 'required_with:birth_month'],
            'birth_month' => ['nullable', 'integer', 'min:1', 'max:12', 'required_with:birth_day'],
            'church_roles' => ['array'],
            'church_roles.*' => [Rule::enum(ChurchRole::class)],
            'other_roles' => ['array', 'max:5', Rule::requiredIf(in_array(ChurchRole::Autre->value, $this->input('church_roles', []), true))],
            'other_roles.*' => ['string', 'max:60'],
            'whatsapp' => ['required', 'string', 'max:25', new WhatsAppNumber],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'is_tegv_christian' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'other_roles.required' => 'Vous avez choisi « Autre » : écrivez au moins un rôle puis touchez « Ajouter ».',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $day = $this->integer('birth_day');
                $month = $this->integer('birth_month');

                // 2024 is a leap year, so 29 February stays valid.
                if ($day && $month && ! checkdate($month, $day, 2024)) {
                    $validator->errors()->add('birth_day', "Ce jour n'existe pas dans le mois choisi.");
                }
            },
        ];
    }
}
