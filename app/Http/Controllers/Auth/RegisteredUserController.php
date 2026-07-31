<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\UserAvatar;
//use App\Models\UserWorld;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'firstName' => ['required', 'string', 'max:50'],
            'lastName' => ['required', 'string', 'max:50']
        ]);

        $newUid = 0;
        $userEx = null;
        $attemptsLeft = 100;
    
        do {
            $newUid = rand(1111111111, 9999999999);
            $userEx = User::where('uid', '=', $newUid)->first();
            $attemptsLeft--;
        } while ($userEx != null && $attemptsLeft > 0);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'uid' => $newUid
        ]);
        
        if ($user === null) {
            throw new \Exception('Error: UID generation got stuck! Please try again.');
        }
        
        // Create the user meta
        $userMeta = UserMeta::create([
            'uid' => $newUid,
            'firstName' => request('firstName'),
            'lastName' => request('lastName'),
            // the schema specifies additional defaults
        ]);

        $userAvatar = UserAvatar::create([
            'uid' => $newUid,
            // the other field defaults to null
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
