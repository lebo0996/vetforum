@extends('layouts.app')

@section('content')
<!-- Contenedor general del perfil -->
<div class="max-w-7xl mx-auto my-10 px-4 sm:px-6 space-y-6 lg:space-y-0 lg:flex lg:gap-8" x-data="{ 
    openComments: {},
    viewerOpen: false,
    currentStories: [],
    currentIndex: 0,
    isOwner: false,
    progress: 0,
    timer: null,
    
    openViewer(stories, ownerFlag) {
        this.currentStories = stories;
        this.currentIndex = 0;
        this.isOwner = ownerFlag;
        this.viewerOpen = true;
        document.body.style.overflow = 'hidden'; 
        this.markAsSeen(this.currentStories[0].id);
        this.startTimer();
    },
    closeViewer() {
        this.viewerOpen = false;
        document.body.style.overflow = 'auto';
        this.clearTimer();
    },
    nextStory() {
        if (this.currentIndex < this.currentStories.length - 1) {
            this.currentIndex++;
            this.markAsSeen(this.currentStories[this.currentIndex].id);
            this.startTimer();
        } else {
            this.closeViewer();
        }
    },
    prevStory() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.startTimer();
        } else {
            this.startTimer();
        }
    },
    startTimer() {
        this.clearTimer();
        const duration = 5000; 
        const interval = 50; 
        const step = (interval / duration) * 100;
        
        this.timer = setInterval(() => {
            this.progress += step;
            if (this.progress >= 100) {
                this.nextStory();
            }
        }, interval);
    },
    clearTimer() {
        if (this.timer) clearInterval(this.timer);
        this.progress = 0;
    },
    markAsSeen(storyId) {
        if(this.isOwner) return; 
        fetch(`/historias/${storyId}/vistas`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
    },
    toggleLike(storyId) {
        let story = this.currentStories[this.currentIndex];
        let miId = {{ auth()->id() ?? 0 }};
        if(!miId) return;
        
        let likeIndex = story.likes.findIndex(l => l.user_id === miId);
        if (likeIndex !== -1) {
            story.likes.splice(likeIndex, 1);
        } else {
            story.likes.push({ user_id: miId });
        }

        fetch(`/historias/${storyId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
    }
}">
    
    <!-- ========================================== -->
    <!-- COLUMNA IZQUIERDA: PERFIL, ESTADÍSTICAS Y CLÍNICA -->
    <!-- ========================================== -->
    <div class="lg:w-1/3 flex flex-col gap-6">
        
        <!-- Tarjeta de Perfil Principal -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            
            <div class="h-32 relative" @style(["background-color: " . ($user->banner_color ?? '#059669')])>
                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
            </div>
            
            <div class="px-6 pb-6 relative">
                <div class="flex justify-center -mt-16 mb-4 relative z-10">
                    @php
                        $userStories = \App\Models\Story::with(['user', 'views', 'likes'])
                            ->where('user_id', $user->id)
                            ->where('expires_at', '>', now())
                            ->oldest()
                            ->get();

                        if ($userStories->count() > 0) {
                            $userStories->each(function($story) {
                                $story->time_ago = $story->created_at->diffForHumans();
                            });
                        }

                        $tieneHistorias = $userStories->count() > 0;
                        $isOwner = auth()->check() && auth()->id() === $user->id;

                        $historiasNuevas = $tieneHistorias && auth()->check() 
                            ? !$userStories->every(fn($s) => $s->views->contains('user_id', auth()->id())) 
                            : false;

                        $claseAro = $tieneHistorias 
                            ? ($historiasNuevas ? 'border-emerald-500' : 'border-slate-300') 
                            : 'border-white';
                    @endphp
                    
                    <div class="relative {{ $tieneHistorias ? 'cursor-pointer hover:scale-105 transition-transform duration-300 group' : 'cursor-default' }}"
                         @if($tieneHistorias)
                             @click='openViewer(@json($userStories), @json($isOwner))'
                         @endif>
                        
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-32 h-32 rounded-full object-cover shadow-md bg-white border-4 p-1 {{ $claseAro }}">
                        @else
                            <div class="w-32 h-32 rounded-full bg-emerald-600 text-white font-bold text-4xl flex items-center justify-center shadow-md border-4 p-1 {{ $claseAro }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        
                        @if($tieneHistorias && $historiasNuevas)
                            <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 bg-emerald-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full border-2 border-white shadow-sm">
                                NUEVA
                            </div>
                        @endif
                    </div>
                </div>

                <div class="text-center space-y-1.5">
                    <!-- NOMBRE (Sin el icono aquí) -->
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <h1 class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $user->name }}</h1>
                    </div>
                    
                    <!-- @USERNAME + INSIGNIA DE VERIFICADO -->
                    <div class="flex items-center justify-center gap-1.5">
                        <p class="text-base font-bold text-slate-500">
                            {{ '@' . ($user->username ?? 'usuario') }}
                        </p>
                        @if($user->is_verified)
                            <span title="Médico Verificado" class="shrink-0 flex items-center">
                                <i data-lucide="badge-check" class="w-5 h-5 fill-blue-500 text-white"></i>
                            </span>
                        @endif
                    </div>

                    <!-- Estatus Profesional Badge -->
                    <div class="flex justify-center mt-1">
                        @if($user->professional_status === 'estudiante')
                            <span class="bg-amber-100 text-amber-700 text-[10px] font-black px-2 py-0.5 rounded-md border border-amber-200 tracking-wider uppercase">Estudiante MVZ</span>
                        @elseif($user->professional_status === 'pasante')
                            <span class="bg-blue-100 text-blue-700 text-[10px] font-black px-2 py-0.5 rounded-md border border-blue-200 tracking-wider uppercase">Pasante</span>
                        @elseif($user->professional_status === 'titulado')
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded-md border border-emerald-200 tracking-wider uppercase">Médico Titulado</span>
                        @elseif($user->professional_status === 'especialista')
                            <span class="bg-purple-100 text-purple-700 text-[10px] font-black px-2 py-0.5 rounded-md border border-purple-200 tracking-wider uppercase">Especialista</span>
                        @endif
                    </div>

                    <p class="text-sm font-semibold text-emerald-600 mt-2">{{ $user->specialty?->name ?? $user->specialty ?? 'Médico Veterinario' }}</p>
                    
                    @if($user->bio)
                        <p class="text-xs text-slate-600 mt-3 px-2 italic">"{{ $user->bio }}"</p>
                    @endif

                    <div class="flex flex-wrap justify-center gap-1.5 pt-3 pb-2">
                        @forelse($user->badges as $badge)
                            <div class="inline-flex items-center gap-1 bg-{{ $badge->color }}-100 text-{{ $badge->color }}-700 border border-{{ $badge->color }}-200 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider shadow-sm cursor-help" title="{{ $badge->name }}">
                                <i data-lucide="{{ $badge->icon }}" class="w-3.5 h-3.5"></i>
                                {{ $badge->name }}
                            </div>
                        @empty
                            <span class="text-[10px] text-slate-300 font-medium italic border border-dashed border-slate-200 px-2 py-1 rounded">Sin insignias obtenidas</span>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 bg-slate-50/80 rounded-2xl p-4 flex items-center justify-center gap-8 text-sm border border-slate-100 shadow-inner">
                    <div class="text-center">
                        <span class="block font-black text-xl text-slate-800">{{ $user->followers->count() }}</span>
                        <span class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">Seguidores</span>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="text-center">
                        <span class="block font-black text-xl text-slate-800">{{ $user->following->count() }}</span>
                        <span class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">Siguiendo</span>
                    </div>
                </div>

                <!-- Botones de Acción / Mensaje / WhatsApp -->
                <div class="mt-5 flex flex-col gap-2">
                    @auth
                        @if(auth()->id() === $user->id)
                            <a href="{{ route('profile.show') }}" class="w-full bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2 shadow-sm border border-slate-200">
                                <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i> Editar mi Perfil
                            </a>
                        @else
                            @php
                                $isFollowing = auth()->user()->following->contains($user->id);
                                $mutualFollow = $isFollowing && $user->following->contains(auth()->id());
                            @endphp
                            
                            <!-- Botón WhatsApp -->
                            @if($user->whatsapp)
                                <a href="https://wa.me/52{{ preg_replace('/[^0-9]/', '', $user->whatsapp) }}" target="_blank" class="w-full bg-[#25D366] hover:bg-[#20b958] text-white text-sm font-bold px-4 py-2.5 rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i> Contactar por WhatsApp
                                </a>
                            @endif
                            
                            <div class="flex gap-2">
                                <form action="{{ route('users.follow', $user) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full text-sm font-bold px-4 py-2.5 rounded-xl transition shadow-sm flex items-center justify-center gap-2 {{ $isFollowing ? 'bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                        <i data-lucide="{{ $isFollowing ? 'user-check' : 'user-plus' }}" class="w-4 h-4"></i>
                                        {{ $isFollowing ? 'Siguiendo' : 'Seguir' }}
                                    </button>
                                </form>

                                @if($mutualFollow)
                                    <a href="{{ route('chat.show', $user) }}" class="flex-1 bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 text-sm font-bold px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2 shadow-sm">
                                        <i data-lucide="mail" class="w-4 h-4 text-emerald-600"></i> Mensaje
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
            
            <div class="grid grid-cols-3 border-t border-slate-100 bg-slate-50 divide-x divide-slate-100 text-center py-4 px-2">
                <div>
                    <span class="block text-lg font-black text-slate-800">{{ $user->statuses->count() }}</span>
                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">Posts</span>
                </div>
                <div>
                    <span class="block text-lg font-black text-rose-600">{{ $totalLikesReceived ?? 0 }}</span>
                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">Likes</span>
                </div>
                <div>
                    <span class="block text-lg font-black text-blue-600">{{ $totalCommentsReceived ?? 0 }}</span>
                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">Respuestas</span>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- NUEVA TARJETA: INFORMACIÓN PROFESIONAL Y CLÍNICA -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden sticky top-20 mt-6 lg:mt-0">
            <div class="bg-slate-50 border-b border-slate-100 px-6 py-4">
                <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                    <i data-lucide="briefcase" class="w-4 h-4 text-emerald-600"></i> Perfil Clínico
                </h3>
            </div>
            <div class="p-6 space-y-5">
                
                @if($user->clinic_name)
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Clínica / Centro de Trabajo</span>
                        <span class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="building" class="w-4 h-4 text-slate-400"></i> {{ $user->clinic_name }}
                        </span>
                    </div>
                @endif

                @if($user->city || $user->state)
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Ubicación</span>
                        <span class="text-sm font-bold text-slate-700 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-rose-500"></i> 
                            {{ $user->city ? $user->city . ',' : '' }} {{ $user->state }}
                        </span>
                    </div>
                @endif

                @if($user->specialty_secondary)
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Especialidad Secundaria</span>
                        <span class="text-sm font-medium text-slate-700">{{ $user->specialty_secondary }}</span>
                    </div>
                @endif

                @if($user->professional_status !== 'estudiante' && $user->cedula)
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Cédula Profesional</span>
                        <span class="text-sm font-mono font-medium text-slate-700 bg-slate-100 px-2 py-1 rounded border border-slate-200">{{ $user->cedula }}</span>
                    </div>
                @endif

                <!-- Servicios (Badges) -->
                @if($user->offers_emergencies || $user->home_visits)
                    <div class="pt-2 border-t border-slate-100">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Servicios Disponibles</span>
                        <div class="flex flex-wrap gap-2">
                            @if($user->offers_emergencies)
                                <span class="bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-black px-2 py-1 rounded-lg uppercase tracking-wider flex items-center gap-1">
                                    <i data-lucide="siren" class="w-3 h-3"></i> Urgencias 24/7
                                </span>
                            @endif
                            @if($user->home_visits)
                                <span class="bg-indigo-50 text-indigo-600 border border-indigo-200 text-[10px] font-black px-2 py-1 rounded-lg uppercase tracking-wider flex items-center gap-1">
                                    <i data-lucide="car" class="w-3 h-3"></i> Servicio a Rancho/Domicilio
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Redes Sociales Adicionales -->
                @if($user->facebook || $user->instagram || $user->website || $user->linkedin)
                    <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
                        @if($user->website)
                            <a href="{{ $user->website }}" target="_blank" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition shadow-sm" title="Sitio Web">
                                <i data-lucide="globe" class="w-4 h-4"></i>
                            </a>
                        @endif
                        @if($user->facebook)
                            <a href="{{ $user->facebook }}" target="_blank" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition shadow-sm" title="Facebook">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                            </a>
                        @endif
                        @if($user->instagram)
                            <a href="https://instagram.com/{{ str_replace('@', '', $user->instagram) }}" target="_blank" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:text-rose-500 hover:bg-rose-50 transition shadow-sm" title="Instagram">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line></svg>
                            </a>
                        @endif
                        @if($user->linkedin)
                            <a href="{{ $user->linkedin }}" target="_blank" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:text-blue-700 hover:bg-blue-50 transition shadow-sm" title="LinkedIn">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect width="4" height="12" x="2" y="9"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                            </a>
                        @endif
                    </div>
                @endif

            </div>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- COLUMNA DERECHA: MURO DE ACTIVIDAD         -->
    <!-- ========================================== -->
    <div class="lg:w-2/3 flex flex-col gap-6">
        
        <!-- Formulario para publicar con Encuesta -->
        @auth
            @if(auth()->id() === $user->id)
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/60 shadow-sm relative overflow-hidden" x-data="{ showPollCreator: false }">
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                    
                    <form action="{{ route('statuses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="flex items-start gap-4">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover ring-2 ring-slate-100 shrink-0 shadow-sm">
                            @else
                                <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold text-lg flex items-center justify-center shrink-0 shadow-sm">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <textarea name="content" rows="3" required placeholder="¿Tienes algún caso clínico o novedad, {{ auth()->user()->name }}?" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 resize-none transition shadow-inner">{{ old('content') }}</textarea>
                        </div>
                        
                        <!-- Creador de Encuestas -->
                        <div x-show="showPollCreator" style="display: none;" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3 ml-16" x-transition>
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-emerald-600"></i> Crear Encuesta
                                </span>
                                <button type="button" @click="showPollCreator = false" class="text-slate-400 hover:text-rose-500 text-xs font-bold">Cancelar</button>
                            </div>
                            <input type="text" name="poll_question" placeholder="Pregunta de la encuesta (ej. ¿Cuál es tu método de diagnóstico preferido?)" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
                            
                            <div class="space-y-2">
                                <input type="text" name="poll_options[]" placeholder="Opción 1" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-emerald-500">
                                <input type="text" name="poll_options[]" placeholder="Opción 2" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-emerald-500">
                                <input type="text" name="poll_options[]" placeholder="Opción 3 (Opcional)" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-emerald-500">
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 border-t border-slate-100 ml-16">
                            <div class="flex items-center gap-2">
                                <label class="cursor-pointer flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm">
                                    <i data-lucide="image" class="w-4 h-4 text-emerald-600"></i>
                                    <span>Adjuntar imagen</span>
                                    <input type="file" name="image" accept="image/*" class="hidden">
                                </label>

                                <button type="button" @click="showPollCreator = !showPollCreator" class="flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm">
                                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-emerald-600"></i>
                                    <span>Encuesta</span>
                                </button>
                            </div>

                            <button type="submit" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 text-white font-extrabold px-6 py-2.5 rounded-xl text-sm transition shadow-sm flex justify-center items-center gap-2">
                                Publicar nota <i data-lucide="send" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        @endauth

        <!-- Lista del Muro -->
        <div class="space-y-6">
            <h2 class="text-xl font-black text-slate-900 flex items-center gap-2 pl-1">
                <i data-lucide="activity" class="w-6 h-6 text-emerald-500"></i> Actividad Reciente
            </h2>

            @forelse($user->statuses()->with(['poll.options.votes', 'statusLikes', 'comments.user', 'repost.user'])->latest()->get() as $status)
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden transition hover:shadow-md">
                    
                    <!-- Cabecera de la publicación -->
                    <div class="p-5 sm:p-6 flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover border border-slate-200 shadow-sm">
                            @else
                                <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold text-lg flex items-center justify-center shadow-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="font-extrabold text-slate-900 text-base leading-none">{{ $user->name }}</span>
                                    
                                    @if($user->is_verified)
                                        <i data-lucide="badge-check" class="w-4 h-4 fill-blue-500 text-white"></i>
                                    @endif

                                    <div class="flex items-center gap-1 ml-1">
                                        @foreach($user->badges as $badge)
                                            <div class="group relative flex items-center justify-center w-5 h-5 rounded-full bg-{{ $badge->color }}-100 text-{{ $badge->color }}-600 cursor-help border border-{{ $badge->color }}-200">
                                                <i data-lucide="{{ $badge->icon }}" class="w-3 h-3"></i>
                                                <span class="absolute bottom-full mb-1 hidden group-hover:block w-max bg-slate-800 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg z-10">{{ $badge->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 font-medium flex items-center gap-1 mt-1">
                                    <i data-lucide="clock" class="w-3 h-3"></i> {{ $status->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <!-- Acciones de la publicación -->
                        <div class="flex items-center gap-2">
                            @auth
                                @if($status->user_id === auth()->id() || auth()->user()->role === 'admin')
                                    <form action="{{ route('statuses.destroy', $status) }}" method="POST" onsubmit="return confirm('¿Eliminar esta publicación?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-slate-300 hover:text-red-500 hover:bg-red-50 p-2 rounded-xl transition" title="Eliminar publicación">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- Contenido de la Publicación -->
                    <div class="px-5 sm:px-6 pb-5 space-y-3">
                        @if($status->repost_id && $status->repost)
                            @if($status->content && $status->content !== 'Compartió esta nota con la comunidad.')
                                <p class="text-slate-700 text-sm sm:text-base whitespace-pre-line leading-relaxed">{{ $status->content }}</p>
                            @endif

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs">
                                <div class="flex items-center gap-1.5 text-slate-500 font-semibold mb-1">
                                    <i data-lucide="repeat-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>Republicado de 
                                        @if($status->repost->user)
                                            <a href="{{ route('users.public', $status->repost->user) }}" class="text-emerald-600 hover:underline font-bold">{{ $status->repost->user->name }}</a>
                                        @else
                                            <span class="text-slate-700">Usuario Desconocido</span>
                                        @endif
                                    </span>
                                </div>
                                <p class="text-slate-600 line-clamp-2">{{ $status->repost?->content }}</p>
                            </div>
                        @else
                            <p class="text-slate-700 text-sm sm:text-base whitespace-pre-line leading-relaxed">{{ $status->content }}</p>
                        @endif
                    </div>

                    @if($status->image)
                        <div class="bg-slate-50 border-t border-b border-slate-100">
                            <a href="{{ asset('storage/' . $status->image) }}" target="_blank">
                                <img src="{{ asset('storage/' . $status->image) }}" alt="Imagen adjunta" class="w-full max-h-[28rem] object-cover">
                            </a>
                        </div>
                    @endif

                    <!-- ========================================== -->
                    <!-- ENCUESTA (INTERACTIVA)                     -->
                    <!-- ========================================== -->
                    @if($status->poll)
                        <div class="px-5 sm:px-6 pb-4" x-data='{
                            poll: @json($status->poll),
                            voted: {{ $status->poll->votes->where("user_id", auth()->id())->count() > 0 ? "true" : "false" }},
                            userVotedOptionId: {{ optional($status->poll->votes->where("user_id", auth()->id())->first())->poll_option_id ?? "null" }},
                            totalVotes: {{ $status->poll->options->sum("votes_count") }},
                            vote(optionId) {
                                if (this.voted) return;
                                fetch(`/poll-options/${optionId}/vote`, {
                                    method: "POST",
                                    headers: {
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                        "Content-Type": "application/json",
                                        "Accept": "application/json"
                                    }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        this.poll.options = data.options;
                                        this.totalVotes = this.poll.options.reduce((acc, opt) => acc + opt.votes_count, 0);
                                        this.voted = true;
                                        this.userVotedOptionId = optionId;
                                    }
                                });
                            }
                        }'>
                            <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 space-y-3">
                                <h4 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-emerald-600"></i>
                                    <span x-text="poll.question"></span>
                                </h4>
                                
                                <div class="space-y-2">
                                    <template x-for="option in poll.options" :key="option.id">
                                        <button @click="vote(option.id)" 
                                                class="w-full text-left p-3 rounded-xl border transition relative overflow-hidden flex items-center justify-between text-sm group"
                                                :class="{
                                                    'border-emerald-500 bg-emerald-50/60 font-bold': userVotedOptionId === option.id,
                                                    'border-slate-200 bg-white hover:border-emerald-300': userVotedOptionId !== option.id,
                                                    'cursor-default': voted
                                                }">
                                            <div class="absolute inset-y-0 left-0 bg-emerald-100/70 transition-all duration-500 z-0"
                                                 :style="`width: ${totalVotes > 0 ? (option.votes_count / totalVotes) * 100 : 0}%`"></div>
                                            
                                            <span class="relative z-10 text-slate-700 flex items-center gap-2">
                                                <span x-text="option.option_text"></span>
                                                <span x-show="userVotedOptionId === option.id" class="text-emerald-600 text-xs font-bold">(Tu voto)</span>
                                            </span>
                                            
                                            <span class="relative z-10 text-xs font-black text-slate-500" x-text="totalVotes > 0 ? Math.round((option.votes_count / totalVotes) * 100) + '%' : '0%'"></span>
                                        </button>
                                    </template>
                                </div>
                                
                                <div class="flex justify-between items-center pt-1 text-[11px] text-slate-400 font-medium">
                                    <span x-text="voted ? 'Gracias por participar' : 'Haz clic en una opción para votar'"></span>
                                    <span x-text="totalVotes + ' votos totales'"></span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Interacciones -->
                    <div class="px-5 sm:px-6 py-3 border-t border-slate-100 bg-slate-50/30 flex items-center gap-6 text-sm font-bold text-slate-500">
                        @auth
                            <form action="{{ route('statuses.like', $status) }}" method="POST">
                                @csrf
                                @php $hasLiked = $status->statusLikes->where('user_id', auth()->id())->count() > 0; @endphp
                                <button type="submit" class="flex items-center gap-2 hover:text-rose-600 transition {{ $hasLiked ? 'text-rose-600' : '' }}">
                                    <i data-lucide="heart" class="w-5 h-5 {{ $hasLiked ? 'fill-rose-500 text-rose-500' : '' }}"></i>
                                    <span>{{ $status->statusLikes->count() }}</span>
                                </button>
                            </form>
                        @else
                            <div class="flex items-center gap-2 text-slate-400">
                                <i data-lucide="heart" class="w-5 h-5"></i>
                                <span>{{ $status->statusLikes->count() }}</span>
                            </div>
                        @endauth

                        <button @click="openComments[{{ $status->id }}] = !openComments[{{ $status->id }}]" class="flex items-center gap-2 hover:text-blue-600 transition">
                            <i data-lucide="message-square" class="w-5 h-5"></i>
                            <span>{{ $status->comments->count() }}</span>
                        </button>

                        @auth
                            <form action="{{ route('statuses.repost', $status) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center gap-1.5 hover:text-emerald-600 transition" title="Republicar en tu muro">
                                    <i data-lucide="repeat-2" class="w-5 h-5"></i>
                                    <span>{{ $status->reposts_count ?? 0 }}</span>
                                </button>
                            </form>
                        @endauth
                    </div>

                    <!-- Comentarios -->
                    <div x-show="openComments[{{ $status->id }}]" class="border-t border-slate-100 bg-white" style="display: none;">
                        @if($status->comments->count() > 0)
                            <div class="p-5 sm:px-6 space-y-4 max-h-80 overflow-y-auto">
                                @foreach($status->comments as $comment)
                                    <div class="flex gap-3">
                                        @if($comment->user?->avatar)
                                            <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover shrink-0">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($comment->user?->name ?? 'U', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="bg-slate-50 border border-slate-100 rounded-2xl rounded-tl-none p-3.5 w-full">
                                            <div class="flex items-center justify-between mb-1">
                                                <a href="{{ route('users.public', $comment->user) }}" class="font-bold text-slate-800 text-xs hover:text-emerald-600 transition flex items-center gap-1">
                                                    {{ $comment->user?->name ?? 'Usuario Anónimo' }}
                                                    @if($comment->user?->is_verified)
                                                        <i data-lucide="badge-check" class="w-3.5 h-3.5 fill-blue-500 text-white"></i>
                                                    @endif
                                                </a>
                                                <span class="text-[10px] text-slate-400 font-medium">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-slate-600 text-xs leading-relaxed">{{ $comment->content }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @auth
                            <form action="{{ route('statuses.comment', $status) }}" method="POST" class="p-4 sm:px-6 border-t border-slate-100 flex gap-3 bg-slate-50/50 items-center">
                                @csrf
                                <input type="text" name="content" required placeholder="Escribe un comentario..." class="flex-1 bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-sm">
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white p-2.5 rounded-xl transition shadow-sm">
                                    <i data-lucide="send" class="w-5 h-5"></i>
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-16 text-center text-slate-500 shadow-sm flex flex-col items-center justify-center">
                    <div class="bg-slate-50 p-4 rounded-full mb-4">
                        <i data-lucide="inbox" class="w-8 h-8 text-slate-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700 mb-1">Sin publicaciones</h3>
                    <p class="text-sm">Este médico aún no ha compartido nada en su muro.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================== -->
    <!-- VISOR DE HISTORIAS                         -->
    <!-- ========================================== -->
    <div x-show="viewerOpen" class="fixed inset-0 z-[150] flex items-center justify-center bg-black/95 backdrop-blur-sm" style="display: none;" x-transition.opacity>
        
        <button @click="closeViewer()" class="absolute top-4 right-4 md:top-6 md:right-8 text-white/70 hover:text-white z-[160] transition-colors p-2">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <template x-if="currentStories.length > 0">
            <div class="relative w-full max-w-md h-[100dvh] sm:h-[85vh] sm:rounded-[2rem] bg-gray-900 overflow-hidden flex flex-col shadow-2xl shadow-emerald-500/10">
                
                <div class="absolute top-2 left-0 w-full px-2 flex gap-1 z-20">
                    <template x-for="(s, index) in currentStories" :key="index">
                        <div class="h-1 flex-1 rounded-full bg-white/30 overflow-hidden backdrop-blur-sm">
                            <div class="h-full bg-white shadow-[0_0_5px_rgba(255,255,255,0.8)]" 
                                 :style="index === currentIndex ? `width: ${progress}%` : (index < currentIndex ? 'width: 100%' : 'width: 0%')">
                            </div>
                        </div>
                    </template>
                </div>

                <div class="absolute top-0 left-0 w-full pt-6 pb-4 px-4 flex items-center gap-3 bg-gradient-to-b from-black/80 to-transparent z-10 text-white">
                    <img :src="currentStories[0].user && currentStories[0].user.avatar ? '/storage/' + currentStories[0].user.avatar : 'https://ui-avatars.com/api/?name=U&background=10b981&color=fff'" class="w-10 h-10 rounded-full border border-white/20 shadow-sm">
                    <div>
                        <p class="font-bold text-sm leading-tight" x-text="currentStories[0].user ? currentStories[0].user.name : 'Usuario'"></p>
                        <p class="text-[10px] text-white/70 font-medium" x-text="currentStories[currentIndex].time_ago"></p>
                    </div>
                </div>

                <div class="absolute inset-y-0 left-0 w-1/3 z-20 cursor-pointer" @click="prevStory()"></div>
                <div class="absolute inset-y-0 right-0 w-1/3 z-20 cursor-pointer" @click="nextStory()"></div>

                <div class="flex-1 flex items-center justify-center relative bg-black">
                    <template x-if="currentStories[currentIndex].media_type === 'image'">
                        <img :src="'/storage/' + currentStories[currentIndex].media_path" class="w-full h-full object-contain">
                    </template>
                    <template x-if="currentStories[currentIndex].media_type === 'video'">
                        <video :src="'/storage/' + currentStories[currentIndex].media_path" autoplay muted loop class="w-full h-full object-cover"></video>
                    </template>
                </div>

                <template x-if="currentStories[currentIndex].caption">
                    <div class="absolute bottom-16 w-full text-center px-6 z-10 pointer-events-none">
                        <span class="bg-black/60 text-white px-4 py-2 rounded-xl text-sm backdrop-blur-md inline-block max-w-full truncate shadow-lg" x-text="currentStories[currentIndex].caption"></span>
                    </div>
                </template>

                <template x-if="!isOwner">
                    <div class="absolute bottom-6 right-4 z-30 flex flex-col items-center gap-1">
                        <button @click.stop="toggleLike(currentStories[currentIndex].id)" class="p-2.5 bg-black/40 backdrop-blur-md rounded-full hover:bg-black/60 transition group shadow-lg active:scale-90">
                            <svg class="w-7 h-7 transition-all duration-300" 
                                 :class="currentStories[currentIndex].likes && currentStories[currentIndex].likes.some(l => l.user_id === {{ auth()->id() ?? 0 }}) ? 'text-rose-500 fill-rose-500 scale-110' : 'text-white group-hover:text-rose-400'" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </button>
                    </div>
                </template>

                <template x-if="isOwner">
                    <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-black/80 to-transparent z-30" x-data="{ showViewers: false }">
                        <div class="flex justify-between items-center w-full max-w-sm mx-auto">
                            
                            <div class="text-white text-xs font-bold flex items-center gap-1.5 bg-black/40 px-3 py-1.5 rounded-full backdrop-blur-sm cursor-default">
                                <svg class="w-4 h-4 text-rose-500 fill-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                <span x-text="currentStories[currentIndex].likes ? currentStories[currentIndex].likes.length : 0"></span>
                            </div>

                            <button @click="showViewers = !showViewers" class="text-white text-xs font-bold flex items-center gap-1.5 hover:text-emerald-400 transition bg-black/40 px-3 py-1.5 rounded-full backdrop-blur-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <span x-text="currentStories[currentIndex].views ? currentStories[currentIndex].views.length : 0"></span> vistas
                            </button>
                            
                            <div class="w-[60px]"></div>
                        </div>

                        <div x-show="showViewers" @click.away="showViewers = false" x-transition class="absolute bottom-full mb-2 left-4 right-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl max-h-60 overflow-y-auto p-2" style="display: none;">
                            <template x-if="!currentStories[currentIndex].views || currentStories[currentIndex].views.length === 0">
                                <p class="text-white/60 text-xs p-4 text-center font-medium">Nadie ha visto esta historia aún.</p>
                            </template>
                            <template x-for="view in currentStories[currentIndex].views" :key="view.id">
                                <div class="flex items-center justify-between p-2 rounded-xl hover:bg-white/10 transition cursor-default">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <img :src="view.user && view.user.avatar ? '/storage/' + view.user.avatar : 'https://ui-avatars.com/api/?name=' + (view.user ? view.user.name : 'U') + '&background=64748b&color=fff'" class="w-8 h-8 rounded-full border border-white/20 shrink-0">
                                        <span class="text-white text-sm font-bold truncate" x-text="view.user ? view.user.name : 'Usuario'"></span>
                                    </div>
                                    <template x-if="currentStories[currentIndex].likes && currentStories[currentIndex].likes.some(l => view.user && l.user_id === view.user.id)">
                                        <div class="shrink-0 ml-2">
                                            <svg class="w-4 h-4 text-rose-500 fill-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

            </div>
        </template>
    </div>

</div>
@endsection