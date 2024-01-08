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
            $user = new User();

            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password, [
                'rounds' => 12,
            ]);
            $user->save();
            $role = Role::where('name', $request->input('role'))->first();
            $user->roles()->attach($role);

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Utilisateur enregistré.',
                'user' => $user,
                'role' => $role->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'inscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(LogUserRequest $request)
    {
        try {
            if (auth()->attempt($request->only('email', 'password'))) {
                $user = auth()->user();
                $token = $user->createToken('clefsecrete')->plainTextToken;

                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Utilisateur connecté.',
                    'user' => $user,
                    'role' => $user->roles()->first()->name,
                    'token' => $token
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
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, User $user)
{
    // Validate the request data
    $data = $request->validate([
        'name' => 'required|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'password' => 'required',
    ]);

    // If a new password is provided, hash it
    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password, ['rounds' => 12]);
    }

    // Update the user with the validated data
    $user->update($data);

    // Return the updated user
    return response()->json([
        'status_code' => 200,
        'status_message' => 'Utilisateur mis à jour.',
        'user' => $user
    ]);
}

public function destroy(User $user)
{
    // Check the user's role
    if ($user->hasRole('beatmaker')) {
        // If the user is a beatmaker, delete their beats
        $user->beats()->delete();
    } elseif ($user->hasRole('artist')) {
        // If the user is a singer, delete their songs
        $user->songs()->delete();
    }

    // Delete the user
    $user->delete();

    // Return a success message
    return response()->json([
        'status_code' => 200,
        'status_message' => 'Utilisateur supprimé.',
    ]);
}
}