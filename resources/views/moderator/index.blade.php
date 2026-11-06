@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">
    
    <!-- Cabecera del Panel -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="bg-amber-100 text-amber-700 text-xs font-black px-2.5 py-0.5 rounded-md uppercase tracking-wider">Moderación</span>
                <span class="text-xs text-slate-400 font-bold">• Comunidad Veterinaria</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900">Panel de Control de Moderadores</h1>
            <p class="text-sm text-slate-500">Supervisa reportes activos, revisa contenido sensible y aplica sanciones a la comunidad.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-slate-50 border border-slate-200 px-4 py-2 rounded-2xl text-right">
                <span class="block text-xs text-slate-400 font-bold uppercase">Tu Rol</span>
                <span class="text-sm font-extrabold text-emerald-600 uppercase">{{ auth()->user()->role }}</span>
            </div>
        </div>
    </div>

    <!-- Alertas de éxito o error -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold flex items-center gap-2 shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl text-sm font-bold flex items-center gap-2 shadow-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-500"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- SECCIÓN DE REPORTES -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h2 class="font-extrabold text-slate-800 flex items-center gap-2">
                <i data-lucide="flag" class="w-5 h-5 text-amber-500"></i>
                Reportes de Contenido y Usuarios
            </h2>
            <span class="text-xs font-bold text-slate-500 bg-slate-200/60 px-2.5 py-1 rounded-xl">
                {{ $reports->count() }} totales
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-400 font-bold">
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Denunciante</th>
                        <th class="px-6 py-4">Reportado</th>
                        <th class="px-6 py-4 min-w-[280px]">Motivo / Evidencia</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($reports as $report)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-medium text-xs">
                                {{ $report->created_at->format('d/m/Y H:i') }}
                            </td>
                            
                            <td class="px-6 py-4">
                                <a href="{{ route('users.public', $report->reporter) }}" target="_blank" class="font-bold text-slate-800 hover:text-emerald-600 transition flex items-center gap-1.5 text-xs">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-400"></i>
                                    {{ $report->reporter->name }}
                                </a>
                            </td>
                            
                            <td class="px-6 py-4">
                                <a href="{{ route('users.public', $report->reported) }}" target="_blank" class="font-bold text-rose-600 hover:text-rose-700 transition flex items-center gap-1.5 text-xs">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-rose-400"></i>
                                    {{ $report->reported->name }}
                                </a>
                            </td>
                            
                            <td class="px-6 py-4 min-w-[250px]">
                                <span class="font-bold text-slate-800 block mb-1 text-xs">{{ $report->reason }}</span>
                                
                                @if($report->status_id && $report->statusPost)
                                    <div class="mt-2 p-3 bg-rose-50 border border-rose-100 rounded-xl text-xs text-slate-700">
                                        <span class="font-bold text-rose-600 mb-1 flex items-center gap-1">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Publicación Denunciada:
                                        </span>
                                        <p class="line-clamp-2 italic">"{{ $report->statusPost->content }}"</p>
                                    </div>
                                @elseif($report->status_id && !$report->statusPost)
                                    <span class="text-[11px] font-bold text-slate-400 italic block mt-1">La publicación ya fue eliminada.</span>
                                @endif

                                @if($report->description)
                                    <p class="text-xs text-slate-500 mt-2 line-clamp-2">
                                        <span class="font-bold text-slate-400">Detalles:</span> "{{ $report->description }}"
                                    </p>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($report->status === 'pendiente')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Pendiente
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check-circle-2" class="w-3 h-3"></i> Resuelto
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @if($report->status === 'pendiente')
                                    <div class="flex items-center justify-end gap-2">
                                        @if($report->status_id && $report->statusPost)
                                            <form action="{{ route('moderator.groups.reports.deleteStatus', $report) }}" method="POST" onsubmit="return confirm('¿Borrar definitivamente esta publicación?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Post
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('moderator.groups.reports.resolve', $report) }}" method="POST" onsubmit="return confirm('¿Marcar este reporte como resuelto?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1" title="Resolver">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-medium italic">Cerrado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                <i data-lucide="shield-check" class="w-8 h-8 mx-auto mb-2 text-emerald-500"></i>
                                No hay reportes pendientes en este momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN DE GESTIÓN Y SANCIONES RÁPIDAS DE USUARIOS -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ search: '' }">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
            <h2 class="font-extrabold text-slate-800 flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5 text-blue-500"></i>
                Control de Usuarios y Sanciones
            </h2>
            <input type="text" x-model="search" placeholder="Buscar usuario por nombre..." class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-emerald-500 w-full sm:w-64 shadow-inner">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-400 font-bold">
                        <th class="px-6 py-4">Usuario</th>
                        <th class="px-6 py-4">Especialidad</th>
                        <th class="px-6 py-4">Rol / Estado</th>
                        <th class="px-6 py-4 text-right">Aplicar Sanción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50/60 transition" x-show="search === '' || '{{ strtolower($u->name) }}'.includes(search.toLowerCase())">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($u->avatar)
                                        <img src="{{ asset('storage/' . $u->avatar) }}" class="w-9 h-9 rounded-full object-cover border border-slate-200">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('users.public', $u) }}" target="_blank" class="font-bold text-slate-800 text-xs hover:text-emerald-600 block">
                                            {{ $u->name }}
                                        </a>
                                        <span class="text-[11px] text-slate-400 font-medium">{{ '@' . $u->username }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-xs font-semibold text-slate-600">
                                {{ $u->specialty ?? 'Médico Veterinario' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-700' : ($u->role === 'moderator' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $u->role }}
                                    </span>

                                    @if(!$u->is_active)
                                        <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-rose-100 text-rose-700">Banido</span>
                                    @elseif($u->suspended_until && \Carbon\Carbon::parse($u->suspended_until)->isFuture())
                                        <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-100 text-amber-700">Suspendido</span>
                                    @else
                                        <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-700">Activo</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @if($u->role !== 'admin' && $u->id !== auth()->id())
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Suspender temporalmente -->
                                        <form action="{{ route('moderator.groups.users.suspend', $u) }}" method="POST" class="flex items-center gap-1">
                                            @csrf
                                            <input type="hidden" name="days" value="3">
                                            <button type="submit" onclick="return confirm('¿Suspender a {{ $u->name }} por 3 días?')" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow-sm" title="Suspender 3 días">
                                                3 días
                                            </button>
                                        </form>

                                        <!-- Ban / Activar -->
                                        <form action="{{ route('moderator.groups.users.ban', $u) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" onclick="return confirm('¿Cambiar estado de baneo de este usuario?')" class="px-3 py-1.5 {{ $u->is_active ? 'bg-rose-100 text-rose-600 hover:bg-rose-600 hover:text-white' : 'bg-emerald-100 text-emerald-600 hover:bg-emerald-600 hover:text-white' }} rounded-xl text-xs font-bold transition shadow-sm">
                                                {{ $u->is_active ? 'Banear' : 'Reactivar' }}
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-300 italic">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection