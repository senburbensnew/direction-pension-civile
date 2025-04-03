<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\RegexExpressions;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\UserType;
use App\Enums\UserTypeEnum;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $userTypes = UserType::orderBy('name')->get();
        return view('auth.register', compact('userTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = [
            'name' => 'nom',
            'prenom' => 'milorme',
            'lastname' => 'nom',
            'email' => 'adresse e-mail',
            'password' => 'mot de passe',
            'nif' => 'NIF',
            'pension_code' => 'code pension',
            'user_type_id' => 'type d\'utilisateur',
            "profile_photo" => "photo profil",
        ];

        $messages = [
            "required" => 'Le champ ":attribute" est obligatoire.',
            "nif.regex" => "Le NIF doit suivre le format 000-000-000-0 (ex: 123-456-789-0).",
            "pension_code.regex" => "Le code pension doit être au format ABC-123456 (ex: PEN-123456).",
        ];

        // Validation rules
        $rules = [
            "lastname" => "required|string|max:255",
            "firstname" => "required|string|max:255",
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nif' => [
                'required',
                'string',
                'regex:' . RegexExpressions::nif(),
                'unique:' . User::class
            ],
            'user_type_id' => ['required', 'exists:user_types,id'],
            'pension_code' => [
                'nullable',
                'string',
                'max:255',
                'regex:' . RegexExpressions::pensionCode()
            ]
        ];

        // Dynamic pension_code rule for pensionnaires
        $userType = DB::table('user_types')
            ->where('id', $request->user_type)
            ->value('name');
        

        if ($userType === 'pensionnaire') {
            $rules['pension_code'] = [
                'required',
                'string',
                'max:255',
                'regex:' . RegexExpressions::pensionCode()
            ];
        }else{
            $rules['pension_code'] = [
                'nullable',
                'string',
                'max:255',
            ];
        }

        // Validate request
        $request->validate($rules, $messages, $attributes);

        // Process profile photo
        $profilePhotoPath = null; // Initialize to null
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // Create the user remains unchanged
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nif' => $request->nif,
            'user_type_id' => $request->user_type_id,
            'pension_code' => $request->pension_code,
            'profile_photo' => $profilePhotoPath,
            "lastname" => $request->last_name,
            "firstname" => $request->firstname,
        ]);
    
        // Determine role based on user type
        $userType = $user->userType->name; // Assuming `userType` is a relationship to the `UserType` model

        switch ($userType) {
            case UserTypeEnum::FONCTIONNAIRE->value :
                $user->assignRole(UserTypeEnum::FONCTIONNAIRE->value);
                break;    
            case UserTypeEnum::PENSIONNAIRE->value :
                $user->assignRole(UserTypeEnum::PENSIONNAIRE->value);
                break;    
            case UserTypeEnum::INSTITUTION->value :
                $user->assignRole(UserTypeEnum::INSTITUTION->value);
                break;    
            default:
                $user->assignRole(UserTypeEnum::PENSIONNAIRE->value);
                break;
        }
    
        // Trigger the registration event
        event(new Registered($user));
    
        // Log the user in
        Auth::login($user);
    
        // Redirect after registration
        return redirect('/');
    }     
}
