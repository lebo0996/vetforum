@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-12 bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Crear Cuenta</h1>
    <p class="text-sm text-slate-500 mb-6">Únete a la comunidad profesional veterinaria.</p>

    @if($errors->any())
        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre Completo o Alias</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ej. Dr. Luis Bastidas" class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm">
        </div>
        <!-- Username -->
        <div class="mt-4">
            <label for="username" class="block font-medium text-sm text-slate-700">Nombre de Usuario (Único)</label>
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 font-bold">@</span>
                <input id="username" class="block w-full pl-8 border-slate-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 rounded-md shadow-sm" 
                       type="text" 
                       name="username" 
                       value="{{ old('username') }}" 
                       required 
                       placeholder="ejemplo: l3bo09" />
            </div>
            @error('username')
                <span class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Especialidad / Área</label>
            <input type="text" name="specialty" value="{{ old('specialty') }}" placeholder="Ej. Bovinos y Ganadería, Pequeñas Especies..." class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Correo Electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" required placeholder="tu@correo.com" class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
            <input type="password" name="password" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Confirmar Contraseña</label>
            <input type="password" name="password_confirmation" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm">
        </div>
        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
            Registrarse
        </button>
    </form>
</div>
@endsection