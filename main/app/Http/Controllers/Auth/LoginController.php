<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    // POST /api/auth/register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed', // needs password_confirmation field
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->register($request->all());

        return response()->json([
            'status'  => 'success',
            'message' => 'User registered successfully',
            'data'    => $result,
        ], 201);
    }

    // POST /api/auth/login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $result = $this->authService->login($credentials);

        if (!$result) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid email or password'
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $result,
        ]);
    }

    // POST /api/auth/logout  (needs jwt.auth middleware)
    public function logout()
    {
        $this->authService->logout();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    // POST /api/auth/refresh  (needs jwt.auth middleware)
    public function refresh()
    {
        $result = $this->authService->refresh();

        return response()->json([
            'status' => 'success',
            'data'   => $result,
        ]);
    }

    // GET /api/auth/me  (needs jwt.auth middleware)
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'data'   => auth('api')->user(),
        ]);
    }
}