@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    
    <!-- Cabecera del Foro (Más compacta) -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="messages-square" class="w-6 h-6 text-emerald-600"></i> Foro Clínico y Pecuario
            </h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">Discute casos clínicos, comparte investigaciones y conecta con especialistas.</p>
        </div>

        <div>
            @auth
                <a href="{{ route('forum.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md flex items-center gap-2 transition-all w-fit text-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Nuevo Caso o Tema
                </a>
            @else
                <a href="{{ route('login') }}" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-5 py-2.5 rounded-xl shadow-sm flex items-center gap-2 transition-all w-fit text-sm">
                    <i data-lucide="log-in" class="w-4 h-4"></i> Inicia sesión para publicar
                </a>
            @endauth
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 lg:gap-8">
        
        <!-- COLUMNA PRINCIPAL (Buscador + Listado de Publicaciones) -->
        <div class="lg:col-span-3 order-1 lg:order-2 space-y-5">
            
            <!-- Buscador Arriba de los Posts -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <form action="{{ route('forum.index') }}" method="GET">
                    @if(request('categoria'))
                        <input type="hidden" name="categoria" value="{{ request('categoria') }}">
                    @endif
                    <div class="relative group">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3 group-hover:text-emerald-500 transition"></i>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar tema o caso clínico..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-sm">
                    </div>
                </form>
            </div>

            <!-- Listado de Publicaciones -->
            <div class="space-y-4">
                @forelse($posts as $post)
                    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-300 hover:shadow-md transition-all group flex flex-col sm:flex-row gap-4 sm:gap-6">
                        
                        <!-- Contenido Principal del Post -->
                        <div class="flex-1 min-w-0">
                            <!-- Header del Post (Categoría, Fijado, Fecha) -->
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded border uppercase tracking-wider" 
                                      @style(['background-color: '.$post->category->color.'10; color: '.$post->category->color.'; border-color: '.$post->category->color.'40;'])>
                                    {{ $post->category->name }}
                                </span>

                                @if($post->is_pinned)
                                    <span class="bg-amber-50 text-amber-600 border border-amber-200 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1 uppercase tracking-wider">
                                        <i data-lucide="pin" class="w-3 h-3"></i> Fijado
                                    </span>
                                @endif

                                @if($post->image)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-500 border border-slate-200 flex items-center gap-1 uppercase tracking-wider">
                                        <i data-lucide="image" class="w-3 h-3"></i> Adjunto
                                    </span>
                                @endif

                                <span class="text-[11px] text-slate-400 font-medium ml-auto flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i> {{ $post->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <!-- Título y Extracto -->
                            <a href="{{ route('forum.show', $post->slug) }}" class="block mt-1 focus:outline-none">
                                <h2 class="text-lg font-extrabold text-slate-900 group-hover:text-emerald-600 transition truncate sm:whitespace-normal sm:line-clamp-2 leading-tight">
                                    {{ $post->title }}
                                </h2>
                                <p class="text-slate-500 text-sm line-clamp-2 mt-1.5 leading-relaxed">
                                    {{ Str::limit(strip_tags(html_entity_decode($post->content)), 160) }}
                                </p>
                            </a>

                            <!-- Footer Compacto (Autor y Estadísticas) -->
                            <div class="mt-4 flex items-center justify-between">
                                <a href="{{ route('users.public', $post->user) }}" class="flex items-center gap-2 hover:opacity-80 transition">
                                    @if($post->user->avatar)
                                        <img src="{{ asset('storage/' . $post->user->avatar) }}" class="w-6 h-6 rounded-full object-cover border border-slate-200">
                                    @else
                                        <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold border border-emerald-200">
                                            {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1">
                                        {{ $post->user->name }}
                                        @if($post->user->is_verified)
                                            <i data-lucide="badge-check" class="w-3.5 h-3.5 fill-blue-500 text-white" title="Verificado"></i>
                                        @endif
                                    </span>
                                </a>

                                <div class="flex items-center gap-3 text-xs font-bold text-slate-400">
                                    <span class="flex items-center gap-1 hover:text-blue-600 transition" title="Comentarios">
                                        <i data-lucide="message-square" class="w-4 h-4"></i> {{ $post->comments_count }}
                                    </span>
                                    <span class="flex items-center gap-1 hover:text-rose-600 transition" title="Me gusta">
                                        <i data-lucide="heart" class="w-4 h-4"></i> {{ $post->likes_count ?? 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Miniatura Cuadrada -->
                        @if($post->image)
                            <a href="{{ route('forum.show', $post->slug) }}" class="shrink-0 hidden sm:block">
                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="w-24 h-24 object-cover rounded-xl border border-slate-200 group-hover:shadow-md transition">
                            </a>
                        @endif
                        
                    </div>
                @empty
                    <div class="bg-white p-12 rounded-3xl border border-dashed border-slate-300 text-center shadow-sm">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-100">
                            <i data-lucide="microscope" class="w-8 h-8 text-emerald-500"></i>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900">Aún no hay casos clínicos</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Sé el primero en iniciar la discusión científica en esta categoría.</p>
                    </div>
                @endforelse

                <!-- Paginación -->
                <div class="pt-2">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>

        <!-- BARRA LATERAL (SOLO CATEGORÍAS): En celular se mueve abajo, en PC va a la izquierda -->
        <div class="order-2 lg:order-1 space-y-5">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 ml-1">Especialidades</h3>
                <ul class="space-y-1 text-sm font-medium">
                    <li>
                        <a href="{{ route('forum.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ !request('categoria') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>Todas las categorías</span>
                        </a>
                    </li>
                    @foreach($categories as $cat)
                        @php
                            $isActive = request('categoria') === $cat->slug;
                        @endphp
                        <li>
                            <a href="{{ route('forum.index', ['categoria' => $cat->slug]) }}" 
                               class="flex items-center justify-between px-3 py-2 rounded-xl transition border {{ $isActive ? 'shadow-sm' : 'border-transparent hover:bg-slate-50' }}"
                               @style(['background-color: '.$cat->color.'1A; border-color: '.$cat->color.'4D; color: '.$cat->color.'; font-weight: 700;' => $isActive])>
                                
                                <span class="flex items-center gap-2 truncate">
                                    <span class="w-2 h-2 rounded-full shrink-0 shadow-sm" @style(['background-color: ' . $cat->color])></span>
                                    <span class="truncate">{{ $cat->name }}</span>
                                </span>
                                
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isActive ? 'bg-white' : 'bg-slate-100 text-slate-500' }}" 
                                      @style(['color: '.$cat->color.'; border: 1px solid '.$cat->color.'4D;' => $isActive])>
                                    {{ $cat->posts_count }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>
</div>
@endsection