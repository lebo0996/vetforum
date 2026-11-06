@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto my-10 px-4">
    
    <!-- Contenedor principal -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xl overflow-hidden min-h-[500px] flex flex-col">
        
        <!-- Cabecera -->
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50 via-white to-slate-50">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Mensajes Privados</h1>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Tus conversaciones clínicas y profesionales con colegas</p>
            </div>
            <div class="bg-emerald-50 text-emerald-700 font-bold text-xs px-3.5 py-1.5 rounded-2xl border border-emerald-100 shadow-sm flex items-center gap-1.5">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>{{ count($conversations) }} Conversaciones</span>
            </div>
        </div>

        <!-- Lista de Conversaciones Compacta -->
        <div class="divide-y divide-slate-100 flex-1">
            @forelse($conversations as $colleague)
                <a href="{{ route('chat.show', $colleague) }}" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50/80 transition group">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="relative shrink-0">
                            @if($colleague->avatar)
                                <img src="{{ asset('storage/' . $colleague->avatar) }}" class="w-11 h-11 rounded-full object-cover border border-slate-200 shadow-sm group-hover:scale-105 transition">
                            @else
                                <div class="w-11 h-11 rounded-full bg-emerald-600 text-white font-bold text-sm flex items-center justify-center shadow-sm group-hover:scale-105 transition">
                                    {{ strtoupper(substr($colleague->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <h3 class="font-extrabold text-slate-900 text-sm truncate group-hover:text-emerald-600 transition">{{ $colleague->name }}</h3>
                                @if($colleague->is_verified)
                                    <i data-lucide="badge-check" class="w-3.5 h-3.5 fill-blue-500 text-white shrink-0"></i>
                                @endif
                            </div>
                            
                            <!-- Último mensaje (Quitamos la especialidad para limpiar la vista) -->
                            @if($colleague->last_message)
                                <p class="text-xs text-slate-500 truncate max-w-md font-medium mt-0.5">
                                    <span class="text-slate-700 font-bold">{{ $colleague->last_message->sender_id === auth()->id() ? 'Tú: ' : '' }}</span>
                                    {{ $colleague->last_message->body }}
                                </p>
                            @else
                                <p class="text-xs text-slate-400 italic mt-0.5">Nueva conversación iniciada</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0 pl-4">
                        @if($colleague->last_message)
                            <span class="text-[11px] text-slate-400 font-medium hidden sm:inline">
                                {{ $colleague->last_message->created_at->diffForHumans(null, true) }}
                            </span>
                        @endif
                        <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-emerald-600 group-hover:text-white text-slate-500 flex items-center justify-center transition shadow-sm">
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-16 text-center text-slate-400 text-sm flex flex-col items-center justify-center m-auto">
                    <div class="bg-slate-50 p-4 rounded-full mb-3 border border-slate-100">
                        <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                    </div>
                    <p class="font-bold text-slate-700 text-base mb-1">Aún no tienes conversaciones abiertas</p>
                    <p class="text-xs text-slate-500 max-w-sm">Visita el perfil de un colega o el muro social para iniciar un chat privado.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection