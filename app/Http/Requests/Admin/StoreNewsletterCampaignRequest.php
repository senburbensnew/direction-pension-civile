<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsletterCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        $locales = config('newsletter.locales', ['fr', 'en', 'ht']);
        $groups = array_keys(config('newsletter.audience_groups', []));
        $topics = array_keys(config('newsletter.preference_topics', []));

        return [
            'subject' => ['required', 'string', 'max:255'],
            'preview_text' => ['nullable', 'string', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:120'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'body' => ['nullable', 'string'],
            'html_body' => ['nullable', 'string'],
            'audience_filter' => ['nullable', 'array'],
            'audience_filter.locales' => ['nullable', 'array'],
            'audience_filter.locales.*' => ['string', Rule::in($locales)],
            'audience_filter.audience_groups' => ['nullable', 'array'],
            'audience_filter.audience_groups.*' => ['string', Rule::in($groups)],
            'audience_filter.preferences_any' => ['nullable', 'array'],
            'audience_filter.preferences_any.*' => ['string', Rule::in($topics)],
            'open_tracking_enabled' => ['nullable', 'boolean'],
            'click_tracking_enabled' => ['nullable', 'boolean'],
            'scheduled_at' => ['nullable', 'date'],
            'action' => ['nullable', 'string', Rule::in(['save', 'send', 'schedule'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'open_tracking_enabled' => $this->boolean('open_tracking_enabled'),
            'click_tracking_enabled' => $this->boolean('click_tracking_enabled'),
        ]);
    }
}
