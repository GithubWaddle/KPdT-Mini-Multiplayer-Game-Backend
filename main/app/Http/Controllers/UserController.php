<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    // GET /api/users
    public function index()
    {
        $users = $this->userService->getAllUsers();

        return response()->json([
            'status' => 'success',
            'data'   => $users,
        ]);
    }

    // GET /api/users/{id}
    public function show(int $id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $user,
        ]);
    }

    // PUT /api/users/profile
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . auth('api')->id(),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $this->userService->updateProfile(
            auth('api')->id(),
            $request->only('name', 'email')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated',
            'data'    => $user,
        ]);
    }
}