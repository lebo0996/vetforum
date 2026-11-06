@extends('layouts.app')

@section('content')

<!-- Estilos para que Tailwind respete las negritas, listas y estilos del editor -->
<style>
    .rich-text-content a { color: #059669; text-decoration: underline; }
    .rich-text-content strong, .rich-text-content b { font-weight: 800; color: #0f172a; }
    .rich-text-content em, .rich-text-content i { font-style: italic; }
    .rich-text-content ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
    .rich-text-content ol { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
    .rich-text-content p { margin-bottom: 0.75rem; }
    .rich-text-content blockquote { border-left: 4px solid #10b981; padding-left: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem; color: #475569; font-style: italic; background-color: #f8fafc; border-radius: 0 0.5rem 0.5rem 0; margin-bottom: 1rem;}
    
    /* Adaptación de Quill a la estética Tailwind */
    .ql-toolbar.ql-snow { border: 1px solid #e2e8f0; border-radius: 0.75rem 0.75rem 0 0; background-color: #f8fafc; font-family: inherit;}
    .ql-container.ql-snow { border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 0.75rem 0.75rem; min-height: 120px; font-family: inherit; font-size: 0.875rem;}
    .ql-editor { color: #334155; }
    .ql-editor.ql-blank::before { color: #94a3b8; font-style: normal; }
    .ql-editor:focus { box-shadow: 0 0 0 2px #d1fae5; outline: none; border-color: #10b981; border-radius: 0 0 0.75rem 0.75rem;}
</style>

<!-- Librerías de Quill.js -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<div class="max-w-7xl mx-auto my-10 px-4 sm:px-6 lg:flex lg:gap-8 space-y-6 lg:space-y-0">
    
    <!-- ========================================== -->
    <!-- COLUMNA IZQUIERDA: PERFIL DEL AUTOR        -->
    <!-- ========================================== -->
    <div class="lg:w-80 shrink-0 flex flex-col">
        <div class="bg-white p-6 rounded-3xl border border-slate-200/60 shadow-sm text-center sticky top-24">
            
            <!-- Avatar -->
            <a href="{{ route('users.public', $post->user) }}" class="inline-block relative mb-4">
                @if($post->user->avatar)
                    <img src="{{ asset('storage/' . $post->user->avatar) }}" alt="Avatar" class="w-24 h-24 rounded-full object-cover ring-4 ring-slate-50 border border-slate-200 shadow-sm mx-auto transition hover:scale-105">
                @else
                    <div class="w-24 h-24 rounded-full bg-emerald-600 text-white font-bold text-3xl flex items-center justify-center ring-4 ring-slate-50 border border-slate-200 shadow-sm mx-auto transition hover:scale-105">
                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                    </div>
                @endif
            </a>

            <!-- Info del Autor -->
            <div class="space-y-1">
                <div class="flex flex-wrap items-center justify-center gap-1.5">
                    <a href="{{ route('users.public', $post->user) }}" class="font-extrabold text-slate-900 text-lg hover:text-emerald-600 transition leading-tight">
                        {{ $post->user->name }}
                    </a>
                    @if($post->user->is_verified)
                        <span title="Verificado"><i data-lucide="badge-check" class="w-5 h-5 fill-blue-500 text-white"></i></span>
                    @endif
                    @if($post->user->role === 'admin')
                        <span class="bg-blue-50 text-blue-600 text-[10px] font-black px-2 py-0.5 rounded-md border border-blue-200 tracking-wider">ADMIN</span>
                    @endif
                </div>
                <p class="text-xs font-bold text-emerald-600">{{ $post->user->specialty->name ?? 'Médico Veterinario' }}</p>
            </div>

            <!-- Insignias Ganadas del Autor -->
            @if($post->user->badges->count() > 0)
                <div class="mt-4 flex flex-wrap gap-2 justify-center">
                    @foreach($post->user->badges as $badge)
                        <span class="inline-flex items-center gap-1.5 bg-{{ $badge->color }}-50 text-{{ $badge->color }}-700 border border-{{ $badge->color }}-200 px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase tracking-wider shadow-sm" title="{{ $badge->name }}">
                            <i data-lucide="{{ $badge->icon }}" class="w-3 h-3"></i>
                            {{ $badge->name }}
                        </span>
                    @endforeach
                </div>
            @endif

            <!-- Contadores encapsulados -->
            <div class="mt-5 bg-slate-50/80 rounded-2xl p-4 flex items-center justify-around text-sm border border-slate-100 shadow-inner">
                <div class="text-center">
                    <span class="block font-black text-lg text-slate-800">{{ $post->user->followers->count() }}</span>
                    <span class="text-slate-500 text-[9px] font-bold uppercase tracking-wider">Seguidores</span>
                </div>
                <div class="w-px h-8 bg-slate-200"></div>
                <div class="text-center">
                    <span class="block font-black text-lg text-slate-800">{{ $post->user->following->count() }}</span>
                    <span class="text-slate-500 text-[9px] font-bold uppercase tracking-wider">Siguiendo</span>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="mt-5 space-y-2">
                @auth
                    @if(auth()->id() !== $post->user->id)
                        <form action="{{ route('users.follow', $post->user) }}" method="POST">
                            @csrf
                            @php
                                $isFollowing = $post->user->followers->contains(auth()->id());
                            @endphp
                            <button type="submit" class="w-full text-sm font-bold px-4 py-2.5 rounded-xl transition shadow-sm flex items-center justify-center gap-2 {{ $isFollowing ? 'bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                <i data-lucide="{{ $isFollowing ? 'user-check' : 'user-plus' }}" class="w-4 h-4"></i>
                                {{ $isFollowing ? 'Siguiendo' : 'Seguir Autor' }}
                            </button>
                        </form>
                    @endif
                @endauth
                <a href="{{ route('users.public', $post->user) }}" class="block w-full bg-white hover:bg-slate-50 border border-slate-200 text-slate-600 text-sm font-bold py-2.5 rounded-xl transition shadow-sm">
                    Ver Perfil
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- COLUMNA DERECHA: POST Y COMENTARIOS        -->
    <!-- ========================================== -->
    <div class="flex-1 flex flex-col gap-6">
        
        <!-- TARJETA DEL POST PRINCIPAL -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 sm:p-10 space-y-6">
            
            <!-- Categoría y Fecha -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <span class="bg-emerald-50 text-emerald-700 font-bold px-3.5 py-1.5 rounded-lg border border-emerald-100 uppercase tracking-wider text-[10px] shadow-sm flex items-center gap-1.5">
                    <i data-lucide="folder" class="w-3.5 h-3.5"></i> {{ $post->category->name ?? 'General' }}
                </span>
                <span class="text-slate-400 text-xs font-medium flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i> {{ $post->created_at->format('d M Y, H:i') }}
                </span>
            </div>

            <!-- Título -->
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">{{ $post->title }}</h1>

            <!-- Cuerpo del Post -->
            <div class="rich-text-content text-slate-700 text-base sm:text-lg leading-relaxed border-t border-slate-100 pt-6">
                {!! $post->content !!}
            </div>

            <!-- ========================================== -->
            <!-- ENCUESTA DEL POST (INTERACTIVA)            -->
            <!-- ========================================== -->
            @if($post->poll ?? false)
                <div class="pt-4" x-data='{
                    poll: @json($post->poll),
                    voted: {{ $post->poll->votes->where("user_id", auth()->id())->count() > 0 ? "true" : "false" }},
                    userVotedOptionId: {{ optional($post->poll->votes->where("user_id", auth()->id())->first())->poll_option_id ?? "null" }},
                    totalVotes: {{ $post->poll->options->sum("votes_count") }},
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
                    <div class="bg-slate-50/90 border border-slate-200 rounded-2xl p-5 space-y-3 shadow-sm">
                        <h4 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                            <i data-lucide="bar-chart-2" class="w-5 h-5 text-emerald-600"></i>
                            <span x-text="poll.question"></span>
                        </h4>
                        
                        <div class="space-y-2.5">
                            <template x-for="option in poll.options" :key="option.id">
                                <button @click="vote(option.id)" 
                                        class="w-full text-left p-3.5 rounded-xl border transition relative overflow-hidden flex items-center justify-between text-sm group"
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
                        
                        <div class="flex justify-between items-center pt-1 text-xs text-slate-400 font-medium">
                            <span x-text="voted ? 'Gracias por participar en la encuesta' : 'Haz clic en una opción para votar'"></span>
                            <span x-text="totalVotes + ' votos totales'"></span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Imagen adjunta -->
            @if($post->image ?? false)
                <div class="overflow-hidden rounded-2xl border border-slate-100 shadow-sm bg-slate-50 mt-6">
                    <a href="{{ asset('storage/' . $post->image) }}" target="_blank" class="block cursor-zoom-in">
                        <img src="{{ asset('storage/' . $post->image) }}" alt="Imagen del post" class="max-h-[40rem] w-full object-contain hover:opacity-95 transition">
                    </a>
                </div>
            @endif

            <!-- INTERACCIONES Y ACCIONES -->
            <div class="flex flex-wrap items-center justify-between gap-4 pt-6 border-t border-slate-100 text-sm">
                <!-- Botón de Like -->
                @auth
                    <form action="{{ route('forum.like', $post) }}" method="POST">
                        @csrf
                        @php
                            $hasLiked = $post->likes->where('user_id', auth()->id())->count() > 0;
                        @endphp
                        <button type="submit" class="flex items-center gap-2 px-5 py-2.5 rounded-xl transition border shadow-sm {{ $hasLiked ? 'bg-rose-50 border-rose-200 text-rose-600 font-bold' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 font-bold' }}">
                            <i data-lucide="heart" class="w-4 h-4 {{ $hasLiked ? 'fill-rose-500 text-rose-500' : '' }}"></i>
                            <span>{{ $post->likes->count() }} Me gusta</span>
                        </button>
                    </form>
                @else
                    <div class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 font-bold shadow-sm">
                        <i data-lucide="heart" class="w-4 h-4"></i>
                        <span>{{ $post->likes->count() }} Me gusta</span>
                    </div>
                @endauth

                <!-- Botones del Autor (Editar / Eliminar) -->
                @auth
                    @if(auth()->id() === $post->user_id || auth()->user()->role === 'admin')
                        <div class="flex items-center gap-2">
                            <a href="{{ route('forum.edit', $post) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition shadow-sm" title="Editar">
                                <i data-lucide="edit-2" class="w-4 h-4"></i> <span class="hidden sm:inline">Editar</span>
                            </a>
                            
                            <form action="{{ route('admin.posts.delete', $post) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar permanentemente esta publicación?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition shadow-sm" title="Eliminar">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i> <span class="hidden sm:inline">Eliminar</span>
                                </button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        <!-- SECCIÓN DE COMENTARIOS -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 sm:p-10 space-y-6">
            <h3 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="message-square" class="w-5 h-5 text-emerald-600"></i> Discusión Clínica ({{ $post->comments->count() }})
            </h3>

            <!-- Formulario de Comentario -->
            @auth
                <form id="comment-form" action="{{ route('forum.storeComment', $post) }}" method="POST" class="space-y-4 bg-slate-50 p-5 rounded-2xl border border-slate-100">
                    @csrf
                    
                    <input type="hidden" name="content" id="hidden-comment-input">

                    <div class="flex items-start gap-4">
                        <div class="shrink-0 hidden sm:block">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover border border-slate-200 shadow-sm">
                            @else
                                <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold text-sm flex items-center justify-center shadow-sm">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-1">
                            <div class="bg-white rounded-xl shadow-sm">
                                <div id="quill-editor"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition shadow-sm flex items-center gap-2">
                            Comentar <i data-lucide="send" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            @else
                <div class="bg-slate-50 border border-slate-200 p-6 rounded-2xl text-center text-sm text-slate-600 shadow-inner">
                    <i data-lucide="lock" class="w-6 h-6 text-slate-400 mx-auto mb-2"></i>
                    <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:underline">Inicia sesión</a> para unirte a la discusión.
                </div>
            @endauth

            <!-- Lista de Comentarios -->
            <div class="space-y-5 pt-4">
                @forelse($post->comments as $comment)
                    <div class="flex items-start gap-4">
                        <a href="{{ route('users.public', $comment->user) }}" class="shrink-0">
                            @if($comment->user->avatar)
                                <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover border border-slate-200 shadow-sm">
                            @else
                                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm flex items-center justify-center border border-emerald-200 shadow-sm">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                            @endif
                        </a>

                        <div class="flex-1 bg-slate-50 p-5 rounded-2xl rounded-tl-none border border-slate-100 shadow-sm space-y-2 relative group">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <a href="{{ route('users.public', $comment->user) }}" class="font-bold text-slate-900 text-sm hover:text-emerald-600 transition flex items-center gap-1.5">
                                    {{ $comment->user->name }}
                                    @if($comment->user->is_verified)
                                        <i data-lucide="badge-check" class="w-4 h-4 fill-blue-500 text-white"></i>
                                    @endif
                                </a>
                                <span class="text-[11px] text-slate-400 font-medium flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i> {{ $comment->created_at->diffForHumans() }}
                                </span>
                            </div>
                            
                            <!-- Contenido del comentario -->
                            <div class="rich-text-content text-slate-700 text-sm sm:text-base leading-relaxed">
                                {!! $comment->content !!}
                            </div>
                            
                            <!-- Div oculto con el contenido sin formato para citar -->
                            <div id="raw-comment-{{ $comment->id }}" style="display:none;">{!! $comment->content !!}</div>

                            <!-- Botón de Responder -->
                            @auth
                                <div class="flex justify-end mt-3 pt-3 border-t border-slate-200/50">
                                    <button type="button" data-username="{{ $comment->user->name }}" data-id="{{ $comment->id }}" onclick="quoteComment(this)" class="text-[11px] font-bold text-slate-400 hover:text-emerald-600 uppercase tracking-wider transition flex items-center gap-1">
                                        <i data-lucide="reply" class="w-3.5 h-3.5"></i> Citar / Responder
                                    </button>
                                </div>
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400 text-sm bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i data-lucide="messages-square" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
                        Aún no hay comentarios en este caso. ¡Sé el primero en aportar!
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<!-- Script de Inicialización de Quill.js -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('quill-editor')) {
            var quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Aporta tu perspectiva profesional (puedes usar negritas, listas o enlaces)...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'clean']
                    ]
                }
            });

            var form = document.getElementById('comment-form');
            form.onsubmit = function() {
                document.getElementById('hidden-comment-input').value = quill.root.innerHTML;
            };

            window.quoteComment = function(btnElement) {
                var username = btnElement.getAttribute('data-username');
                var commentId = btnElement.getAttribute('data-id');
                var rawHtml = document.getElementById('raw-comment-' + commentId).innerHTML;
                var quoteHtml = '<blockquote><strong>@' + username + ' dijo:</strong><br>' + rawHtml + '</blockquote><br><p><br></p>';
                
                var currentHtml = quill.root.innerHTML;
                if(currentHtml === '<p><br></p>') {
                    currentHtml = '';
                }
                
                quill.clipboard.dangerouslyPasteHTML(0, quoteHtml);
                document.getElementById('quill-editor').scrollIntoView({ behavior: 'smooth', block: 'center' });
                quill.focus();
            };
        }
    });
</script>
@endsection