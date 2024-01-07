<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BeatController extends Controller
{
    // Afficher la bibliothèque de Beats pour un Beatmaker
    public function index()
    {
        // Récupérer tous les Beats
        $beats = Beat::all();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Bibliothèque de Beats récupérée avec succès.',
            'beats' => $beats,
        ]);
    }
    
    // Afficher la bibliothèque de Beats pour un Beatmaker
    public function beatmakerBeats($beatmakerId)
    {
        // Récupérer les beats du beatmaker spécifié
        $beats = Beat::where('user_id', $beatmakerId)->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Beats du beatmaker récupérés avec succès.',
            'beats' => $beats,
        ]);
    }

    // Enregistrer un nouveau Beat
    public function store(Request $request)
    {
        try {
            $beat = new Beat();
            $beat->user_id = auth()->user()->id;
            $beat->beatmaker_name = auth()->user()->name; // Ajout du nom du Beatmaker
            $beat->title = $request->title;
            $beat->description = $request->description;

            // Enregistrez le fichier MP3 dans le stockage
            $audioFile = $request->file('audio_file');

            if ($audioFile->isValid()) {
                // Renommer le fichier MP3 en fonction du titre
                $title = str_replace([' ', 'é'], ['_', 'e'], $beat->title);
                $beatmakerName = str_replace([' ', 'é'], ['_', 'e'], $beat->beatmaker_name);
                $fileName = $this->getUniqueFileName($title, $audioFile->getClientOriginalExtension(), $beatmakerName);

                // Enregistrez le fichier MP3 dans le stockage avec le nom spécifié
                $audioFilePath = $audioFile->storeAs("beats/{$beatmakerName}", $fileName, 'public');
                $beat->file_path = $audioFilePath;
                $beat->save();

                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Beat enregistré avec succès.',
                    'beat' => $beat,
                ]);
            } else {
                return response()->json([
                    'status_code' => 422,
                    'errors' => [
                        'audio_file' => ['The audio file is not valid.']
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur est survenue lors de l\'enregistrement du Beat.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function show(Beat $beat)
    {
        // Si le beat n'existe pas, retourner une erreur 404
        if (!$beat) {
            return response()->json([
                'status_code' => 404,
                'error' => 'Beat non trouvé.',
            ], 404);
        }

        // Retourner le beat
        return response()->json([
            'status_code' => 200,
            'data' => $beat,
        ], 200);
    }

    public function update(Request $request, Beat $beat)
    {
        try {
            // Valider les données du formulaire (y compris le fichier MP3)
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'audio_file' => 'nullable|mimes:mp3|max:10240', // Vérifiez le fichier MP3 s'il est fourni
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'errors' => $validator->errors(),
                ]);
            }

            // Mettre à jour les champs du Beat
            $beat->title = $request->title;
            $beat->description = $request->description;

            // Si un nouveau fichier MP3 est fourni, procédez au renommage et à l'enregistrement
            if ($request->hasFile('audio_file')) {
                $audioFile = $request->file('audio_file');
                if ($audioFile->isValid()) {
                    // Obtenez le nouveau titre
                    $newTitle = str_replace([' ', 'é'], ['_', 'e'], $beat->title);

                    // Obtenez le nom du fichier actuel
                    $currentFileName = pathinfo($beat->file_path, PATHINFO_FILENAME);

                    $beatmakerName = str_replace([' ', 'é'], ['_', 'e'], $beat->beatmaker_name);

                    // Renommer le fichier MP3 en fonction du nouveau titre
                    $fileName = $this->getUniqueFileName($newTitle, $audioFile->getClientOriginalExtension(), $beatmakerName);

                    // Supprimer l'ancien fichier
                    Storage::disk('public')->delete($beat->file_path);

                    // Enregistrez le nouveau fichier MP3 dans le stockage avec le nom spécifié
                    $audioFilePath = $audioFile->storeAs("beats/{$newTitle}", $fileName, 'public');
                    $beat->file_path = $audioFilePath;
                }
            }

            $beat->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Beat mis à jour avec succès.',
                'beat' => $beat,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur est survenue lors de la mise à jour du Beat.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Beat $beat)
    {
        try {
            // Supprimer le fichier audio du stockage
            Storage::disk('public')->delete($beat->file_path);

            // Supprimer l'enregistrement du Beat
            $beat->delete();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Beat supprimé avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur est survenue lors de la suppression du Beat.',
                'error' => $e->getMessage(),
            ]);
        }
    }


    // Fonction pour obtenir un nom de fichier unique
    private function getUniqueFileName($title, $extension, $beatmakerName)
    {
        $fileName = $title . '.' . $extension;
        $counter = 1;

        // Vérifier l'existence du fichier, ajout de suffixes jusqu'à ce qu'il soit unique
        while (Storage::disk('public')->exists("beats/{$beatmakerName}/$fileName")) {
            $fileName = $title . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $fileName;
    }
}
