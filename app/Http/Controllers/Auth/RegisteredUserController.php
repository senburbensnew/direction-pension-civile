<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\RegexExpressions;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
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
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $userTypes = UserType::orderBy('name')->get();
        return view('auth.register', compact('userTypes'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // French field names
        $attributes = [
            'name' => 'nom',
            'email' => 'adresse e-mail',
            'password' => 'mot de passe',
            'nif' => 'NIF',
            'pension_code' => 'code pension',
            'user_type' => 'type d\'utilisateur'
        ];

        // Custom French error messages
        $messages = [
            "required" => 'Le champ ":attribute" est obligatoire.',
            "nif.regex" => "Le NIF doit suivre le format 000-000-000-0 (ex: 123-456-789-0).",
            "pension_code.regex" => "Le code pension doit être au format ABC-123456 (ex: PEN-123456).",
        ];

        // Validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nif' => [
                'required',
                'string',
                'regex:' . RegexExpressions::nif(),
                'unique:' . User::class
            ],
            'user_type' => ['required', 'exists:user_types,id'],
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
        $validated = $request->validate($rules, $messages, $attributes);
        
        // Create the user remains unchanged
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nif' => $request->nif,
            'user_type_id' => $request->user_type,
            'pension_code' => $request->pension_code
        ]);
    
        // Determine role based on user type
        $userType = $user->userType->name; // Assuming `userType` is a relationship to the `UserType` model
        
        switch ($userType) {
            case 'fonctionnaire':
                $user->assignRole('fonctionnaire');
                break;
    
            case 'pensionnaire':
                $user->assignRole('pensionnaire');
                break;
    
            case 'institution':
                $user->assignRole('institution');
                break;
    
            default:
                // Optionally handle unknown user types
                $user->assignRole('pensionnaire');
                break;
        }
    
        // Trigger the registration event
        event(new Registered($user));
    
        // Log the user in
        Auth::login($user);
    
        // Redirect after registration
        return redirect('/');
    }     


/*     public function store(Request $request): RedirectResponse
    {
        // Custom French validation messages and attributes
        $attributes = [
            'name' => 'nom',
            'email' => 'adresse e-mail',
            'password' => 'mot de passe',
            'nif' => 'NIF',
            'pension_code' => 'code pension',
            'user_type' => 'type d\'utilisateur'
        ];

        $messages = [
            "required" => 'Le champ ":attribute" est obligatoire.',
            "nif.regex" => "Le NIF doit suivre le format 000-000-000-0 (ex: 123-456-789-0).",
            "pension_code.regex" => "Le code pension doit être au format ABC-123456 (ex: PEN-123456).",
        ];

        // Base validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nif' => [
                'required',
                'string',
                'regex:' . RegexExpressions::nif(),
                'unique:' . User::class
            ],
            'pension_code' => ['nullable', 'string', 'max:255'],
            'user_type' => ['required', 'exists:user_types,id'],
        ];

        // Validate first to prevent unnecessary DB queries
        $validated = $request->validate($rules, $messages, $attributes);

        // Get user type using Eloquent
        $userType = UserType::find($validated['user_type']);
        $userTypeName = strtolower($userType->name);

        // Configure pension_code rules
        $pensionCodeRules = [
            'string', 
            'max:255',
            'regex:' . RegexExpressions::pensionCode()
        ];

        if ($userTypeName === UserTypeEnum::PENSIONNAIRE->value) {
            $validated['pension_code'] = $request->validate(
                ['pension_code' => ['required', ...$pensionCodeRules]],
                $messages,
                $attributes
            )['pension_code'];
        } else {
            $validated['pension_code'] = $request->pension_code;
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'nif' => $validated['nif'],
            'user_type_id' => $validated['user_type'],
            'pension_code' => $validated['pension_code']
        ]);

        // Assign role using enum
        try {
            $role = match($userTypeName) {
                UserTypeEnum::FONCTIONNAIRE->value => UserTypeEnum::FONCTIONNAIRE->value,
                UserTypeEnum::PENSIONNAIRE->value => UserTypeEnum::PENSIONNAIRE->value,
                UserTypeEnum::INSTITUTION->value => UserTypeEnum::INSTITUTION->value,
                default => throw new \InvalidArgumentException("Type d'utilisateur non valide: {$userTypeName}")
            };
            $user->assignRole($role);
        } catch (\InvalidArgumentException $e) {
            report($e);  // Log the error
            abort(422, 'Type d\'utilisateur invalide');
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect('/');
    } */
}
