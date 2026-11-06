<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Badge;
use App\Models\Specialty;
use App\Models\Report; // <-- Importación del modelo de reportes agregada
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') abort(403);

        return view('admin.index', [
            'settings'    => Setting::first(),
            'categories'  => Category::withCount('posts')->latest()->get(),
            'posts'       => Post::with(['user', 'category'])->latest()->get(),
            'comments'    => Comment::with(['user', 'post'])->latest()->get(),
            'users'       => User::latest()->get(),
            'specialties' => Specialty::orderBy('name')->get(),
            'badges'      => Badge::latest()->get(),
            // Agregamos los reportes a tu vista principal para que los pongas en una pestaña nueva
            'reports'     => Report::with(['reporter', 'reported'])
                                ->orderByRaw("FIELD(status, 'pendiente', 'en_revision', 'resuelto')")
                                ->latest()
                                ->get(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $setting = Setting::firstOrCreate([]);

        $request->validate([
            'site_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'site_description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'favicon' => 'nullable|mimes:ico,png|max:512',
        ]);

        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $setting->logo = $request->file('logo')->store('branding', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon) {
                Storage::disk('public')->delete($setting->favicon);
            }
            $setting->favicon = $request->file('favicon')->store('branding', 'public');
        }

        $setting->update([
            'site_name' => $request->site_name,
            'contact_email' => $request->contact_email,
            'site_description' => $request->site_description,
            'allow_registrations' => $request->has('allow_registrations'),
            'allow_guest_posting' => $request->has('allow_guest_posting'),
            'posts_require_approval' => $request->has('posts_require_approval'),
        ]);

        return back()->with('success', 'Configuración general actualizada.');
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
            'color' => 'required|string|max:20',
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'],
            'color' => $data['color'],
        ]);

        return back()->with('success', 'Categoría creada.');
    }

    public function deleteCategory(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Categoría eliminada.');
    }

    public function togglePinPost(Post $post)
    {
        $post->update(['is_pinned' => !$post->is_pinned]);
        return back()->with('success', 'Estado de fijado actualizado.');
    }

    public function deletePost(Post $post)
    {
        $post->delete();
        return back()->with('success', 'Publicación eliminada.');
    }

    public function deleteComment(Comment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Comentario eliminado.');
    }

    public function updateRole(Request $request, User $user)
    {
        $user->update(['role' => $request->role]);
        return back()->with('success', 'Rol de usuario actualizado.');
    }
    
    public function toggleVerify(\App\Models\User $user)
    {
        $user->is_verified = !$user->is_verified;
        $user->save();

        $statusText = $user->is_verified ? 'verificado' : 'desverificado';
        return back()->with('success', "El usuario {$user->name} ahora está {$statusText}.");
    }

    public function storeBadge(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
            'icon' => 'required|string',
            'requirement_type' => 'required|string',
            'requirement_goal' => 'nullable|integer|min:1'
        ]);

        if ($data['requirement_type'] === 'manual') {
            $data['requirement_goal'] = 0;
        }

        \App\Models\Badge::create($data);

        return back()->with('success', '¡Insignia creada con éxito!');
    }

    public function destroyBadge(\App\Models\Badge $badge)
    {
        $badge->users()->detach();
        $badge->delete();
        return back()->with('success', '¡Insignia eliminada correctamente!');
    }

    public function updateBadge(Request $request, \App\Models\Badge $badge)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
            'icon' => 'required|string',
            'requirement_type' => 'required|string',
            'requirement_goal' => 'nullable|integer|min:1'
        ]);

        if ($data['requirement_type'] === 'manual') {
            $data['requirement_goal'] = 0;
        }

        $badge->update($data);

        return back()->with('success', '¡Insignia actualizada correctamente!');
    }

    public function assignBadge(Request $request, \App\Models\User $user)
    {
        $request->validate([
            'badges' => 'nullable|array',
            'badges.*' => 'exists:badges,id'
        ]);

        $user->badges()->sync($request->badges ?? []);

        return back()->with('success', 'Insignias actualizadas para el Dr. ' . $user->name);
    }
    
    public function storeSpecialty(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:specialties,name',
        ]);

        Specialty::create(['name' => $request->name]);

        return back()->with('success', 'Especialidad agregada correctamente.');
    }

    public function deleteSpecialty(Specialty $specialty)
    {
        $specialty->delete();
        return back()->with('success', 'Especialidad eliminada.');
    }

    // ==========================================
    // MÉTODOS PARA GESTIÓN DE REPORTES
    // ==========================================

    public function resolveReport(Report $report)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $report->update(['status' => 'resuelto']);

        return back()->with('success', 'El reporte ha sido marcado como resuelto.');
    }

    public function deleteReport(Report $report)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $report->delete();

        return back()->with('success', 'El reporte ha sido eliminado del registro.');
    }
    // ==========================================
    // MÉTODOS PARA SANCIONES (SISTEMA DE MODERACIÓN)
    // ==========================================

    public function suspendUser(Request $request, User $user)
    {
        if (Auth::user()->role !== 'admin' || $user->id === Auth::id()) abort(403);

        $request->validate(['days' => 'required|integer|min:1']);
        
        $user->update([
            'suspended_until' => now()->addDays($request->days)
        ]);

        return back()->with('success', "El usuario {$user->name} ha sido suspendido por {$request->days} días.");
    }

    public function toggleBanUser(User $user)
    {
        if (Auth::user()->role !== 'admin' || $user->id === Auth::id()) abort(403);

        $user->update([
            'is_active' => !$user->is_active,
            'suspended_until' => null // Limpiamos suspensión si lo baneamos/desbaneamos
        ]);

        $status = $user->is_active ? 'reactivada' : 'desactivada permanentemente';
        return back()->with('success', "La cuenta de {$user->name} ha sido {$status}.");
    }
    public function deleteReportedStatus(Report $report)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        // Si el reporte está ligado a una publicación, la eliminamos
        if ($report->status_id && $report->statusPost) {
            $report->statusPost->delete();
        }

        // Cerramos el caso
        $report->update(['status' => 'resuelto']);

        return back()->with('success', 'Publicación infractora eliminada y reporte resuelto.');
    }
}