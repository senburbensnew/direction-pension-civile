<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserTypeEnum;
use App\Helpers\RegexExpressions;
use App\Http\Controllers\Controller;
use App\Models\AffiliationPension;
use App\Models\User;
use App\Models\UserCategory;
use App\Rules\Nif;
use App\Services\Account\InstitutionAccountService;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\Pension\BeneficiaireProvisioningService;
use App\Support\Storage\UploadPaths;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /** @var list<string> */
    private const REGISTER_CATEGORY_CODES = [
        UserTypeEnum::PENSIONNE->value,
        UserTypeEnum::FONCTIONNAIRE->value,
        UserTypeEnum::INSTITUTION->value,
    ];

    public function create(): View
    {
        $accountCategories = UserCategory::query()
            ->whereIn('code', self::REGISTER_CATEGORY_CODES)
            ->orderBy('label')
            ->get();

        return view('auth.register', compact('accountCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = [
            'name' => 'nom d\'utilisateur',
            'firstname' => 'prénom',
            'lastname' => 'nom de famille',
            'email' => 'adresse e-mail',
            'username' => 'nom d\'utilisateur',
            'password' => 'mot de passe',
            'nif' => 'NIF',
            'pension_code' => 'code pension',
            'primary_category' => 'type d\'utilisateur',
            'profile_photo' => 'photo profil',
        ];

        $messages = [
            'required' => 'Le champ ":attribute" est obligatoire.',
            'nif.regex' => 'Le NIF doit suivre le format 000-000-000-0 (ex: 123-456-789-0).',
            'pension_code.regex' => 'Le code pension doit être au format ABC-123456 (ex: PEN-123456).',
        ];

        $categoryCode = (string) $request->input('primary_category');

        $rules = [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:'.User::class],
            'username' => ['nullable', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nif' => [
                'required',
                new Nif,
                'unique:'.User::class,
            ],
            'primary_category' => ['required', 'string', Rule::in(self::REGISTER_CATEGORY_CODES)],
            'pension_code' => [
                'nullable',
                'string',
                'max:255',
                'regex:'.RegexExpressions::pensionCode(),
            ],
        ];

        if ($categoryCode === UserTypeEnum::PENSIONNE->value) {
            $rules['pension_code'] = [
                'required',
                'string',
                'max:255',
                'regex:'.RegexExpressions::pensionCode(),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (AffiliationPension::codeExists((string) $value)) {
                        $fail('Ce code pension est déjà utilisé.');
                    }
                },
            ];
        }

        if ($categoryCode === UserTypeEnum::INSTITUTION->value) {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                'unique:'.User::class,
            ];

            $rules['firstname'] = ['nullable', 'string', 'max:255'];
            $rules['lastname'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules, $messages, $attributes);

        $profilePhotoPath = $request->hasFile('profile_photo')
            ? $request->file('profile_photo')->store(
                UploadPaths::userProfilePhoto(),
                UploadPaths::publicDisk()
            )
            : null;

        $categorySync = app(UserCategoryRoleSyncService::class);

        if ($categoryCode === UserTypeEnum::INSTITUTION->value) {
            $user = app(InstitutionAccountService::class)->createWithLogin(
                [
                    'raison_sociale' => $validated['name'],
                    'nif' => $validated['nif'],
                ],
                [
                    'name' => $validated['name'],
                    'lastname' => $validated['lastname'] ?? null,
                    'firstname' => $validated['firstname'] ?? null,
                    'email' => $validated['email'],
                    'username' => $validated['username'] ?? null,
                    'password' => Hash::make($validated['password']),
                    'nif' => $validated['nif'],
                    'profile_photo' => $profilePhotoPath,
                ]
            );
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'lastname' => $validated['lastname'] ?? null,
                'firstname' => $validated['firstname'] ?? null,
                'email' => $validated['email'],
                'username' => $validated['username'] ?? null,
                'password' => Hash::make($validated['password']),
                'nif' => $validated['nif'],
                'profile_photo' => $profilePhotoPath,
            ]);

            $categorySync->assignCategory($user, $categoryCode, primary: true);
        }

        if ($categoryCode === UserTypeEnum::PENSIONNE->value) {
            app(BeneficiaireProvisioningService::class)->provisionAdultSelf(
                $user->fresh(),
                null,
                $validated['pension_code'] ?? null
            );
        }

        event(new Registered($user));

        return redirect()->route('admin.dashboard.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }
}
