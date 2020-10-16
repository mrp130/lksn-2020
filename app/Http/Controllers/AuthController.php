<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
        return auth()->shouldUse('api');
    }

    public function login()
    {
        $credentials = request(['username', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function reset(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string|max:20',
            'new_password' => 'required|string|max:20',
        ]);

        $user = auth()->user();
        if(!Hash::check($request->input('old_password'),$user->password)) {
            abort(422, 'old password did not match');
        }

        $user->password = bcrypt($request->input('new_password'));
        $user->save();

        auth()->logout();
        return response()->json(['message' => 'reset success, user logged out']);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
