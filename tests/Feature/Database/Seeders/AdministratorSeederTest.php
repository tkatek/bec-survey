<?php

namespace Tests\Feature\Database\Seeders;

use App\Models\User;
use Database\Seeders\AdministratorSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Tests\TestCase;

class AdministratorSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeding_creates_one_administrator_with_the_configured_credential(): void
    {
        config()->set('auth.administrator', [
            'name' => 'Survey Administrator',
            'email' => 'admin@bostonenglishcenter.com',
            'password' => 'fixed-administrator-password',
        ]);

        $this->seed(AdministratorSeeder::class);
        $this->seed(AdministratorSeeder::class);

        $administrator = User::query()->where('email', 'admin@bostonenglishcenter.com')->sole();

        $this->assertSame('Survey Administrator', $administrator->name);
        $this->assertTrue($administrator->is_admin);
        $this->assertTrue(Hash::check('fixed-administrator-password', $administrator->password));
        $this->assertDatabaseCount('users', 1);
    }

    public function test_seeding_rejects_a_missing_administrator_password(): void
    {
        config()->set('auth.administrator.password');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('ADMIN_PASSWORD');

        $this->seed(AdministratorSeeder::class);
    }
}
