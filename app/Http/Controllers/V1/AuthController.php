<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\LogoutRequest;
use App\Services\Authservice;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private Authservice $auth){}

    public function login(LoginRequest $request)
    {
        $data = $this->auth->login($request->validated());
        return $this->successResponse($data, 'Login exitoso');
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string'
        ]);

        $data = $this->auth->refresh($request->refresh_token);
        return $this->successResponse($data, 'Token refrescado exitosamente');
    }

    public function logout(LogoutRequest $request)
    {
        $deleted = DB::table('refresh_tokens')
            ->where('token', $request->refresh_token)
            ->delete();

        if ($deleted) {
            return $this->successResponse(null, 'Logout exitoso');
        }

        return $this->errorResponse('Token no encontrado', 404);
    }
}
