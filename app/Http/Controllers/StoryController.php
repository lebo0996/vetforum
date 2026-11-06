<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use Illuminate\Support\Facades\Auth;

class StoryController extends Controller
{
    // Guardar una nueva historia
    public function store(Request $request)
    {
        $request->validate([
            // Aceptamos fotos y videos de hasta 10MB
            'media' => 'required|file|mimes:jpeg,png,jpg,webp,mp4,mov|max:10240', 
            'caption' => 'nullable|string|max:255',
        ]);

        $path = $request->file('media')->store('stories', 'public');
        
        // Detectamos automáticamente si es video o imagen
        $mimeType = $request->file('media')->getMimeType();
        $mediaType = str_contains($mimeType, 'video') ? 'video' : 'image';

        Story::create([
            'user_id' => Auth::id(),
            'media_path' => $path,
            'media_type' => $mediaType,
            'caption' => $request->caption,
            'expires_at' => now()->addHours(24), // ¡La magia de la autodestrucción!
        ]);

        return back()->with('success', '¡Historia publicada! Desaparecerá en 24 horas.');
    }

    // Registrar silenciosamente cuando alguien ve una historia
    public function markAsSeen(Story $story)
    {
        // firstOrCreate se asegura de no crear duplicados si el usuario la ve 2 veces
        $story->views()->firstOrCreate([
            'user_id' => Auth::id()
        ]);
        
        return response()->json(['success' => true]);
    }
    // Dar o quitar Like a una historia (vía AJAX/Fetch)
    // Dentro de tu función toggleLike en StoryController.php
    public function toggleLike(Story $story)
    {
        $existingLike = $story->likes()->where('user_id', Auth::id())->first();

        if ($existingLike) {
            $existingLike->delete();
            return response()->json(['status' => 'unliked']);
        } else {
            $story->likes()->create(['user_id' => Auth::id()]);

            // Enviar notificación al dueño de la historia (si no eres tú mismo)
            if ($story->user_id !== Auth::id()) {
                $story->user->notify(new \App\Notifications\GeneralNotification([
                    'message' => Auth::user()->name . ' le dio me gusta a tu historia.',
                    'url' => route('statuses.index'),
                    'icon' => 'heart',
                    'color' => 'rose'
                ]));
            }

            return response()->json(['status' => 'liked']);
        }
    }
}