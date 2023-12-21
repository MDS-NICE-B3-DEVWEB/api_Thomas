<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogUserRequest;
use App\Http\Requests\RegisterUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(RegisterUser $request)
    {
        try {
            $user = new User();

            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password, [
                'rounds' => 12,
            ]);
            $user->save();
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Utilisateur enregistré.',
                'user'=>$user
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'inscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(LogUserRequest $request)
    {
        try {
            if(auth()->attempt($request->only('email','password'))) {
                $user = auth()->user();
                $token = $user->createToken('clefsecrete')->plainTextToken;
                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Utilisateur connecté.',
                    'user' => $user,
                    'token' => $token,
                ]);
            }
            else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Informations non valides.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ]);
        }
    }
}
