@extends('layouts.app')

@section('content')

<!-- Estilos para adaptar Quill al estilo Tailwind de VetForum -->
<style>
    .ql-toolbar.ql-snow {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem 0.75rem 0 0;
        background-color: #f8fafc;
        font-family: inherit;
    }
    .ql-container.ql-snow {
        border: 1px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 0.75rem 0.75rem;
        min-height: 250px;
        font-family: inherit;
        font-size: 0.95rem;
    }
    .ql-editor {
        color: #334155;
    }
    .ql-editor.ql-blank::before {
        color: #94a3b8;
        font-style: normal;
    }
    .ql-editor:focus {
        box-shadow: 0 0 0 2px #d1fae5;
        outline: none;
        border-color: #10b981;
        border-radius: 0 0 0.75rem 0.75rem;
    }
</style>

<!-- Quill.js CDN -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<div class="max-w-5xl mx-auto my-10 px-4 sm:px-6">
    
    <!-- Encabezado y Retorno -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="message-square-plus" class="w-8 h-8 text-emerald-600"></i> Nuevo Caso, Discusión o Encuesta
            </h1>
            <p class="text-sm text-slate-500 mt-1">Comparte un caso clínico, duda médica, investigación o abre una votación con la comunidad.</p>
        </div>
        <a href="{{ route('forum.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 bg-white border border-slate-200 px-3.5 py-2 rounded-xl transition shadow-sm flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Foro
        </a>
    </div>

    <!-- Tarjeta Principal del Formulario -->
    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 sm:p-10" x-data="{ showPollCreator: false }">
        
        <form id="create-post-form" action="{{ route('forum.storePost') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Input Oculto que guarda el contenido previo y recibe el HTML de Quill -->
            <input type="hidden" name="content" id="hidden-post-content" value="{{ old('content', '') }}">

            <!-- Título del Post -->
            <div>
                <label for="title" class="block text-sm font-bold text-slate-800 mb-2">Título de la Publicación *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="Ej. Manejo quirúrgico de torsión gástrica en canino macho de 6 años" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm sm:text-base text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-sm">
                @error('title')
                    <p class="text-xs text-rose-500 font-medium mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Categoría Clínica -->
            <div>
                <label for="category_id" class="block text-sm font-bold text-slate-800 mb-2">Especialidad o Categoría *</label>
                <select name="category_id" id="category_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-sm">
                    <option value="" disabled selected>Selecciona una categoría para clasificar el tema...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-xs text-rose-500 font-medium mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Editor de Texto Enriquecido Quill -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-2">Descripción o Caso Clínico *</label>
                <div class="bg-white rounded-xl shadow-sm">
                    <div id="quill-post-editor"></div>
                </div>
                @error('content')
                    <p class="text-xs text-rose-500 font-medium mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- SECCIÓN: CREADOR DE ENCUESTAS OPCIONAL -->
            <div class="pt-2">
                <button type="button" @click="showPollCreator = !showPollCreator" class="flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition px-4 py-2.5 rounded-xl border border-emerald-200 shadow-sm">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    <span x-text="showPollCreator ? 'Quitar Encuesta' : 'Añadir una Encuesta a la Discusión'"></span>
                </button>

                <div x-show="showPollCreator" style="display: none;" class="mt-4 p-5 bg-slate-50 border border-slate-200 rounded-2xl space-y-4" x-transition>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="bar-chart-2" class="w-4 h-4 text-emerald-600"></i> Configurar Encuesta
                        </span>
                        <button type="button" @click="showPollCreator = false" class="text-slate-400 hover:text-rose-500 text-xs font-bold">Cancelar</button>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pregunta de la encuesta</label>
                        <input type="text" name="poll_question" placeholder="Ej. ¿Qué tratamiento prefieres para este cuadro?" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Opciones de respuesta</label>
                        <input type="text" name="poll_options[]" placeholder="Opción 1" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-emerald-500">
                        <input type="text" name="poll_options[]" placeholder="Opción 2" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-emerald-500">
                        <input type="text" name="poll_options[]" placeholder="Opción 3 (Opcional)" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Subida de Imagen Adjunta (Radiografía, Eco, Fotografía) -->
            <div x-data="{ photoName: null, photoPreview: null }">
                <label class="block text-sm font-bold text-slate-800 mb-2">Imagen Adjunta (Opcional - Máx. 5MB)</label>
                
                <input type="file" name="image" id="image" class="hidden" x-ref="photo" accept="image/*"
                    x-on:change="
                        photoName = $refs.photo.files[0].name;
                        const reader = new FileReader();
                        reader.onload = (e) => { photoPreview = e.target.result; };
                        reader.readAsDataURL($refs.photo.files[0]);
                    ">

                <!-- Botón de Carga -->
                <div class="flex items-center gap-4">
                    <button type="button" x-on:click.prevent="$refs.photo.click()" class="bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold px-4 py-2.5 rounded-xl text-sm transition shadow-sm flex items-center gap-2">
                        <i data-lucide="image-plus" class="w-4 h-4 text-emerald-600"></i> Seleccionar Imagen
                    </button>
                    <span x-text="photoName ? photoName : 'Ninguna imagen seleccionada'" class="text-xs text-slate-400 truncate max-w-xs"></span>
                </div>

                <!-- Preview de la Imagen cargada -->
                <div x-show="photoPreview" class="mt-4 relative w-fit" style="display: none;">
                    <img :src="photoPreview" class="h-44 w-auto object-cover rounded-2xl border border-slate-200 shadow-sm">
                    <button type="button" x-on:click="photoPreview = null; photoName = null; $refs.photo.value = null;" class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-full p-1 shadow-md hover:bg-rose-600 transition" title="Quitar imagen">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                @error('image')
                    <p class="text-xs text-rose-500 font-medium mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('forum.index') }}" class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition shadow-sm flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Publicar Caso
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Script Inicializador de Quill.js sin sintaxis propensa a alertas de Blade -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var editorElement = document.getElementById('quill-post-editor');
        var hiddenInput = document.getElementById('hidden-post-content');
        var form = document.getElementById('create-post-form');

        if (editorElement && hiddenInput && form) {
            var quill = new Quill('#quill-post-editor', {
                theme: 'snow',
                placeholder: 'Describe la sintomatología, anamnesis, tratamiento aplicado o consulta profesional...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'blockquote', 'clean']
                    ]
                }
            });

            // Si hay contenido previo de validación (old), se inyecta en el editor
            if (hiddenInput.value && hiddenInput.value.trim() !== '') {
                quill.clipboard.dangerouslyPasteHTML(0, hiddenInput.value);
            }

            form.onsubmit = function() {
                var htmlContent = quill.root.innerHTML;
                
                // Si el editor está vacío, evitamos enviar el párrafo residual de Quill
                if (htmlContent === '<p><br></p>') {
                    htmlContent = '';
                }
                
                hiddenInput.value = htmlContent;
            };
        }
    });
</script>
@endsection