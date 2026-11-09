@php
    /** @var \App\Models\User|null $user */
    $user = $user ?? null;
    $passwordRequired = (bool) ($passwordRequired ?? false);
    $usernameFormatMessage = __('messages.admin_user_username_format');
@endphp

<div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
            Adresse e-mail <span class="text-red-500">*</span>
        </label>
        <input
            type="email"
            name="email"
            value="{{ old('email', $user?->email) }}"
            required
            maxlength="255"
            class="{{ $field('email') }}"
            placeholder="jean.dupont@example.com"
            autocomplete="email"
        >
        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Nom d’utilisateur</label>
        <input
            type="text"
            name="username"
            value="{{ old('username', $user?->username) }}"
            class="{{ $field('username') }}"
            placeholder="jean.dupont"
            autocomplete="username"
            minlength="3"
            maxlength="50"
            pattern="[A-Za-z0-9._-]+"
            title="{{ $usernameFormatMessage }}"
        >
        <p class="text-xs text-gray-400 mt-1">
            @if($user === null)
                Laissé vide, il sera généré à partir de l’e-mail.
            @endif
            {{ $usernameFormatMessage }}
        </p>
        @error('username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <x-telephone-input
            name="phone"
            :value="$user?->phone ?? ''"
            label="Téléphone"
            :required="true"
            label-class="block text-xs font-medium text-gray-600 mb-1"
            hint-class="text-xs text-gray-500 mt-1"
            error-class="text-red-500 text-xs mt-1"
            client-error-class="text-red-500 text-xs mt-1"
            :input-class="$field('phone')"
        />
    </div>
    <div></div>
    <x-admin-user-password-fields
        :password-required="$passwordRequired"
        :password-label="$passwordRequired ? __('messages.password') : 'Nouveau mot de passe'"
        :password-optional-hint="$passwordRequired ? null : '(laisser vide pour ne pas changer)'"
        :password-input-class="$field('password')"
    />
</div>
