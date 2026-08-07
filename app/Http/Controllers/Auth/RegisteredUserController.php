<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
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
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Crea un tenant nuevo y registra al primer usuario como admin.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'negocio'  => ['nullable', 'string', 'max:200'],
            'nombre'   => ['nullable', 'string', 'max:150'],
            'name'     => ['nullable', 'string', 'max:150'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Nombre del negocio: lo que escribió el usuario o derivado del email
        $nombreNegocio = trim((string) $request->input('negocio'));
        if ($nombreNegocio === '') {
            $nombreNegocio = 'Dulcería ' . Str::of($request->input('email'))->before('@')->title();
        }

        // Crear el tenant
        $tenant = Tenant::create(['nombre' => $nombreNegocio]);

        // Nombre del usuario
        $nombre = trim((string) ($request->input('nombre') ?: $request->input('name')));
        if ($nombre === '') {
            $nombre = Str::of($request->input('email'))->before('@')->title()->toString();
        }

        // Username único derivado del email
        $base     = Str::of($request->input('email'))->before('@')->slug('')->toString();
        $username = $base ?: Str::lower(Str::random(8));
        $original = Str::limit($username, 90, '');
        $i = 1;
        while (User::withoutGlobalScopes()->where('username', $username)->exists()) {
            $username = $original . $i++;
        }

        // Primer usuario del tenant siempre es administrador
        $user = User::create([
            'tenant_id' => $tenant->id,
            'nombre'    => $nombre,
            'username'  => $username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'rol'       => 'admin',
            'activo'    => true,
        ]);

        event(new Registered($user));
        Auth::login($user);

        session()->flash('bienvenida', true);

        return redirect()->route('dashboard');
    }
}
