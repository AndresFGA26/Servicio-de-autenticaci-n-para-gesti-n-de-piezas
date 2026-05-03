<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

    class UserSeeder extends Seeder
    {
        public function run(): void
        {
            try {
            $user = User::firstOrCreate(
                ['email'=> 'admin@test.com'],
                [
                    'name'=> 'Admin',
                    'password'=> '123456', // Laravel lo hashea automáticamente por el cast 'hashed'
                ]
            );
            
            \Log::info('UserSeeder: User created/found successfully', [
                'id' => $user->id,
                'email' => $user->email,
                'password_hash_length' => strlen($user->password),
                'password_hash_starts' => substr($user->password, 0, 20)
            ]);
            
            // Verificar que la contraseña funcione
            $checkResult = \Illuminate\Support\Facades\Hash::check('123456', $user->password);
            \Log::info('UserSeeder: Password verification', [
                'verification_result' => $checkResult ? 'PASSED' : 'FAILED'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('UserSeeder: Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }


        }
    }

