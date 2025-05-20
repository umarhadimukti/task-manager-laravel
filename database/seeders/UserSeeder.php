<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'admin',
            'email' => 'admin@taskmanager.test',
            'password' => bcrypt('test1234'),
            'role' => 'admin',
            'status' => true,
        ]);
        
        User::create([
            'name' => 'manager',
            'email' => 'manager@taskmanager.test',
            'password' => bcrypt('test1234'),
            'role' => 'manager',
            'status' => true,
        ]);
        
        User::create([
            'name' => 'staff',
            'email' => 'staff@taskmanager.test',
            'password' => bcrypt('test1234'),
            'role' => 'staff',
            'status' => true,
        ]);

        User::factory(5)->create();
    }
}
