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
        Admin::create([
            'name' => 'Admin Registration',
            'email' => 'admin@tentangdental.com',
            'password' => Hash::make('password'),
            'role' => 'registration',
        ]);

        // Admin Rontgen
        Admin::create([
            'name' => 'Admin Rontgen',
            'email' => 'rontgen@tentangdental.com',
            'password' => Hash::make('password'),
            'role' => 'rontgen',
        ]);
    }
}
