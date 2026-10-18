<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewsletterSubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $locales = config('newsletter.locales', ['fr', 'en', 'ht']);
        $groups = array_keys(config('newsletter.audience_groups', []));

        return [
            'email' => ['required', 'email', 'max:255'],
            'locale' => ['nullable', 'string', Rule::in($locales)],
            'audience_group' => ['nullable', 'string', Rule::in($groups)],
            'preferences' => ['nullable', 'array'],
            'preferences.*' => ['string', Rule::in(array_keys(config('newsletter.preference_topics', [])))],
        ];
    }
}
