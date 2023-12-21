<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Réccupère la liste des articles 
Route::get('posts', [PostController::class, 'index']);

//Créer un article
Route::post('posts/create', [PostController::class, 'store']);

//Modifier un article
Route::put('posts/edit/{post}', [PostController::class, 'update']);

//Supprimer un article
Route::delete('posts/{post}', [PostController::class, 'delete']);

//Inscription
Route::post('/register', [UserController::class, 'register']);

//Connexion
Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    //Créer un article
    Route::post('posts/create', [PostController::class, 'store']);

    //Modifier un article
    Route::put('posts/edit/{post}', [PostController::class, 'update']);

    //Supprimer un article
    Route::delete('posts/{post}', [PostController::class, 'delete']);

    //Retourner l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
