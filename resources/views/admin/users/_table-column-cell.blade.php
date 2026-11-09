@php
    /** @var \App\Models\User $user */
    /** @var \App\Services\Admin\AdminUserColumnCatalog $columnCatalog */
    $columnKey = $columnKey ?? '';
@endphp

@switch($columnKey)
    @case('user_summary')
        <td class="px-4 py-3">
            <div class="flex items-center gap-3">
                @if($user->profile_photo)
                    <img src="{{ asset('storage/' . $user->profile_photo) }}"
                        class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                @else
                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-indigo-600 text-xs font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                @endif
                <div>
                    <p class="font-medium text-gray-800">{{ $user->name }}</p>
                    <p class="text-xs text-gray-400">{{ $user->email ?: '—' }}</p>
                </div>
            </div>
        </td>
        @break

    @case('roles')
        <td class="px-4 py-3">
            <div class="flex flex-wrap gap-1">
                @forelse($user->roles as $role)
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $role->name === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $role->name }}
                    </span>
                @empty
                    <span class="text-gray-400 text-xs">—</span>
                @endforelse
            </div>
        </td>
        @break

    @case('status_label')
        @php
            $status = $user->resolvedAccountStatus();
        @endphp
        <td class="px-4 py-3 text-center">
            <span @class([
                'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                'bg-green-100 text-green-700' => $status->isActif(),
                'bg-amber-100 text-amber-800' => $status->isProvisionnel(),
                'bg-red-100 text-red-700' => $status === \App\Enums\AccountStatusEnum::BLOQUE,
                'bg-gray-100 text-gray-500' => $status === \App\Enums\AccountStatusEnum::DESACTIVE,
            ])>
                {{ $status->label() }}
            </span>
        </td>
        @break

    @case('primary_category')
        @php
            $primaryCategoryCode = $user->primaryCategoryCode();
        @endphp
        <td class="px-4 py-3 text-gray-600">
            {{ $primaryCategoryCode ? ucfirst(str_replace('_', ' ', $primaryCategoryCode)) : '—' }}
        </td>
        @break

    @default
        <td @class([
            'px-4 py-3 text-gray-600',
            'text-center' => $columnKey === 'must_change_password',
        ])>
            {{ $columnCatalog->displayValue($user, $columnKey) }}
        </td>
@endswitch
