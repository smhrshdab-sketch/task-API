<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller{
    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = [
            'email' => trim(strtolower($request->email)),
            'password' => $request->password,
        ];

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            logger('Login failed', [
                'email' => $credentials['email'],
            ]);

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }
        $account = auth('api')->user();

        $memberships = $account->memberships()
            ->where('status', 'active')
            ->with(['organization', 'department', 'role'])
            ->get();

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => $account,
            'memberships' => $memberships,
        ]);
    }
    //===========
    public function me(){
        return response()->json(auth('api')->user());
    }

    public function logout(){
        auth('api')->logout();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh(){
        return response()->json([
            'access_token' => auth('api')->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
