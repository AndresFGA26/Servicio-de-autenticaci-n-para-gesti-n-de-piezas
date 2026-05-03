<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SupabaseUserSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('=== SUPABASE USER SEEDER STARTED ===');
        
        try {
            // Verificar conexión a PostgreSQL
            $connection = DB::connection()->getPdo();
            Log::info('Database connection successful', [
                'driver' => DB::connection()->getDriverName(),
                'database' => DB::connection()->getDatabaseName()
            ]);
            
            // Limpiar usuario existente si hay
            $existingUser = User::where('email', 'admin@test.com')->first();
            if ($existingUser) {
                Log::info('Deleting existing user', ['email' => 'admin@test.com']);
                $existingUser->delete();
            }
            
            // Crear nuevo usuario con contraseña correcta
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => '123456', // Laravel lo hashea automáticamente por el cast 'hashed'
            ]);
            
            Log::info('User created successfully', [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'password_hash_length' => strlen($user->password),
                'password_hash_preview' => substr($user->password, 0, 25) . '...',
                'created_at' => $user->created_at
            ]);
            
            // Verificación inmediata de contraseña
            $verificationTest = [
                'input_password' => '123456',
                'stored_hash' => $user->password,
                'hash_check_result' => Hash::check('123456', $user->password),
                'verification_timestamp' => now()->toISOString()
            ];
            
            Log::info('Password verification test', $verificationTest);
            
            if (!Hash::check('123456', $user->password)) {
                Log::error('CRITICAL: Password verification FAILED immediately after creation!');
                throw new \Exception('Password verification failed - this should never happen');
            }
            
            // Verificar que refresh_tokens table exista
            $refreshTokensExists = DB::getSchemaBuilder()->hasTable('refresh_tokens');
            Log::info('Refresh tokens table check', ['exists' => $refreshTokensExists]);
            
            if (!$refreshTokensExists) {
                Log::error('CRITICAL: refresh_tokens table does not exist!');
                throw new \Exception('refresh_tokens table missing - run migrations first');
            }
            
            Log::info('=== SUPABASE USER SEEDER COMPLETED SUCCESSFULLY ===');
            
            echo "\n✅ Supabase User Seeder Completed Successfully\n";
            echo "📧 Email: admin@test.com\n";
            echo "🔑 Password: 123456\n";
            echo "🔍 Check storage/logs/laravel.log for detailed info\n";
            
        } catch (\Exception $e) {
            Log::error('SupabaseUserSeeder FAILED', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            echo "\n❌ Supabase User Seeder FAILED\n";
            echo "📋 Error: " . $e->getMessage() . "\n";
            echo "🔍 Check storage/logs/laravel.log for details\n";
            
            throw $e;
        }
    }
}
