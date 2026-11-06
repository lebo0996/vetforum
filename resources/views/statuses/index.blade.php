@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    
    <!-- GRID GENERAL DE 4 COLUMNAS -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- ========================================== -->
        <!-- COLUMNA IZQUIERDA: MURO SOCIAL (3 Cols)   -->
        <!-- ========================================== -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- ========================================== -->
            <!-- BARRA DE HISTORIAS (24 HRS)                -->
            <!-- ========================================== -->
            @auth
            @php
                $coleccionBase = collect($stories->all());
                $misHistorias = collect($coleccionBase->pull(auth()->id(), []));
                $otrasHistorias = $coleccionBase;

                if ($misHistorias->count() > 0) {
                    $misHistorias->each(function($story) {
                        $story->load('views.user');
                        $story->time_ago = $story->created_at->diffForHumans();
                    });
                }

                $otrasHistorias->each(function($userStories) {
                    $userStories->each(fn($s) => $s->time_ago = $s->created_at->diffForHumans());
                });
            @endphp

            <div x-data="{
                uploadModal: false,
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
                    let miId = {{ auth()->id() }};
                    
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
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm overflow-x-auto no-scrollbar mb-6">
                    <div class="flex items-center gap-4 min-w-max px-1">
                        
                        <!-- 1. TU HISTORIA -->
                        <div class="flex flex-col items-center gap-1.5 relative group">
                            @if($misHistorias->count() > 0)
                                @php
                                    $todasVistas = $misHistorias->every(fn($s) => $s->views->contains('user_id', auth()->id()));
                                @endphp
                                <div class="relative cursor-pointer" @click='openViewer(@json($misHistorias), true)'>
                                    <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=10b981&color=fff' }}" 
                                         class="w-16 h-16 rounded-full object-cover border-4 {{ $todasVistas ? 'border-slate-300' : 'border-emerald-500' }} p-0.5 group-hover:scale-105 transition-transform duration-300 shadow-sm">
                                </div>
                                <button @click="uploadModal = true" class="absolute bottom-5 -right-1 bg-blue-500 text-white rounded-full p-1 border-2 border-white shadow-sm hover:bg-blue-600 transition z-10" title="Agregar más">
                                    <i data-lucide="plus" class="w-3 h-3"></i>
                                </button>
                            @else
                                <div class="relative cursor-pointer" @click="uploadModal = true">
                                    <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=10b981&color=fff' }}" 
                                         class="w-16 h-16 rounded-full object-cover border-2 border-slate-200 p-0.5 group-hover:scale-105 transition-transform duration-300 shadow-sm">
                                    <div class="absolute bottom-0 right-0 bg-blue-500 text-white rounded-full p-1 border-2 border-white shadow-sm hover:bg-blue-600 transition">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    </div>
                                </div>
                            @endif
                            <span class="text-xs font-bold text-slate-700">Tu historia</span>
                        </div>

                        <!-- 2. HISTORIAS DE COLEGAS -->
                        @foreach($otrasHistorias as $userId => $userStories)
                            @if($userStories->count() > 0)
                                @php
                                    $autor = $userStories->first()->user;
                                    $todasVistas = $userStories->every(fn($s) => $s->views->contains('user_id', auth()->id()));
                                @endphp
                                <div class="flex flex-col items-center gap-1.5 cursor-pointer group" @click='openViewer(@json($userStories), false)'>
                                    <img src="{{ $autor?->avatar ? asset('storage/' . $autor->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($autor?->name ?? 'U') . '&background=64748b&color=fff' }}" 
                                         class="w-16 h-16 rounded-full object-cover border-4 {{ $todasVistas ? 'border-slate-300' : 'border-emerald-500' }} p-0.5 group-hover:scale-105 transition-transform duration-300 shadow-sm">
                                    <span class="text-xs font-bold {{ $todasVistas ? 'text-slate-500' : 'text-slate-900' }} truncate w-16 text-center">
                                        {{ explode(' ', $autor?->name ?? 'Colega')[0] }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- MODAL DE CARGA -->
                <div x-show="uploadModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center z-[150] p-4" style="display: none;" x-transition>
                    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative" @click.away="uploadModal = false">
                        <button type="button" @click="uploadModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-rose-600 bg-slate-100 rounded-full p-1.5 transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                        
                        <h3 class="text-lg font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                            <i data-lucide="camera" class="w-5 h-5 text-emerald-600"></i> Nueva Historia
                        </h3>
                        <p class="text-xs text-slate-500 mb-5">Las fotos o videos desaparecerán automáticamente en 24 horas.</p>

                        <form action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div x-data="{ fileName: '' }">
                                <label class="block w-full border-2 border-dashed border-slate-300 rounded-2xl p-8 text-center cursor-pointer hover:border-emerald-500 hover:bg-emerald-50 transition">
                                    <i data-lucide="image-plus" class="w-8 h-8 text-slate-400 mx-auto mb-2" x-show="fileName === ''"></i>
                                    <i data-lucide="check-circle" class="w-8 h-8 text-emerald-500 mx-auto mb-2" x-show="fileName !== ''" style="display: none;"></i>
                                    <span class="text-sm font-bold text-slate-700 block" x-text="fileName === '' ? 'Toca para seleccionar foto o video' : fileName"></span>
                                    <input type="file" name="media" accept="image/*,video/mp4,video/quicktime" class="hidden" @change="fileName = $event.target.files[0].name" required>
                                </label>
                            </div>

                            <div>
                                <input type="text" name="caption" placeholder="Escribe un mensaje corto (opcional)..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition">
                            </div>

                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-3 rounded-xl transition shadow-sm flex justify-center items-center gap-2">
                                <i data-lucide="send" class="w-4 h-4"></i> Publicar Historia
                            </button>
                        </form>
                    </div>
                </div>

                <!-- VISOR DE HISTORIAS -->
                <div x-show="viewerOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-sm" style="display: none;" x-transition.opacity>
                    
                    <button @click="closeViewer()" class="absolute top-4 right-4 md:top-6 md:right-8 text-white/70 hover:text-white z-[110] transition-colors p-2">
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
                                             :class="currentStories[currentIndex].likes && currentStories[currentIndex].likes.some(l => l.user_id === {{ auth()->id() }}) ? 'text-rose-500 fill-rose-500 scale-110' : 'text-white group-hover:text-rose-400'" 
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
            @endauth
            <!-- ========================================== -->
            
            <!-- Formulario para Crear Publicación con Encuesta -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm" x-data="{ showPollCreator: false }">
                <h2 class="font-extrabold text-slate-800 text-base mb-4 flex items-center gap-2">
    <i data-lucide="edit-3" class="w-5 h-5 text-emerald-600"></i> ¿Qué estás pensando, {{ auth()->check() ? explode(' ', auth()->user()->name)[0] : 'Colega' }}?
</h2>
                
                <form action="{{ route('statuses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <textarea name="content" rows="3" placeholder="Comparte, actualiza o informa.. respeta las normas y reglas.." required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 resize-none transition shadow-inner">{{ old('content') }}</textarea>
                    
                    <!-- CREADOR DE ENCUESTAS -->
                    <div x-show="showPollCreator" style="display: none;" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3" x-transition>
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

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 border-t border-slate-100">
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

                        <button type="submit" class="w-full sm:w-auto bg-slate-900 hover:bg-emerald-700 text-white font-extrabold px-6 py-2.5 rounded-xl text-sm transition shadow-sm flex justify-center items-center gap-2">
                            Publicar nota
                        </button>
                    </div>
                </form>
            </div>

            <!-- LISTADO VERTICAL (FEED) -->
            <div class="space-y-5">
                @forelse($statuses as $status)
                    <div x-data="{ showComments: false, editing: false, reposting: false }" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition hover:border-slate-300">
                        
                        <!-- Cabecera del Autor -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                @if($status->user?->avatar)
                                    <img src="{{ asset('storage/' . $status->user->avatar) }}" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($status->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="flex items-center gap-2">
                                        @if($status->user)
                                            <a href="{{ route('users.public', $status->user) }}" class="font-bold text-slate-800 text-sm hover:text-emerald-600 transition flex items-center gap-1">
                                                {{ $status->user->name }}
                                                @if($status->user->is_verified)
                                                    <i data-lucide="badge-check" class="w-4 h-4 text-blue-500 fill-blue-500/20" title="Médico Verificado"></i>
                                                @endif
                                            </a>
                                        @else
                                            <span class="font-bold text-slate-800 text-sm">Usuario Eliminado</span>
                                        @endif

                                        <div class="flex items-center gap-1">
                                            @foreach($status->user?->badges ?? [] as $badge)
                                                <div class="group relative flex items-center justify-center w-5 h-5 rounded-full bg-{{ $badge->color }}-100 text-{{ $badge->color }}-600 cursor-help border border-{{ $badge->color }}-200">
                                                    <i data-lucide="{{ $badge->icon }}" class="w-3 h-3"></i>
                                                    <span class="absolute bottom-full mb-1 hidden group-hover:block w-max bg-slate-800 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg z-10">{{ $badge->name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <span class="block text-xs text-slate-400 font-medium">{{ $status->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            
                            <!-- ACCIONES DE LA PUBLICACIÓN (Editar, Eliminar, Reportar) -->
                            <div class="flex items-center gap-3" x-data="{ showReportStatusModal: false }">
                                @auth
                                    <!-- Botón de Editar (Solo dueño) -->
                                    @if($status->user_id === auth()->id())
                                        <button @click="editing = !editing" class="text-slate-300 hover:text-blue-500 transition" title="Editar publicación"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                                    @endif
                                    
                                    <!-- Botón de Eliminar (Dueño o admin) -->
                                    @if($status->user_id === auth()->id() || auth()->user()?->role === 'admin')
                                        <form action="{{ route('statuses.destroy', $status) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta nota?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-rose-500 transition" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                        </form>
                                    @endif

                                    <!-- Botón de Reportar (Solo si NO es el dueño) -->
                                    @if($status->user_id !== auth()->id())
                                        <button @click="showReportStatusModal = true" type="button" class="text-slate-300 hover:text-amber-500 transition" title="Reportar esta publicación">
                                            <i data-lucide="flag" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                @endauth

                                <!-- MODAL DE REPORTE DE PUBLICACIÓN -->
                                <div x-show="showReportStatusModal" style="display: none;" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm text-left">
                                    <div @click.away="showReportStatusModal = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
                                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                                            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                                <i data-lucide="flag" class="w-5 h-5 text-amber-500"></i>
                                                Reportar Publicación
                                            </h3>
                                            <button @click="showReportStatusModal = false" type="button" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-200">
                                                <i data-lucide="x" class="w-5 h-5"></i>
                                            </button>
                                        </div>

                                        <form action="{{ route('reports.store', $status->user) }}" method="POST" class="p-6 space-y-5">
                                            @csrf
                                            <!-- INPUT OCULTO CON EL ID DE LA PUBLICACIÓN -->
                                            <input type="hidden" name="status_id" value="{{ $status->id }}">
                                            
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700 mb-2">Motivo del reporte <span class="text-rose-500">*</span></label>
                                                <select name="reason" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition text-slate-800 font-medium">
                                                    <option value="" disabled selected>Selecciona un motivo...</option>
                                                    <option value="Contenido gráfico o sensible sin advertencia">Contenido gráfico o sensible sin advertencia</option>
                                                    <option value="Spam o publicidad no deseada">Spam o publicidad no deseada</option>
                                                    <option value="Información falsa o antiprofesional">Información falsa o antiprofesional</option>
                                                    <option value="Acoso o discurso de odio">Acoso o discurso de odio</option>
                                                    <option value="Otro">Otro motivo</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-bold text-slate-700 mb-2">Detalles adicionales (Opcional)</label>
                                                <textarea name="description" rows="3" placeholder="Describe por qué esta publicación infringe las normas..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition text-slate-800 resize-none"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-3 pt-2">
                                                <button type="button" @click="showReportStatusModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                                                    Cancelar
                                                </button>
                                                <button type="submit" class="flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-xl shadow-md transition active:scale-95">
                                                    <span>Enviar Reporte</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- FIN MODAL -->

                            </div>
                        </div>

                        <!-- Formulario de Edición -->
                        <form x-show="editing" action="{{ route('statuses.update', $status) }}" method="POST" class="mb-4" style="display: none;">
                            @csrf @method('PUT')
                            <textarea name="content" rows="3" class="w-full border border-slate-300 rounded-xl p-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition">{{ $status->content }}</textarea>
                            <div class="flex justify-end gap-2 mt-2">
                                <button type="button" @click="editing = false" class="px-4 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-lg transition font-semibold">Cancelar</button>
                                <button type="submit" class="px-4 py-1.5 text-xs bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-bold shadow-sm">Guardar cambios</button>
                            </div>
                        </form>

                        <!-- Contenido -->
                        <div x-show="!editing" class="space-y-3 mb-4">
                            @if($status->repost_id && $status->repost)
                                @if($status->content && $status->content !== 'Compartió esta nota con la comunidad.')
                                    <p class="text-slate-700 text-sm leading-relaxed whitespace-pre-line mb-3">{{ $status->content }}</p>
                                @endif

                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 mb-3 text-xs">
    <div class="flex items-center gap-1.5 text-slate-500 font-bold mb-1">
        <i data-lucide="repeat-2" class="w-3.5 h-3.5 text-emerald-600"></i>
        <span>Republicado de 
            @if($status->repost && $status->repost->user)
                <a href="{{ route('users.public', $status->repost->user) }}" class="text-emerald-700 hover:underline">{{ $status->repost->user->name }}</a>
            @else
                <span class="text-slate-700">Usuario Desconocido</span>
            @endif
        </span>
    </div>
    <p class="text-slate-600 line-clamp-2">{{ $status->repost?->content }}</p>
</div>
                            @else
                                <p class="text-slate-800 text-sm leading-relaxed whitespace-pre-line">{{ $status->content }}</p>
                            @endif
                            
                            @if($status->image)
                                <div class="rounded-xl overflow-hidden bg-slate-50 border border-slate-200 max-h-[450px]">
                                    <img src="{{ asset('storage/' . $status->image) }}" class="w-full h-auto object-cover">
                                </div>
                            @endif
                        </div>

                        <!-- ENCUESTA -->
                        @if($status->poll)
                            <div class="mb-4" x-data='{
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

                        <!-- Barra de Interacciones -->
                        <div class="pt-4 border-t border-slate-100 flex items-center gap-6 text-slate-500 text-xs font-bold">
                            <form action="{{ route('statuses.like', $status) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center gap-1.5 hover:text-rose-500 transition {{ $status->statusLikes->contains('user_id', auth()->id()) ? 'text-rose-500' : '' }}">
                                    <i data-lucide="heart" class="w-4 h-4 {{ $status->statusLikes->contains('user_id', auth()->id()) ? 'fill-current' : '' }}"></i>
                                    <span>{{ $status->statusLikes->count() }}</span>
                                </button>
                            </form>

                            <button @click="$refs.commentInput{{ $status->id }}.focus()" class="flex items-center gap-1.5 hover:text-blue-500 transition group">
                                <i data-lucide="message-square" class="w-4 h-4 group-hover:scale-110 transition"></i>
                                <span>{{ $status->comments->count() }}</span>
                            </button>

                            <button type="button" @click="reposting = !reposting" class="flex items-center gap-1.5 hover:text-emerald-600 transition group" title="Republicar nota">
                                <i data-lucide="repeat-2" class="w-4 h-4 group-hover:scale-110 transition"></i>
                                <span>{{ $status->reposts_count ?? 0 }}</span>
                            </button>
                        </div>

                        <!-- Republicar -->
                        <div x-show="reposting" x-transition class="mt-3 p-3 bg-emerald-50 border border-emerald-100 rounded-xl" style="display: none;">
                            <form action="{{ route('statuses.repost', $status) }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="content" placeholder="Añade tu opinión al republicar (opcional)..." class="flex-1 border border-emerald-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 bg-white shadow-inner">
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition text-xs font-bold shadow-sm">Republicar</button>
                            </form>
                        </div>

                        <!-- COMENTARIOS -->
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            
                            @if($status->comments->count() > 0)
                                <div class="space-y-3 mb-2">
                                    @foreach($status->comments->take(2) as $comment)
                                        <div class="flex gap-2.5">
                                            @if($comment->user?->avatar)
                                                <img src="{{ asset('storage/' . $comment->user->avatar) }}" class="w-7 h-7 rounded-full object-cover border border-slate-200">
                                            @else
                                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center font-bold text-[10px]">
                                                    {{ strtoupper(substr($comment->user?->name ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                            
                                            <div class="flex-1 bg-slate-50 border border-slate-100 rounded-xl p-3">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="font-bold text-slate-800 text-[11px]">{{ $comment->user?->name ?? 'Usuario Anónimo' }}</span>
                                                    <span class="text-[10px] text-slate-400 font-medium">{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-slate-600 text-xs leading-relaxed">{{ $comment->content }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($status->comments->count() > 2)
                                <button @click="showComments = !showComments" class="text-[11px] text-slate-500 hover:text-emerald-600 font-bold mb-3 flex items-center gap-1 transition">
                                    <span x-show="!showComments">Ver los {{ $status->comments->count() }} comentarios...</span>
                                    <span x-show="showComments">Ocultar comentarios anteriores</span>
                                </button>
                                
                                <div x-show="showComments" x-transition class="space-y-3 mb-3 max-h-60 overflow-y-auto pr-1" style="display: none;">
                                    @foreach($status->comments->skip(2) as $comment)
                                        <div class="flex gap-2.5">
                                            @if($comment->user?->avatar)
                                                <img src="{{ asset('storage/' . $comment->user->avatar) }}" class="w-7 h-7 rounded-full object-cover border border-slate-200">
                                            @else
                                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center font-bold text-[10px]">
                                                    {{ strtoupper(substr($comment->user?->name ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                            
                                            <div class="flex-1 bg-slate-50 border border-slate-100 rounded-xl p-3">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="font-bold text-slate-800 text-[11px]">{{ $comment->user?->name ?? 'Usuario Anónimo' }}</span>
                                                    <span class="text-[10px] text-slate-400 font-medium">{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-slate-600 text-xs leading-relaxed">{{ $comment->content }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <form action="{{ route('statuses.comment', $status) }}" method="POST" class="flex gap-2 mt-3 relative">
                                @csrf
                                <input type="text" name="content" required x-ref="commentInput{{ $status->id }}" placeholder="Escribe tu opinión o consejo clínico..." class="flex-1 border border-slate-200 rounded-xl pl-4 pr-12 py-2.5 text-xs focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 bg-slate-50 focus:bg-white transition shadow-inner">
                                <button type="submit" class="absolute right-1.5 top-1.5 bottom-1.5 bg-slate-900 hover:bg-emerald-600 text-white px-3 rounded-lg transition shadow-sm flex items-center justify-center" title="Enviar comentario">
                                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white p-12 rounded-2xl border border-dashed border-slate-300 text-center text-slate-400 text-sm shadow-sm">
                        <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 text-slate-300"></i>
                        Aún no hay publicaciones en el muro social. ¡Sé el primero en compartir algo con los colegas!
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ========================================== -->
        <!-- COLUMNA DERECHA: TOP COLEGAS               -->
        <!-- ========================================== -->
        <div class="hidden lg:block space-y-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm sticky top-20">
                <h3 class="font-extrabold text-slate-900 text-sm mb-4 flex items-center gap-2">
                    <i data-lucide="flame" class="w-4 h-4 text-orange-500 fill-orange-500 animate-pulse"></i> 
                    Colegas Más Activos
                </h3>
                
                <div class="space-y-4">
                    @forelse($topColegas ?? [] as $index => $colega)
                        <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black w-5 text-center {{ $index === 0 ? 'text-amber-500' : ($index === 1 ? 'text-slate-400' : ($index === 2 ? 'text-amber-700' : 'text-slate-400')) }}">
                                    @if($index === 0) 🥇 
                                    @elseif($index === 1) 🥈 
                                    @elseif($index === 2) 🥉 
                                    @else {{ $index + 1 }}º 
                                    @endif
                                </span>

                                @if($colega->avatar)
                                    <img src="{{ asset('storage/' . $colega->avatar) }}" class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-sm">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-600 border border-emerald-200 shadow-sm flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($colega->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <a href="{{ route('users.public', $colega) }}" class="font-bold text-slate-800 text-xs hover:text-emerald-600 block line-clamp-1 transition">
                                        {{ $colega->name }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 flex items-center gap-1 font-medium">
                                        <i data-lucide="message-square" class="w-3 h-3 text-emerald-500"></i> {{ $colega->statuses_count }} notas
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-2 font-medium">Calculando actividad...</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection