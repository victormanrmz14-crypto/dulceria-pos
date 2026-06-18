<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreatePlatformAdmin extends Command
{
    protected $signature = 'platform-admin:create
        {--name= : Nombre del super admin}
        {--last-name= : Apellido del super admin}
        {--username= : Username del super admin}
        {--email= : Email del super admin}
        {--password= : Password inicial. Si se omite, se solicita o genera}
        {--force : Actualiza el usuario existente sin pedir confirmación}';

    protected $description = 'Crea o actualiza un super admin de la plataforma.';

    public function handle(): int
    {
        $data = [
            'nombre' => $this->stringOption('name') ?: $this->ask('Nombre', 'Super Admin'),
            'apellido' => $this->stringOption('last-name') ?: $this->ask('Apellido', ''),
            'username' => $this->stringOption('username') ?: $this->ask('Username', 'superadmin'),
            'email' => $this->stringOption('email') ?: $this->ask('Email'),
        ];

        $existingUser = $this->existingUser($data['username'], $data['email']);

        if ($existingUser === false) {
            return self::FAILURE;
        }

        $ignoreId = $existingUser?->id;

        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'max:150'],
            'apellido' => ['nullable', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($ignoreId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if ($existingUser && ! $this->option('force')) {
            $confirmed = $this->confirm(
                "Ya existe el usuario {$existingUser->email}. ¿Quieres actualizarlo como super admin?",
                true
            );

            if (! $confirmed) {
                $this->warn('Operación cancelada.');

                return self::FAILURE;
            }
        }

        [$password, $generated] = $this->password();

        $attributes = [
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'] ?: null,
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'rol' => 'admin',
            'es_super_admin' => true,
            'activo' => true,
        ];

        if ($existingUser) {
            $existingUser->update($attributes);
            $user = $existingUser->refresh();
        } else {
            $user = User::create($attributes);
        }

        $this->info("Super admin listo: {$user->email}");

        if ($generated) {
            $this->warn("Contraseña generada: {$password}");
        }

        return self::SUCCESS;
    }

    private function existingUser(string $username, string $email): User|false|null
    {
        $byEmail = User::where('email', $email)->first();
        $byUsername = User::where('username', $username)->first();

        if ($byEmail && $byUsername && ! $byEmail->is($byUsername)) {
            $this->error('El email y username pertenecen a usuarios distintos.');

            return false;
        }

        return $byEmail ?: $byUsername;
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function password(): array
    {
        $password = $this->stringOption('password');

        if ($password === '') {
            $password = $this->secret('Contraseña inicial (deja vacío para generar una segura)') ?: '';
        }

        if ($password !== '') {
            return [$password, false];
        }

        return [Str::password(20), true];
    }

    private function stringOption(string $key): string
    {
        $value = $this->option($key);

        return is_string($value) ? trim($value) : '';
    }
}
