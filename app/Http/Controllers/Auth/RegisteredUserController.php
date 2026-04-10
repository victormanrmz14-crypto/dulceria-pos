<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
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
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => ['nullable', 'string', 'max:150'],
            'name' => ['nullable', 'string', 'max:150'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $nombre = trim((string) ($request->input('nombre') ?: $request->input('name')));
        if ($nombre === '') {
            $nombre = 'Usuario';
        }

        $username = trim((string) $request->input('username'));
        if ($username === '') {
            $base = Str::of($request->input('email'))->before('@')->slug('');
            $username = (string) Str::limit($base === '' ? Str::lower(Str::random(8)) : $base, 90, '');
        }

        $originalUsername = $username;
        $i = 1;
        while (User::query()->where('username', $username)->exists()) {
            $username = Str::limit($originalUsername, 90, '') . $i;
            $i++;
        }

        $user = User::create([
            'nombre' => $nombre,
            'username' => $username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
