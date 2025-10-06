<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function register(RegisterRequest $request): jsonResponse
    {
        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        Auth::login($user);

        return response()->json([
            'user' => $user,
            'token' => Auth::user()->createToken('default')->plainTextToken,
        ], 201);

    }

    public function login(LoginRequest $request): jsonResponse
    {

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json([
                'user' => Auth::user(),
                'token' => Auth::user()->createToken('default')->plainTextToken,
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid credentials.',
        ], 401);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.'
        ], 200);
    }
    
}
