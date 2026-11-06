<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request, User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'No puedes reportar tu propio contenido.');
        }

        $request->validate([
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status_id' => 'nullable|exists:statuses,id',
            'post_id' => 'nullable|exists:posts,id',
            'comment_id' => 'nullable|exists:comments,id',
        ]);

        // Evitar duplicados del mismo contenido
        $existingReport = Report::where('reporter_id', Auth::id())
            ->where('reported_id', $user->id)
            ->where('status', 'pendiente')
            ->when($request->status_id, fn($q) => $q->where('status_id', $request->status_id))
            ->when($request->post_id, fn($q) => $q->where('post_id', $request->post_id))
            ->when($request->comment_id, fn($q) => $q->where('comment_id', $request->comment_id))
            ->first();

        if ($existingReport) {
            return back()->with('error', 'Ya tienes un reporte pendiente para este elemento.');
        }

        Report::create([
            'reporter_id' => Auth::id(),
            'reported_id' => $user->id,
            'status_id' => $request->status_id,
            'post_id' => $request->post_id,
            'comment_id' => $request->comment_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pendiente'
        ]);

        return back()->with('success', 'Reporte enviado correctamente. Administración revisará el caso.');
    }
}