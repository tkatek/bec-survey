<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = config('auth.administrator.name');
        $email = config('auth.administrator.email');
        $password = config('auth.administrator.password');

        if (! is_string($name) || blank($name) || ! is_string($email) || blank($email) || ! is_string($password) || blank($password)) {
            throw new LogicException('ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD must be configured before seeding the administrator.');
        }

        $administrator = User::query()->firstOrNew(['email' => $email]);
        $administrator->forceFill([
            'name' => $name,
            'password' => $password,
            'is_admin' => true,
        ])->save();
    }
}
