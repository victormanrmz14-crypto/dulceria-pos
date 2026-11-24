<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ProfileController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Perfil/Index');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
            'user_id' => ['prohibited'],
            'avatar_path' => ['prohibited'],
        ], [
            'avatar.required' => 'Selecciona una fotografía antes de guardar.',
            'avatar.image' => 'Selecciona una imagen JPEG, PNG o WebP válida.',
            'avatar.mimetypes' => 'Solo se permiten fotografías JPEG, PNG o WebP.',
            'avatar.max' => 'La fotografía debe pesar como máximo 2 MB.',
        ]);

        $user = $request->user();
        $path = $request->file('avatar')->store('avatars/'.$user->getKey(), 'public');

        if ($path === false) {
            throw ValidationException::withMessages(['avatar' => 'No se pudo guardar la fotografía. Inténtalo de nuevo.']);
        }

        try {
            $previous = DB::transaction(function () use ($user, $path) {
                $current = User::query()->lockForUpdate()->findOrFail($user->getKey());
                $previous = $current->avatar_path;
                $current->avatar_path = $path;
                $current->save();

                return $previous;
            });
        } catch (Throwable $exception) {
            $this->deleteAvatarFile($user, $path);
            throw $exception;
        }
        $user->avatar_path = $path;

        // Solo se retira el anterior tras almacenar y confirmar la nueva referencia.
        $this->deleteAvatarFile($user, $previous);

        return back()->with('success', 'Fotografía de perfil guardada.');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $request->validate(['user_id' => ['prohibited'], 'avatar_path' => ['prohibited']]);
        $user = $request->user();
        $previous = DB::transaction(function () use ($user) {
            $current = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $previous = $current->avatar_path;
            $current->avatar_path = null;
            $current->save();

            return $previous;
        });
        $user->avatar_path = null;
        $this->deleteAvatarFile($user, $previous);

        return back()->with('success', 'Fotografía eliminada. Ahora se muestran tus iniciales.');
    }

    private function deleteAvatarFile(User $user, ?string $path): void
    {
        if (! $user->ownsAvatarPath($path)) {
            return;
        }

        try {
            if (! Storage::disk('public')->delete($path)) {
                Log::warning('No se pudo retirar un archivo de avatar anterior.', ['user_id' => $user->getKey()]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
