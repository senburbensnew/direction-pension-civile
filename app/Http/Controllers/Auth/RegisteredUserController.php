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
            'name' => 'nom d\'utilisateur',
            'firstname' => 'prénom',
            'lastname' => 'nom de famille',
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
    
        $rules = [
            "firstname" => "required|string|max:255",
            "lastname" => "required|string|max:255",
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . User::class],
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
                'regex:' . RegexExpressions::pensionCode(),
                'unique:' . User::class
            ]
        ];
    
        // Fetch user type using validated user_type_id
        $userType = UserType::find($request->user_type_id)?->name;
    
        if ($userType === UserTypeEnum::PENSIONNAIRE->value) {
            $rules['pension_code'] = [
                'required',
                'string',
                'max:255',
                'regex:' . RegexExpressions::pensionCode(),
                'unique:' . User::class
            ];
        }
    
        $validated = $request->validate($rules, $messages, $attributes);
    
        // Process profile photo
        $profilePhotoPath = $request->hasFile('profile_photo') 
            ? $request->file('profile_photo')->store('profile_photos', 'public') 
            : null;
    
        $user = User::create([
            'name' => $validated['name'],
            "lastname" => $validated['lastname'],
            "firstname" => $validated['firstname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'nif' => $validated['nif'],
            'user_type_id' => $validated['user_type_id'],
            'pension_code' => $validated['pension_code'] ?? null,
            'profile_photo' => $profilePhotoPath,
        ]);
    
        // Assign role based on user type
        $user->assignRole($userType);
    
        event(new Registered($user));
        Auth::login($user);
    
        return redirect('/');
    } 
}
