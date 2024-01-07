<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BeatController extends Controller
{
    public function index()
    {
        $beats = Beat::all();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Bibliothèque de Beats récupérée avec succès.',
            'beats' => $beats,
        ]);
    }
    
    public function beatmakerBeats($beatmakerId)
    {
        $beats = Beat::where('user_id', $beatmakerId)->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Beats du beatmaker récupérés avec succès.',
            'beats' => $beats,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'audio_file' => 'required|mimes:mp3|max:51200', // 50MB maximum
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'errors' => $validator->errors(),
                ]);
            }

            $beat = new Beat();
            $beat->user_id = auth()->user()->id;
            $beat->beatmaker_name = auth()->user()->name;
            $beat->title = $request->title;
            $beat->description = $request->description;

            $audioFile = $request->file('audio_file');

            $fileName = $this->getUniqueFileName($beat->title, $audioFile->getClientOriginalExtension(), $beat->beatmaker_name);
            $audioFilePath = $audioFile->storeAs("beats/{$beat->beatmaker_name}", $fileName, 'public');

            $beat->file_path = $audioFilePath;
            $beat->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Beat enregistré avec succès.',
                'beat' => $beat,
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'enregistrement du Beat.',
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function show(Beat $beat)
    {
        if (!$beat) {
            return response()->json([
                'status_code' => 404,
                'error' => 'Beat non trouvé.',
            ], 404);
        }

        return response()->json([
            'status_code' => 200,
            'data' => $beat,
        ], 200);
    }

    public function update(Request $request, Beat $beat)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'audio_file' => 'nullable|mimes:mp3|max:51200', // 50MB maximum
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'errors' => $validator->errors(),
                ]);
            }

            $beat->title = $request->title;
            $beat->description = $request->description;

            if ($request->hasFile('audio_file')) {
                $audioFile = $request->file('audio_file');

                $fileName = $this->getUniqueFileName($beat->title, $audioFile->getClientOriginalExtension(), $beat->beatmaker_name);
                Storage::disk('public')->delete($beat->file_path);

                $beat->file_path = $audioFile->storeAs("beats/{$beat->beatmaker_name}", $fileName, 'public');
            }

            $beat->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Beat mis à jour avec succès.',
                'beat' => $beat,
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la mise à jour du Beat.',
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function destroy(Beat $beat)
    {
        try {
            Storage::disk('public')->delete($beat->file_path);
            $beat->delete();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Beat supprimé avec succès.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la suppression du Beat.',
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function getUniqueFileName($title, $extension, $beatmakerName)
    {
        $fileName = "{$title}.{$extension}";

        if (Storage::disk('public')->exists("beats/{$beatmakerName}/{$fileName}")) {
            $fileName = "{$title}-" . time() . ".{$extension}";
        }

        return $fileName;
    }
}
