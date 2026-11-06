@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto my-10 px-4 space-y-8">
    
    @auth
        <!-- Encabezado y Filtros -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        <span class="p-2.5 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100 flex items-center justify-center">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </span>
                        Directorio de Colegas
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">Conecta, colabora y encuentra especialistas en tu área.</p>
                </div>

                @if(request('search') || request('specialty'))
                    <a href="{{ route('users.directory') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3.5 py-2 rounded-xl transition w-fit">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Limpiar filtros
                    </a>
                @endif
            </div>

            <form action="{{ route('users.directory') }}" method="GET" class="space-y-4 pt-2">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                    <!-- Búsqueda libre (Nombre, Clínica o Ciudad) -->
                    <div class="sm:col-span-6 relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-4 top-3.5"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre, Clínica, Ciudad o @usuario..." class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>

                    <!-- Selector de Especialidades -->
                    <div class="sm:col-span-4 relative">
                        <i data-lucide="stethoscope" class="w-4 h-4 text-slate-400 absolute left-4 top-3.5"></i>
                        <select name="specialty" class="w-full pl-11 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition appearance-none text-slate-700">
                            <option value="">Todas las especialidades</option>
                            @foreach($specialties as $item)
                                <option value="{{ $item }}" {{ request('specialty') === $item ? 'selected' : '' }}>
                                    {{ $item }}
                                </option>
                            @endforeach
                        </select>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3.5 top-3.5 pointer-events-none"></i>
                    </div>

                    <div class="sm:col-span-2">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-2xl text-sm transition shadow-sm flex items-center justify-center gap-2">
                            <i data-lucide="filter" class="w-4 h-4"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tarjetas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($colleagues as $colleague)
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md hover:border-slate-300 transition group">
                    <div>
                        <div class="h-20 w-full relative opacity-90" @style(['background-color: ' . ($colleague->banner_color ?? '#10b981')])></div>

                        <div class="px-5 pb-4 -mt-10 flex flex-col items-center text-center">
                            <!-- Avatar -->
                            <a href="{{ route('users.public', $colleague) }}" class="relative mb-2">
                                @if($colleague->avatar)
                                    <img src="{{ asset('storage/' . $colleague->avatar) }}" alt="{{ $colleague->name }}" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-white shadow-sm bg-white">
                                @else
                                    <div class="w-20 h-20 rounded-2xl bg-emerald-600 text-white font-black text-2xl flex items-center justify-center ring-4 ring-white shadow-sm">
                                        {{ strtoupper(substr($colleague->name, 0, 1)) }}
                                    </div>
                                @endif

                                @if($colleague->is_verified ?? false)
                                    <span class="absolute -bottom-1 -right-1 bg-white p-0.5 rounded-full shadow-sm" title="Colega Verificado">
                                        <i data-lucide="badge-check" class="w-4 h-4 fill-blue-500 text-white"></i>
                                    </span>
                                @endif
                            </a>

                            <!-- Datos principales -->
                            <div class="w-full space-y-1">
                                <a href="{{ route('users.public', $colleague) }}" class="font-extrabold text-slate-900 text-base hover:text-emerald-600 transition block truncate" title="{{ $colleague->name }}">
                                    {{ $colleague->name }}
                                </a>
                                
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-md border border-emerald-100 uppercase">
                                        {{ $colleague->specialty ?? 'General' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Nuevos datos clínicos -->
                            <div class="mt-3 w-full space-y-1.5 text-left bg-slate-50 rounded-xl p-3 border border-slate-100">
                                @if($colleague->clinic_name)
                                    <p class="text-xs text-slate-700 font-bold flex items-center gap-1.5 truncate" title="{{ $colleague->clinic_name }}">
                                        <i data-lucide="building" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i> {{ $colleague->clinic_name }}
                                    </p>
                                @endif
                                
                                @if($colleague->city)
                                    <p class="text-xs text-slate-500 font-medium flex items-center gap-1.5 truncate">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-400 shrink-0"></i> {{ $colleague->city }}{{ $colleague->state ? ', ' . $colleague->state : '' }}
                                    </p>
                                @endif

                                <!-- Iconos de Servicios (Opcional mantenerlos aquí para informar) -->
                                @if($colleague->offers_emergencies || $colleague->home_visits)
                                    <div class="flex gap-2 pt-1 mt-1 border-t border-slate-200">
                                        @if($colleague->offers_emergencies)
                                            <span title="Urgencias 24/7" class="text-rose-500 flex items-center gap-1 text-[10px] font-bold">
                                                <i data-lucide="siren" class="w-3.5 h-3.5"></i> 24/7
                                            </span>
                                        @endif
                                        @if($colleague->home_visits)
                                            <span title="Servicio a rancho / domicilio" class="text-indigo-500 flex items-center gap-1 text-[10px] font-bold">
                                                <i data-lucide="car" class="w-3.5 h-3.5"></i> Domicilio
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="p-4 pt-0 mt-auto">
                        <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2">
                            <a href="{{ route('users.public', $colleague) }}" class="bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold py-2.5 rounded-xl text-xs transition border border-slate-200/80 text-center">
                                Perfil
                            </a>

                            @if(auth()->id() !== $colleague->id)
                                @php
                                    $isFollowing = auth()->user()->following->contains($colleague->id);
                                @endphp
                                <form action="{{ route('users.follow', $colleague) }}" method="POST" class="w-full">
                                    @csrf
                                    @if($isFollowing)
                                        <button type="submit" class="w-full bg-slate-100 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 text-slate-600 font-bold py-2.5 rounded-xl text-xs transition border border-slate-200 flex items-center justify-center gap-1.5 group/btn">
                                            <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-600 group-hover/btn:hidden"></i>
                                            <i data-lucide="user-minus" class="w-3.5 h-3.5 hidden group-hover/btn:inline text-rose-600"></i>
                                            <span class="group-hover/btn:hidden">Siguiendo</span>
                                            <span class="hidden group-hover/btn:inline">Dejar</span>
                                        </button>
                                    @else
                                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                            <span>Seguir</span>
                                        </button>
                                    @endif
                                </form>
                            @else
                                <span class="bg-slate-100 text-slate-400 font-bold py-2.5 rounded-xl text-xs flex items-center justify-center">
                                    Tú
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-3xl border border-slate-200 p-12 text-center space-y-4 shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mx-auto border border-slate-100">
                        <i data-lucide="user-x" class="w-8 h-8"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-extrabold text-slate-800">No se encontraron colegas</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">No hay resultados para los filtros seleccionados.</p>
                    </div>
                    <div>
                        <a href="{{ route('users.directory') }}" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-4 py-2.5 rounded-xl transition">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Ver todos los colegas
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="pt-4">
            {{ $colleagues->withQueryString()->links() }}
        </div>
    @endauth

    @guest
        <div class="max-w-2xl mx-auto bg-white rounded-3xl border border-slate-200/80 shadow-sm p-10 sm:p-14 text-center my-12 space-y-6">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto shadow-sm border border-emerald-100">
                <i data-lucide="lock" class="w-10 h-10"></i>
            </div>
            <div class="space-y-2">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Directorio Exclusivo para Colegas</h2>
                <p class="text-slate-500 text-sm sm:text-base max-w-md mx-auto leading-relaxed">El listado de médicos veterinarios es un espacio privado. Inicia sesión o regístrate para conectar con colegas de la red.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="{{ route('register') }}" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-8 py-3.5 rounded-2xl transition shadow-sm text-sm">Crear mi cuenta gratis</a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto bg-white hover:bg-slate-50 text-slate-700 font-bold px-8 py-3.5 rounded-2xl transition border border-slate-200 text-sm">Iniciar Sesión</a>
            </div>
        </div>
    @endguest
</div>
@endsection