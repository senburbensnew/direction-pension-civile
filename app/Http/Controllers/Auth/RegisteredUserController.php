<?php

namespace App\Http\Controllers\Auth;

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
        $userType = DB::table('user_types')->where('id', $request->user_type)->value('name');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nif' => ['required', 'string', 'size:10', 'regex:/^\d{10}$/', 'unique:' . User::class],
            'user_type' => ['required', 'exists:user_types,id'],
            'pension_code' => ['nullable', 'string', 'max:255']
        ];
        
        // Add pension_code validation dynamically
        if ($userType === 'pensionnaire') {
            $rules['pension_code'][] = 'required';
        }
        
        $request->validate($rules);
    
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nif' => $request->nif,
            'user_type_id' => $request->user_type, // Store the user_type_id
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
}
