<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new User;
        $user->name = 'Frank Pulido';
        $user->nickname = 'frankpulido';
        $user->email = 'frankpulido@me.com';
        $user->email_verified_at = now();
        $user->password = bcrypt('1234');
        $user->role = 'admin';  // Role for Sanctum (I cannot install Passport in my XAMPP php version)
        $user->remember_token = Str::random(10);
        $user->save();
        
        User::factory(12)->create();
    }
}
