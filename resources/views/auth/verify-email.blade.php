@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 sm:px-6">
    <div class="max-w-md w-full bg-white p-8 rounded-3xl shadow-sm border border-slate-200 text-center">
        
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="mail" class="w-8 h-8"></i>
        </div>

        <h2 class="text-2xl font-black text-slate-900 mb-2">Verifica tu correo electrónico</h2>
        <p class="text-sm text-slate-600 mb-6 leading-relaxed">
            ¡Gracias por unirte a la comunidad! Antes de empezar a publicar y conectar con otros colegas, por favor verifica tu dirección de correo haciendo clic en el enlace que te acabamos de enviar.
        </p>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-4 rounded-xl transition shadow-sm mb-4">
                Reenviar correo de verificación
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-slate-500 hover:text-rose-600 font-semibold transition">
                Cerrar sesión por ahora
            </button>
        </form>
    </div>
</div>
@endsection