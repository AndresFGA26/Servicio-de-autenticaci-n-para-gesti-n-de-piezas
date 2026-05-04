<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;



class Authservice
{
  public function __construct(private UserRepository $userRepository) {}


  //====Login=====
    public function login(Array $data): array
    {
        // DEBUG: Verificar datos recibidos
        \Log::info('AuthService::login - Attempt started', [
            'email' => $data['email'],
            'password_length' => strlen($data['password']),
            'timestamp' => now()->toISOString()
        ]);

        try {
            $user = $this->userRepository->findByEmail($data['email']);

            // DEBUG: Verificar si usuario existe
            if (!$user) {
                \Log::error('AuthService::login - User not found', [
                    'email' => $data['email'],
                    'query_executed' => true
                ]);
                throw new \Exception('Los datos de acceso son incorrectos. Por favor, verifica tu correo y contraseña.');
            }

            // DEBUG: Verificar contraseña hasheada en BD
            \Log::info('AuthService::login - User found', [
                'id' => $user->id,
                'email' => $user->email,
                'password_hash_length' => strlen($user->password),
                'password_hash_starts' => substr($user->password, 0, 20) . '...',
                'user_created_at' => $user->created_at
            ]);

            // DEBUG: Verificar Hash::check
            $passwordCheck = Hash::check($data['password'], $user->password);
            \Log::info('AuthService::login - Hash check result', [
                'password_check_result' => $passwordCheck,
                'input_password' => $data['password'],
                'stored_hash_length' => strlen($user->password),
                'hash_algorithm_used' => 'bcrypt'
            ]);

            if (!$passwordCheck) {
                \Log::error('AuthService::login - Password verification failed', [
                    'email' => $data['email'],
                    'input_password' => $data['password'],
                    'stored_hash' => substr($user->password, 0, 20) . '...'
                ]);
                throw new \Exception('Los datos de acceso son incorrectos. Por favor, verifica tu correo y contraseña.');
            }

            \Log::info('AuthService::login - Authentication successful', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

        } catch (\Exception $e) {
            \Log::error('AuthService::login - Exception occurred', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'email_attempted' => $data['email'] ?? 'unknown'
            ]);
            throw $e;
        }

        $access= $this ->generateJWT($user,3600);
        $refresh= $this ->generateRefreshToken($user);

        return [
            'access_token'=>$access,
            'refresh_token'=>$refresh,
            'token_type'=>'Bearer',
            'expires_in'=> 3600,

        ];

    }
    //====Register=====
    public function register(array $data): array
    {
        \Log::info('AuthService::register - User registration started', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password_length' => strlen($data['password']),
            'timestamp' => now()->toISOString()
        ]);

        try {
            // Verificar si el email ya existe
            $existingUser = $this->userRepository->findByEmail($data['email']);
            if ($existingUser) {
                \Log::error('AuthService::register - Email already exists', [
                    'email' => $data['email']
                ]);
                throw new \Exception('El correo electrónico ya está registrado.');
            }

            // Crear nuevo usuario
            // Laravel aplicará automáticamente el cast 'hashed' para encriptar con bcrypt
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // Laravel encripta automáticamente
            ]);

            \Log::info('AuthService::register - User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'password_hash_length' => strlen($user->password),
                'password_hash_algorithm' => 'bcrypt',
                'created_at' => $user->created_at
            ]);

            // Verificar que la contraseña se haya encriptado correctamente
            $passwordVerification = Hash::check($data['password'], $user->password);
            \Log::info('AuthService::register - Password encryption verification', [
                'password_check_result' => $passwordVerification,
                'original_password' => $data['password'],
                'stored_hash' => substr($user->password, 0, 20) . '...'
            ]);

            if (!$passwordVerification) {
                \Log::error('AuthService::register - Password encryption failed');
                throw new \Exception('Error en el encriptado de la contraseña.');
            }

            // Generar tokens para el usuario recién creado
            $accessToken = $this->generateJWT($user, 3600);
            $refreshToken = $this->generateRefreshToken($user);

            \Log::info('AuthService::register - Registration completed successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at
                ],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ];

        } catch (\Exception $e) {
            \Log::error('AuthService::register - Exception occurred', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email_attempted' => $data['email'] ?? 'unknown'
            ]);
            throw $e;
        }
    }
    // == REFRESH==
    public function refresh(string $refreshToken): array
    {
        $record =\DB::table('refresh_tokens')
        ->where('token',$refreshToken)
        ->where('expires_at','>',now())
        ->first();

        if (!$record) {
            throw new \Exception('Refresh token invalido.');

        }

        $user= User::findOrFail($record->user_id);
        $newAccess= $this->generateJWT($user,3600);

        return [
            'access_token'=>$newAccess,
            'token_type'=>'Bearer',
            'expires_in'=> 3600,

        ];
    }
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    // === JWT SIMPLE(sin Librerias)====
    private function generateJWT(User $user, int $ttl): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]));

        $payload = $this->base64UrlEncode(json_encode([
            'iss' => config('app.name'), // Añadimos el emisor (Issuer)
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + $ttl,
        ]));

        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $this->key(), true)
        );

        return $header . '.' . $payload . '.' . $signature;
    }

    //=== REFRESH TOKEN====
    private function generateRefreshToken(User $user): string
    {
        $token = Str::random(60);
        \DB::table('refresh_tokens')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addDay(7),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $token;
    }

    public function getUserById(int $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }

    /**
     * Obtiene la llave de forma segura. Si no es base64, retorna el texto plano.
     */
    private function key(): string
    {
        $key = env('JWT_SECRET', config('app.key'));
        return str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7)) : $key;
    }

}

