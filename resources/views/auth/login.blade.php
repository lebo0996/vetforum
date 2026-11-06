@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-14 bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Iniciar Sesión</h1>
    <p class="text-sm text-slate-500 mb-6">Accede con tu cuenta del foro veterinario.</p>

    @if($errors->any())
        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Correo Electrónico</label>
            <input type="email" name="email" value="admin@vetforum.com" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
            <input type="password" name="password" value="12345678" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm">
        </div>
        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
            Entrar al Foro
        </button>
    </form>
</div>
@endsection