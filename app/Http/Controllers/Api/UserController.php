<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogUserRequest;
use App\Http\Requests\RegisterUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(RegisterUser $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password, ['rounds' => 12]),
            ]);

            $role = Role::where('name', $request->input('role'))->first();
            $user->roles()->attach($role);

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Utilisateur enregistré.',
                'user' => $user,
                'role' => $role->name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur est survenue lors de l\'inscription',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function login(LogUserRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (auth()->attempt($credentials)) {
                $user = auth()->user();
                $token = $user->createToken('clefsecrete')->plainTextToken;

                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Utilisateur connecté.',
                    'user' => $user,
                    'role' => $user->roles()->first()->name,
                    'token' => $token,
                ]);
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Informations non valides.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur est survenue',
                'error' => $e->getMessage(),
            ]);
        }
    }
}