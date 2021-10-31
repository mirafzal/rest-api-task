<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'phone_number' => ['required', 'string', 'max:15', 'unique:users']
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'phone_number' => $data['phone_number']
        ]);
        $token = $user->createToken('API Token');

        return ['token' => $token->plainTextToken];
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($data)) {
            return response(['token' => Auth::user()->createToken('API Token')->plainTextToken]);
        }

        return response('Credentials not match', 401);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return ['message' => 'Logging out succeed.'];
    }
}
