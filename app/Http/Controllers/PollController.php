<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;

class PollController extends Controller
{
    public function vote(Request $request, PollOption $option)
    {
        $poll = $option->poll;

        // Verificar si el usuario ya votó en esta encuesta
        $existingVote = PollVote::where('poll_id', $poll->id)
                                ->where('user_id', Auth::id())
                                ->first();

        if ($existingVote) {
            return response()->json(['message' => 'Ya has votado en esta encuesta.'], 400);
        }

        // Registrar el voto
        PollVote::create([
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'user_id' => Auth::id(),
        ]);

        // Incrementar contador
        $option->increment('votes_count');

        // Retornar las opciones actualizadas con sus nuevos conteos
        return response()->json([
            'success' => true,
            'options' => $poll->options()->with('votes')->get()
        ]);
    }
}