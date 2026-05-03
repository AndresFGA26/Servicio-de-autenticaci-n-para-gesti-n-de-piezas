<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class FixUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@test.com';
        $password = '123456';
        
        Log::info('FixUserSeeder: Starting user fix process');
        
        // Eliminar usuario existente si existe
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            Log::info('FixUserSeeder: Deleting existing user', ['email' => $email]);
            $existingUser->delete();
        }
        
        // Crear nuevo usuario con contraseña correcta
        $user = User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => $password, // Laravel lo hashea automáticamente por el cast 'hashed'
        ]);
        
        Log::info('FixUserSeeder: User created successfully', [
            'id' => $user->id,
            'email' => $user->email,
            'password_hash_length' => strlen($user->password),
            'password_hash_starts' => substr($user->password, 0, 20)
        ]);
        
        // Verificar que Hash::check funciona
        $checkResult = Hash::check($password, $user->password);
        Log::info('FixUserSeeder: Password verification test', [
            'password_check_result' => $checkResult,
            'test_password' => $password
        ]);
        
        if ($checkResult) {
            Log::info('FixUserSeeder: ✅ Password verification PASSED');
        } else {
            Log::error('FixUserSeeder: ❌ Password verification FAILED');
        }
        
        echo "✅ User fix completed. Check logs for details.\n";
        echo "📧 Email: {$email}\n";
        echo "🔑 Password: {$password}\n";
        echo "🔍 Check storage/logs/laravel.log for debug info\n";
    }
}
