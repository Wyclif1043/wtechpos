<?php
// app/Console/Commands/CreateAdminUser.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'pos:create-admin';
    protected $description = 'Create admin user for POS system';

    public function handle()
    {
        $name = $this->ask('Enter admin name');
        $email = $this->ask('Enter admin email');
        $password = $this->secret('Enter admin password');
        
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'pin_code' => rand(1000, 9999)
        ]);

        $this->info('Admin user created successfully!');
        $this->info('PIN Code: ' . $user->pin_code);
    }
}