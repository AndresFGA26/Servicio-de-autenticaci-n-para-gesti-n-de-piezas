<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;

class JwtMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');

        if(!$header) {
            return $this->errorResponse('No se proporcionó el encabezado Authorization.', 401);
        }

        if(!str_starts_with($header, 'Bearer ')) {
            return $this->errorResponse('El formato del token debe ser "Bearer <token>".', 401);
        }

        $token = str_replace('Bearer ', '', $header);

        try{
            $parts = explode('.', $token);
            if(count($parts) !== 3){
                return $this->errorResponse('Estructura de token inválida. El JWT debe tener 3 partes separadas por puntos.', 401);
            }

            [$header64, $payload64, $signature] = $parts;

            // Decodificar el header para validar el algoritmo
            $headerDecoded = json_decode($this->base64UrlDecode($header64), true);
            if (!$headerDecoded || !isset($headerDecoded['alg']) || $headerDecoded['alg'] !== 'HS256') {
                return $this->errorResponse('Algoritmo de token no soportado o inválido.', 401);
            }

            $expectedSignature = $this->base64UrlEncode(
                hash_hmac('sha256', "$header64.$payload64", $this->key(), true)
            );

            // Previene ataques de timing (Timing Attacks)
            if(!hash_equals($expectedSignature, $signature)) {
                return $this->errorResponse('La firma del token es inválida o el token ha sido manipulado.', 401);
            }

            $payload = json_decode($this->base64UrlDecode($payload64), true);

            if (!$payload) {
                return $this->errorResponse('El payload del token no es válido o está corrupto.', 401);
            }

            if(!isset($payload['exp'])) {
                return $this->errorResponse('El token no contiene fecha de expiración (claim "exp").', 401);
            }

            if ($payload['exp'] < time()) {
                return $this->errorResponse('El token ha expirado.', 401);
            }

            if(isset($payload['iat']) && $payload['iat'] > time()) {
                return $this->errorResponse('El token es del futuro (claim "iat" inválido).', 401);
            }

            if(!isset($payload['sub'])) {
                return $this->errorResponse('El token no contiene el identificador de usuario (claim "sub").', 401);
            }

            // El usuario se pude guardar en el request
            $request->merge(['user_id' => $payload['sub']]);

        } catch (\Exception $exception){
            return $this->errorResponse('Error inesperado al procesar el token: ' . $exception->getMessage(), 401);
        }

        return $next($request);
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private function key(): string
    {
        $key = env('JWT_SECRET', config('app.key'));
        return str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7)) : $key;
    }
}
