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
        // Récupérer tous les Sons
        $songs = Song::all(); 

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Bibliothèque de Sons récupérée avec succès.',
            'songs' => $songs,
        ]);
    }

    public function artistSongs($artistId)
    {
        // Récupérer les Sons de l'artiste spécifié
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
            $song = new Song();
            $song->user_id = auth()->user()->id;
            $song->artist_name = auth()->user()->name; // Ajout du nom de l'artiste
            $song->title = $request->title;

            // Enregistrez le fichier MP3 dans le stockage
            $audioFile = $request->file('audio_file');

            if ($audioFile->isValid()) {
                // Renommer le fichier MP3 en fonction du titre
                $title = str_replace([' ', 'é'], ['_', 'e'], $song->title);
                $artistName = str_replace([' ', 'é'], ['_', 'e'], $song->artist_name);
                $fileName = $this->getUniqueFileName($title, $audioFile->getClientOriginalExtension(), $artistName);

                // Enregistrez le fichier MP3 dans le stockage avec le nom spécifié
                $audioFilePath = $audioFile->storeAs("songs/{$artistName}", $fileName, 'public');
                $song->file_path = $audioFilePath;
                $song->save();

                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Son enregistré avec succès.',
                    'song' => $song,
                ]);
            } else {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Erreur lors de l\'enregistrement du fichier audio.',
                ]);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'enregistrement du Son.',
                'exception' => $exception,
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
            // Valider les données du formulaire (y compris le fichier MP3)
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'audio_file' => 'nullable|mimes:mp3|max:10240', // Vérifiez le fichier MP3 s'il est fourni
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'errors' => $validator->errors(),
                ]);
            }

            // Mettre à jour les champs du Son
            $song->title = $request->title;

            // Si un nouveau fichier MP3 est fourni, procédez au renommage et à l'enregistrement
            if ($request->hasFile('audio_file')) {
                $audioFile = $request->file('audio_file');
                if ($audioFile->isValid()) {
                    // Obtenez le nouveau titre
                    $newTitle = str_replace([' ', 'é'], ['_', 'e'], $song->title);

                    // Obtenez le nom du fichier actuel
                    $currentFileName = pathinfo($song->file_path, PATHINFO_FILENAME);

                    $artistName = str_replace([' ', 'é'], ['_', 'e'], $song->artist_name);

                    // Renommer le fichier MP3 en fonction du nouveau titre
                    $fileName = $this->getUniqueFileName($newTitle, $audioFile->getClientOriginalExtension(), $artistName);

                    // Supprimer l'ancien fichier
                    Storage::disk('public')->delete($song->file_path);

                    // Enregistrez le nouveau fichier MP3 dans le stockage avec le nom spécifié
                    $audioFilePath = $audioFile->storeAs("songs/{$newTitle}", $fileName, 'public');
                    $song->file_path = $audioFilePath;
                }
            }

            $song->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Son mis à jour avec succès.',
                'song' => $song,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur est survenue lors de la mise à jour du Son.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Song $song)
    {
        try {
            $song->delete();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Son supprimé avec succès.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la suppression du Son.',
                'exception' => $exception,
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
