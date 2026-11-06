@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl" x-data="{ tab: localStorage.getItem('adminTab') || 'settings', modalCat: false }">
    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Panel de Administración</h1>
        <p class="text-slate-500 mt-1">Gestiona la configuración general, categorías, publicaciones, usuarios, insignias y reportes.</p>
    </div>

    <!-- Métricas Rápidas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Categorías Activas</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $categories->count() }}</p>
            </div>
            <i data-lucide="layers" class="w-6 h-6 text-emerald-600"></i>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Publicaciones</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $posts->count() }}</p>
            </div>
            <i data-lucide="file-text" class="w-6 h-6 text-blue-600"></i>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Comentarios</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $comments->count() }}</p>
            </div>
            <i data-lucide="message-square" class="w-6 h-6 text-purple-600"></i>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Usuarios Registrados</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $users->count() }}</p>
            </div>
            <i data-lucide="users" class="w-6 h-6 text-amber-600"></i>
        </div>
        <!-- Nueva métrica de Reportes -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Reportes Pend.</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $reports->where('status', 'pendiente')->count() }}</p>
            </div>
            <i data-lucide="shield-alert" class="w-6 h-6 text-rose-600"></i>
        </div>
    </div>

    <!-- Barra de Pestañas -->
    <div class="flex flex-wrap gap-2 bg-slate-100 p-1.5 rounded-lg border border-slate-200 mb-6 w-fit">
        <button x-on:click="tab = 'settings'; localStorage.setItem('adminTab', 'settings')" x-bind:class="tab === 'settings' ? 'bg-white shadow-sm text-emerald-600 font-semibold' : 'text-slate-600'" class="px-4 py-2 rounded-md text-sm transition">Ajustes</button>
        <button x-on:click="tab = 'categories'; localStorage.setItem('adminTab', 'categories')" x-bind:class="tab === 'categories' ? 'bg-white shadow-sm text-emerald-600 font-semibold' : 'text-slate-600'" class="px-4 py-2 rounded-md text-sm transition">Categorías</button>
        <button x-on:click="tab = 'posts'; localStorage.setItem('adminTab', 'posts')" x-bind:class="tab === 'posts' ? 'bg-white shadow-sm text-emerald-600 font-semibold' : 'text-slate-600'" class="px-4 py-2 rounded-md text-sm transition">Publicaciones</button>
        <button x-on:click="tab = 'users'; localStorage.setItem('adminTab', 'users')" x-bind:class="tab === 'users' ? 'bg-white shadow-sm text-emerald-600 font-semibold' : 'text-slate-600'" class="px-4 py-2 rounded-md text-sm transition">Usuarios</button>
        <button x-on:click="tab = 'badges'; localStorage.setItem('adminTab', 'badges')" x-bind:class="tab === 'badges' ? 'bg-white shadow-sm text-blue-600 font-semibold' : 'text-slate-600'" class="px-4 py-2 rounded-md text-sm transition flex items-center gap-1.5"><i data-lucide="award" class="w-4 h-4"></i> Insignias</button>
        <button x-on:click="tab = 'specialties'; localStorage.setItem('adminTab', 'specialties')" x-bind:class="tab === 'specialties' ? 'bg-white shadow-sm text-emerald-600 font-semibold' : 'text-slate-600'" class="px-4 py-2 rounded-md text-sm transition flex items-center gap-1.5"><i data-lucide="stethoscope" class="w-4 h-4"></i> Especialidades</button>
        
        <!-- PESTAÑA DE REPORTES -->
        <button x-on:click="tab = 'reports'; localStorage.setItem('adminTab', 'reports')" x-bind:class="tab === 'reports' ? 'bg-white shadow-sm text-rose-600 font-semibold' : 'text-slate-600'" class="px-4 py-2 rounded-md text-sm transition flex items-center gap-1.5">
            <i data-lucide="shield-alert" class="w-4 h-4"></i> Reportes
            @if($reports->where('status', 'pendiente')->count() > 0)
                <span class="bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $reports->where('status', 'pendiente')->count() }}</span>
            @endif
        </button>
    </div>

    <!-- Contenedores -->
    <div x-show="tab === 'settings'" style="display: none;">
        @include('admin.partials.settings')
    </div>
    <div x-show="tab === 'categories'" style="display: none;">
        @include('admin.partials.categories')
    </div>
    <div x-show="tab === 'posts'" style="display: none;">
        @include('admin.partials.posts')
    </div>
    <div x-show="tab === 'users'" style="display: none;">
        @include('admin.partials.users')
    </div>
    <div x-show="tab === 'badges'" style="display: none;">
        @include('admin.partials.badges')
    </div>
    <div x-show="tab === 'specialties'" style="display: none;">
        @include('admin.partials.specialties')
    </div>
    <!-- CONTENEDOR DE REPORTES -->
    <div x-show="tab === 'reports'" style="display: none;">
        @include('admin.partials.reports')
    </div>
</div>
@endsection