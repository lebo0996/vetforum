<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Setting;
use App\Models\Status;
use App\Models\Specialty;
use App\Http\Controllers\UserController;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('forum.index'))->with('success', 'Bienvenido de nuevo.');
        }

        return back()->withErrors(['email' => 'Las credenciales no coinciden.'])->onlyInput('email');
    }

    public function showRegister() { return view('auth.register'); }

    public function register(Request $request)
    {
        $settings = Setting::first();
        if ($settings && !$settings->allow_registrations) {
            return back()->withErrors(['email' => 'El registro público está deshabilitado actualmente.']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:30|unique:users,username|regex:/^[a-zA-Z0-9_.]+$/',
            'email' => 'required|email|unique:users,email',
            'specialty' => 'nullable|string|max:100',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => strtolower($data['username']),
            'email' => $data['email'],
            'specialty' => $data['specialty'] ?? 'Médico Veterinario',
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function showProfile()
    {
        $user = Auth::user();
        $specialties = Specialty::orderBy('name')->pluck('name'); 
        
        return view('auth.profile', compact('user', 'specialties'));
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Validamos todos los campos, incluyendo los nuevos
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:30|regex:/^[a-zA-Z0-9_.]+$/|unique:users,username,' . $user->id,
            'bio' => 'nullable|string|max:1000', // Ampliado para biografías completas
            'banner_color' => 'nullable|string|max:7',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'password' => 'nullable|min:6|confirmed',
            
            // Campos Profesionales y de Directorio
            'professional_status' => 'required|in:estudiante,pasante,titulado,especialista',
            'cedula' => 'nullable|string|max:50',
            'specialty' => 'nullable|string|max:100',
            'specialty_secondary' => 'nullable|string|max:100',
            'clinic_name' => 'nullable|string|max:150',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'whatsapp' => 'nullable|string|max:30',
            'website' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url|max:255',
        ]);

        // 2. Asignamos los campos de texto
        $user->name = $data['name'];
        $user->username = strtolower($data['username']);
        $user->bio = $data['bio'] ?? null;
        
        $user->professional_status = $data['professional_status'];
        $user->cedula = $data['cedula'] ?? null;
        $user->specialty = $data['specialty'] ?? $user->specialty;
        $user->specialty_secondary = $data['specialty_secondary'] ?? null;
        $user->clinic_name = $data['clinic_name'] ?? null;
        $user->city = $data['city'] ?? null;
        $user->state = $data['state'] ?? null;
        $user->country = $data['country'] ?? 'México';
        $user->whatsapp = $data['whatsapp'] ?? null;
        $user->website = $data['website'] ?? null;
        $user->facebook = $data['facebook'] ?? null;
        $user->instagram = $data['instagram'] ?? null;
        $user->linkedin = $data['linkedin'] ?? null;

        if (array_key_exists('banner_color', $data)) {
            $user->banner_color = $data['banner_color'];
        }

        // 3. Manejo de Checkboxes (Si vienen en el form son true, sino false)
        $user->offers_emergencies = $request->has('offers_emergencies');
        $user->home_visits = $request->has('home_visits');

        // 4. Manejo de Imágenes y Contraseñas
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Perfil profesional actualizado correctamente.');
    }
}