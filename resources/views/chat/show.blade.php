@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto my-10 px-4" 
     data-messages='@json($messages)'
     data-auth-id="{{ auth()->id() }}"
     data-store-url="{{ route('chat.store', $user) }}"
     data-typing-url="{{ route('chat.typing', $user) }}"
     data-fetch-url="{{ route('chat.fetch', $user) }}"
     data-initial-status="{{ $user->specialty?->name ?? 'Médico Veterinario' }}"
     x-data="{
        messages: [],
        newMessage: '',
        otherUserTyping: false,
        otherUserStatus: '',
        isOnline: false,
        authId: null,
        showEmojiPicker: false,
        imagePreview: null,
        
        // Variables fijas para evitar que Alpine pierda el contexto
        storeUrl: '',
        typingUrl: '',
        fetchUrl: '',

        init() {
            // Usamos this.$root que SIEMPRE apunta al div principal
            this.messages = JSON.parse(this.$root.dataset.messages);
            this.authId = parseInt(this.$root.dataset.authId);
            this.otherUserStatus = this.$root.dataset.initialStatus;
            
            this.storeUrl = this.$root.dataset.storeUrl;
            this.typingUrl = this.$root.dataset.typingUrl;
            this.fetchUrl = this.$root.dataset.fetchUrl;

            this.scrollToBottom();

            setInterval(() => {
                this.fetchUpdates();
            }, 3000);
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.chatContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        addEmoji(emoji) {
            this.newMessage += emoji;
        },

        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        sendMessage() {
            const fileInput = document.getElementById('chatImageInput');
            if (!this.newMessage.trim() && (!fileInput || !fileInput.files[0])) return;

            const formData = new FormData();
            formData.append('body', this.newMessage);
            if (fileInput && fileInput.files[0]) {
                formData.append('image', fileInput.files[0]);
            }

            // Usamos this.storeUrl fijado en init()
            fetch(this.storeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    this.messages.push(data.message);
                    this.newMessage = '';
                    this.imagePreview = null;
                    if (fileInput) fileInput.value = '';
                    this.showEmojiPicker = false;
                    this.scrollToBottom();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo enviar el mensaje.'));
                }
            })
            .catch(err => {
                console.error('Error de red:', err);
            });
        },

        handleTyping() {
            // Usamos this.typingUrl
            fetch(this.typingUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
        },

        fetchUpdates() {
            // Usamos this.fetchUrl
            fetch(this.fetchUrl, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.messages && data.messages.length !== this.messages.length) {
                    this.messages = data.messages;
                    this.scrollToBottom();
                }
                this.otherUserTyping = data.is_typing;
                this.otherUserStatus = data.status_text;
                this.isOnline = data.is_online;
            })
            .catch(err => console.error('Fallo al actualizar el chat:', err));
        }
     }">
     
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xl overflow-hidden flex flex-col h-[750px]">
        
        <!-- Cabecera del Chat -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50 via-white to-slate-50">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('chat.index') }}" class="text-slate-400 hover:text-slate-700 bg-white border border-slate-200 p-2 rounded-xl transition shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                
                <a href="{{ route('users.public', $user) }}" class="relative group">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-12 h-12 rounded-full object-cover border-2 border-slate-200 shadow-sm group-hover:scale-105 transition">
                    @else
                        <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold text-base flex items-center justify-center shadow-sm group-hover:scale-105 transition">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="absolute bottom-0 right-0 w-3.5 h-3.5 border-2 border-white rounded-full transition-colors duration-300"
                          :class="isOnline ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300'"></span>
                </a>

                <div>
                    <a href="{{ route('users.public', $user) }}" class="font-extrabold text-slate-900 text-base hover:text-emerald-600 transition leading-tight block">
                        {{ $user->name }}
                    </a>
                    <span class="text-xs font-semibold block mt-0.5" 
                          :class="otherUserTyping ? 'text-emerald-600 font-bold animate-pulse' : 'text-slate-400'"
                          x-text="otherUserTyping ? 'Escribiendo un mensaje...' : otherUserStatus">
                    </span>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-2 text-xs font-bold text-slate-400 bg-slate-100 px-3.5 py-1.5 rounded-xl">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i> Chat Seguro entre Colegas
            </div>
        </div>

        <!-- Contenedor de Mensajes -->
        <div class="flex-1 overflow-y-auto p-8 space-y-4 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:16px_16px] bg-slate-50/50 flex flex-col" x-ref="chatContainer">
            <template x-for="msg in messages" :key="msg.id">
                <div class="flex" :class="msg.sender_id === authId ? 'justify-end' : 'justify-start'">
                    <div class="max-w-md w-fit rounded-2xl px-4 py-2.5 text-sm shadow-sm relative group transition-all space-y-1.5"
                         :class="msg.sender_id === authId ? 'bg-emerald-600 text-white rounded-br-none shadow-emerald-600/10' : 'bg-white border border-slate-200/80 text-slate-800 rounded-bl-none shadow-slate-200/50'">
                        
                        <template x-if="msg.image">
                            <a :href="'/storage/' + msg.image" target="_blank" class="block overflow-hidden rounded-xl cursor-pointer">
                                <img :src="'/storage/' + msg.image" class="max-h-60 w-full object-cover rounded-xl hover:opacity-95 transition">
                            </a>
                        </template>

                        <p class="leading-relaxed whitespace-pre-wrap break-words" x-text="msg.body"></p>
                        
                        <div class="flex items-center justify-end gap-1.5 pt-0.5">
                            <span class="text-[10px] font-medium" 
                                  :class="msg.sender_id === authId ? 'text-emerald-100' : 'text-slate-400'"
                                  x-text="msg.time_formatted">
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Vista previa de la imagen seleccionada -->
        <div x-show="imagePreview" class="px-5 pt-3 bg-white border-t border-slate-100 flex items-center gap-3" style="display: none;">
            <div class="relative w-fit">
                <img :src="imagePreview" class="h-20 w-auto object-cover rounded-xl border border-slate-200 shadow-sm">
                <button type="button" @click="imagePreview = null; document.getElementById('chatImageInput').value = '';" class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-full p-1 shadow hover:bg-rose-600 transition">
                    &times;
                </button>
            </div>
            <span class="text-xs text-slate-500 font-medium">Imagen lista para enviar...</span>
        </div>

        <!-- Formulario para Enviar -->
        <form @submit.prevent="sendMessage()" class="p-5 border-t border-slate-100 bg-white flex items-center gap-3 relative">
            
            <!-- Selector de Emojis -->
            <div class="absolute bottom-20 left-5 bg-white border border-slate-200 shadow-2xl rounded-2xl p-4 w-72 z-50" 
                 x-show="showEmojiPicker" 
                 @click.away="showEmojiPicker = false" 
                 style="display: none;"
                 x-transition>
                
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex justify-between items-center">
                    <span>Selecciona un emoticón</span>
                    <button type="button" @click="showEmojiPicker = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <div class="grid grid-cols-6 gap-2 max-h-48 overflow-y-auto pr-1 text-xl select-none">
                    <button type="button" @click="addEmoji('🐶')">🐶</button>
                    <button type="button" @click="addEmoji('🐱')">🐱</button>
                    <button type="button" @click="addEmoji('🐮')">🐮</button>
                    <button type="button" @click="addEmoji('🐷')">🐷</button>
                    <button type="button" @click="addEmoji('🐴')">🐴</button>
                    <button type="button" @click="addEmoji('🐰')">🐰</button>
                    <button type="button" @click="addEmoji('🩺')">🩺</button>
                    <button type="button" @click="addEmoji('💊')">💊</button>
                    <button type="button" @click="addEmoji('💉')">💉</button>
                    <button type="button" @click="addEmoji('🧬')">🧬</button>
                    <button type="button" @click="addEmoji('🔬')">🔬</button>
                    <button type="button" @click="addEmoji('🩹')">🩹</button>
                    <button type="button" @click="addEmoji('😊')">😊</button>
                    <button type="button" @click="addEmoji('😂')">😂</button>
                    <button type="button" @click="addEmoji('👍')">👍</button>
                    <button type="button" @click="addEmoji('❤️')">❤️</button>
                    <button type="button" @click="addEmoji('🔥')">🔥</button>
                    <button type="button" @click="addEmoji('🙌')">🙌</button>
                    <button type="button" @click="addEmoji('😎')">😎</button>
                    <button type="button" @click="addEmoji('🤔')">🤔</button>
                    <button type="button" @click="addEmoji('🙏')">🙏</button>
                    <button type="button" @click="addEmoji('✨')">✨</button>
                    <button type="button" @click="addEmoji('💡')">💡</button>
                    <button type="button" @click="addEmoji('💪')">💪</button>
                </div>
            </div>

            <!-- Input de archivo -->
            <input type="file" id="chatImageInput" class="hidden" accept="image/*" @change="previewImage($event)">

            <button type="button" @click="showEmojiPicker = !showEmojiPicker" class="text-slate-400 hover:text-slate-600 p-2.5 rounded-xl hover:bg-slate-100 transition" title="Insertar emoticón">
                😊
            </button>
            <button type="button" @click="document.getElementById('chatImageInput').click()" class="text-slate-400 hover:text-slate-600 p-2.5 rounded-xl hover:bg-slate-100 transition" title="Adjuntar imagen o radiografía">
                📷
            </button>

            <!-- Input de Texto -->
            <div class="relative flex-1">
                <input type="text" 
                       x-model="newMessage" 
                       @input="handleTyping()"
                       autocomplete="off" 
                       placeholder="Escribe un mensaje profesional o clínico..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-4 py-3.5 text-sm focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-inner text-slate-800">
            </div>

            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white px-7 py-3.5 rounded-2xl font-bold text-sm transition shadow-md shadow-emerald-600/20 flex items-center gap-2 shrink-0">
                <span>Enviar</span>
            </button>
        </form>

    </div>
</div>
@endsection