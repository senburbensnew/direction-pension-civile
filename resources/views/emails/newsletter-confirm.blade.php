<x-mail::message>
# {{ __('newsletter.confirm_email_heading') }}

{{ __('newsletter.confirm_email_body') }}

<x-mail::button :url="$confirmUrl">
{{ __('newsletter.confirm_email_button') }}
</x-mail::button>

{{ __('newsletter.confirm_email_expiry') }}

{{ __('newsletter.confirm_email_ignore') }}
</x-mail::message>
