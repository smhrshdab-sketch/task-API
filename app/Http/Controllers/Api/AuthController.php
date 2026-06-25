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

        logger('Login success', [
            'email' => $credentials['email'],
            'user_id' => Auth::guard('api')->user()->id,
        ]);
        $uid = auth('api')->user();
        $member = $uid->memberships;
        //$member = Membership::where('account_id',$uid->id);
        logger('Account: ',[$uid->name]);
        logger('Membership: ',[$member[0]]);
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'department'   => $member[0]->department_id,
            'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60,
            'user'         => Auth::guard('api')->user(),
        ]);
    }
    //===========
    public function me(){
        logger('me me');
        return response()->json(auth('api')->user());
    }

    public function logout(){
        logger('logout',[auth('api')]);
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
