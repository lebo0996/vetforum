@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 space-y-14">
    
    <!-- HERO PRINCIPAL -->
    <div class="bg-gradient-to-br from-emerald-600 via-teal-700 to-slate-900 rounded-3xl p-8 sm:p-14 md:p-20 text-white shadow-xl relative overflow-hidden text-center md:text-left">
        <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>

        <div class="relative z-10 max-w-3xl space-y-6 mx-auto md:mx-0">
            <span class="inline-flex items-center gap-1.5 bg-white/10 text-emerald-200 border border-emerald-400/30 text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-wider backdrop-blur-sm">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> La Comunidad del Gremio Veterinario
            </span>

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                Bienvenido a <span class="text-emerald-400">Veterinaria Ideal</span>
            </h1>
            
            <p class="text-emerald-50 text-base sm:text-lg font-medium leading-relaxed max-w-2xl">
                {{ $settings->site_description ?? 'La fusión definitiva entre el debate clínico especializado y la colaboración en red para médicos veterinarios y especialistas en ganadería.' }}
            </p>
            
            <div class="flex flex-wrap justify-center md:justify-start gap-4 pt-4">
                <a href="{{ route('forum.index') }}" class="bg-white text-slate-900 hover:bg-slate-100 font-extrabold px-8 py-4 rounded-2xl shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2 text-sm sm:text-base">
                    <i data-lucide="compass" class="w-5 h-5 text-emerald-600"></i> Explorar el Foro Clínico
                </a>
                
                @guest
                    <a href="{{ route('register') }}" class="bg-emerald-700/80 hover:bg-emerald-700 text-white font-extrabold px-8 py-4 rounded-2xl border border-emerald-400/40 backdrop-blur-sm transition transform hover:-translate-y-0.5 flex items-center gap-2 text-sm sm:text-base">
                        <i data-lucide="user-plus" class="w-5 h-5"></i> Unirse a la Red
                    </a>
                @endguest

                @auth
                    <a href="{{ route('statuses.index') }}" class="bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-extrabold px-8 py-4 rounded-2xl shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2 text-sm sm:text-base">
                        <i data-lucide="activity" class="w-5 h-5"></i> Ver el Muro Social
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- SECCIÓN: CÓMO FUNCIONA -->
    <div class="text-center space-y-3 max-w-2xl mx-auto">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Todo lo que necesitas en un solo lugar</h2>
        <p class="text-slate-500 text-sm sm:text-base font-medium">Diseñado por veterinarios para optimizar el intercambio de experiencias médicas y de campo.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-8 rounded-3xl border border-slate-200/60 shadow-sm transition hover:shadow-md hover:border-slate-300 space-y-4 group">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center transition group-hover:scale-110 shadow-sm">
                <i data-lucide="layers" class="w-7 h-7"></i>
            </div>
            <h3 class="font-extrabold text-xl text-slate-900">Foro Clínico Especializado</h3>
            <p class="text-slate-500 text-sm leading-relaxed">
                Debates estructurados por categorías: desde bovinos y sanidad de hato hasta pequeñas especies y farmacología quirúrgica.
            </p>
        </div>

        <div class="bg-white p-8 rounded-3xl border border-slate-200/60 shadow-sm transition hover:shadow-md hover:border-slate-300 space-y-4 group">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center transition group-hover:scale-110 shadow-sm">
                <i data-lucide="activity" class="w-7 h-7"></i>
            </div>
            <h3 class="font-extrabold text-xl text-slate-900">Muro Social & Actividad</h3>
            <p class="text-slate-500 text-sm leading-relaxed">
                Comparte notas rápidas del día a día en tu clínica o rancho, sube fotos de casos al instante e interactúa con los me gusta de tus colegas.
            </p>
        </div>

        <div class="bg-white p-8 rounded-3xl border border-slate-200/60 shadow-sm transition hover:shadow-md hover:border-slate-300 space-y-4 group">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center transition group-hover:scale-110 shadow-sm">
                <i data-lucide="message-circle" class="w-7 h-7"></i>
            </div>
            <h3 class="font-extrabold text-xl text-slate-900">Networking y Chat Privado</h3>
            <p class="text-slate-500 text-sm leading-relaxed">
                Sigue a otros colegas, obtén insignias de reconocimiento profesional y chatea de forma privada.
            </p>
        </div>
    </div>

    <!-- SECCIÓN CTA INFERIOR -->
    @guest
        <div class="bg-slate-900 text-white rounded-3xl p-8 sm:p-12 text-center space-y-6 shadow-xl relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:16px_16px]"></div>
            
            <div class="relative z-10 max-w-xl mx-auto space-y-4">
                <h3 class="text-2xl sm:text-3xl font-black tracking-tight">¿Listo para formar parte de Veterinaria Ideal?</h3>
                <p class="text-slate-400 text-sm sm:text-base">Únete hoy mismo a la red de profesionales que está revolucionando la medicina veterinaria y ganadera.</p>
                <div class="pt-2">
                    <a href="{{ route('register') }}" class="inline-block bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold px-8 py-3.5 rounded-2xl transition shadow-lg shadow-emerald-900/50">
                        Crear mi cuenta gratis
                    </a>
                </div>
            </div>
        </div>
    @endguest

</div>
@endsection