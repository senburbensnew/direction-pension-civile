<x-mail::message>
# {{ __('messages.login_two_factor_email_heading') }}

{{ __('messages.login_two_factor_email_intro') }}

<x-mail::panel>
**{{ $code }}**
</x-mail::panel>

{{ __('messages.login_two_factor_email_hint') }}

{{ __('messages.login_two_factor_email_ignore') }}
</x-mail::message>
