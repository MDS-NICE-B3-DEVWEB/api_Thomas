<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class SongController extends Controller
{
    public function index()
    {
        $songs = Song::all();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Bibliothèque de Sons récupérée avec succès.',
            'songs' => $songs,
        ], 200);
    }

    public function artistSongs($artistId)
    {
        $songs = Song::where('user_id', $artistId)->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Sons de l\'artiste récupérés avec succès.',
            'songs' => $songs,
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'audio_file' => 'required|mimes:mp3|max:51200', // 50MB maximum
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $song = new Song();
            $song->user_id = auth()->user()->id;
            $song->artist_name = auth()->user()->name;
            $song->title = $request->title;

            $audioFile = $request->file('audio_file');

            $fileName = $this->getUniqueFileName($song->title, $audioFile->getClientOriginalExtension(), $song->artist_name);
            $audioFilePath = $audioFile->storeAs("songs/{$song->artist_name}", $fileName, 'public');

            $song->file_path = $audioFilePath;
            $song->save();

            return response()->json([
                'status_code' => 201,
                'status_message' => 'Son enregistré avec succès.',
                'song' => $song,
            ], 201);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'enregistrement du Son.',
                'exception' => $exception->getMessage(),
            ], 500);
        }
    }

    public function show(Song $song)
    {
        if (!$song) {
            return response()->json([
                'status_code' => 404,
                'error' => 'Son non trouvé.',
            ], 404);
        }
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Son récupéré avec succès.',
            'song' => $song,
        ], 200);
    }

    public function update(Request $request, Song $song)
    {
        try {
            if (Auth::id() !== $song->user_id) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'Vous n\'avez pas la permission de modifier cette chanson.',
                ], 403);
            }
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Update the beat with the validated data
            $song->update($validator->validated());

            // Return the updated beat
            return response()->json(
                [
                    'status_code' => 200,
                    'status_message' => 'Son mis à jour avec succès.',
                    'song' => $song,
                ],
                200
            );

        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la mise à jour du Son.',
                'exception' => $exception->getMessage(),
            ], 500);
        }
    }


    public function destroy(Song $song)
    {
        try {
            if (Auth::id() !== $song->user_id) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'Vous n\'avez pas la permission de supprimer cette chanson.',
                ], 403);
            }
            Storage::disk('public')->delete($song->file_path);
            $song->delete();

            return response()->json([
                'status_code' => 204,
                'status_message' => 'Son supprimé avec succès.',
            ], 204);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la suppression du Son.',
                'exception' => $exception->getMessage(),
            ], 500);
        }
    }

    private function getUniqueFileName($title, $extension, $artistName)
    {
        $fileName = "{$title}.{$extension}";

        if (Storage::disk('public')->exists("songs/{$artistName}/{$fileName}")) {
            $fileName = "{$title}-" . time() . ".{$extension}";
        }

        return $fileName;
    }
}