<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SongController extends Controller
{
    public function index()
    {
        $songs = Song::all(); 

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Bibliothèque de Sons récupérée avec succès.',
            'songs' => $songs,
        ]);
    }

    public function artistSongs($artistId)
    {
        $songs = Song::where('user_id', $artistId)->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Sons de l\'artiste récupérés avec succès.',
            'songs' => $songs,
        ]);
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
                ]);
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
                'status_code' => 200,
                'status_message' => 'Son enregistré avec succès.',
                'song' => $song,
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'enregistrement du Son.',
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function show(Song $song)
    {
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Son récupéré avec succès.',
            'song' => $song,
        ]);
    }

    public function update(Request $request, Song $song)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'errors' => $validator->errors(),
                ]);
            }

            // Update the beat with the validated data
            $song->update($validator->validated());

            // Return the updated beat
            return response()->json(
                [
                    'status_code' => 200,
                    'status_message' => 'Son mis à jour avec succès.',
                    'song' => $song,
                ]
            );

        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la mise à jour du Son.',
                'exception' => $exception->getMessage(),
            ]);
        }
    }


    public function destroy(Song $song)
    {
        try {
            Storage::disk('public')->delete($song->file_path);
            $song->delete();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Son supprimé avec succès.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la suppression du Son.',
                'exception' => $exception->getMessage(),
            ]);
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
