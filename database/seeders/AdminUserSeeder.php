<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => 'admin@nghomecleaners.co.uk'],
            [
                'name' => 'NG Admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
