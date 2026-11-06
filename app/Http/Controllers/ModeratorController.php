<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModeratorController extends Controller
{
    public function index()
    {
        // Solo cargamos reportes y usuarios para moderar (sin configuraciones globales ni roles de admin)
        $reports = Report::with(['reporter', 'reported', 'statusPost'])->latest()->get();
        $users = User::latest()->paginate(15);

        return view('moderator.index', compact('reports', 'users'));
    }

    public function resolveReport(Report $report)
    {
        $report->update(['status' => 'resuelto']);
        return back()->with('success', 'Reporte marcado como resuelto.');
    }

    public function deleteReportedStatus(Report $report)
    {
        if ($report->status_id && $report->statusPost) {
            $report->statusPost->delete();
        }
        $report->update(['status' => 'resuelto']);
        return back()->with('success', 'Publicación infractora eliminada.');
    }

    public function suspendUser(Request $request, User $user)
    {
        if ($user->role === 'admin') abort(403, 'No puedes sancionar a un administrador.');
        
        $request->validate(['days' => 'required|integer|min:1']);
        
        $user->update([
            'suspended_until' => now()->addDays($request->days)
        ]);

        return back()->with('success', "El usuario {$user->name} ha sido suspendido por {$request->days} días.");
    }

    public function toggleBanUser(User $user)
    {
        if ($user->role === 'admin') abort(403, 'No puedes sancionar a un administrador.');

        $user->update([
            'is_active' => !$user->is_active,
            'suspended_until' => null
        ]);

        $status = $user->is_active ? 'reactivada' : 'desactivada permanentemente';
        return back()->with('success', "La cuenta de {$user->name} ha sido {$status}.");
    }
}