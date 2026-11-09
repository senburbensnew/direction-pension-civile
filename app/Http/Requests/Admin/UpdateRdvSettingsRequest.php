<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRdvSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'allow_weekends' => ['nullable', 'boolean'],
            'booking_start' => ['required', 'date_format:H:i'],
            'booking_end' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
        ];
    }
}
