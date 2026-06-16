<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin Registration
        Admin::firstOrCreate([
            'email' => 'admin@tentangdental.com',
        ], [
            'name' => 'Admin Registration',
            'password' => Hash::make('password'),
            'role' => 'registration',
        ]);

        // Admin Rontgen
        Admin::firstOrCreate([
            'email' => 'rontgen@tentangdental.com',
        ], [
            'name' => 'Admin Rontgen',
            'password' => Hash::make('password'),
            'role' => 'rontgen',
        ]);
    }
}
