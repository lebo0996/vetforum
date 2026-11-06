<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- COLUMNA IZQUIERDA: Formulario para Crear Insignia -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 h-fit">
        <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-5 h-5 text-emerald-600"></i> Crear Nueva Insignia
        </h2>
        
        <form action="{{ route('admin.badges.store') }}" method="POST" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre de la Insignia</label>
        <input type="text" name="name" required placeholder="Ej. Cirujano Experto" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <!-- Select de Color -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Color</label>
            <select name="color" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
                <option value="emerald">Esmeralda</option>
                <option value="blue">Azul</option>
                <option value="amber">Dorado</option>
                <option value="purple">Púrpura</option>
                <option value="rose">Rosa</option>
                <option value="orange">Naranja</option>
                <option value="slate">Plata / Gris</option>
            </select>
        </div>
        <!-- Select de Icono Ampliado -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Icono</label>
            <select name="icon" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
                <optgroup label="Clínica y Médica">
                    <option value="stethoscope">🩺 Estetoscopio</option>
                    <option value="bone">🦴 Hueso</option>
                    <option value="syringe">💉 Jeringa</option>
                    <option value="activity">📈 Latido / Vitales</option>
                    <option value="microscope">🔬 Microscopio</option>
                    <option value="heart">❤️ Corazón Clínico</option>
                    <option value="droplet">💧 Gota / Fluido</option>
                </optgroup>
                <optgroup label="Premios y Logros">
                    <option value="award">🏆 Medalla Simple</option>
                    <option value="trophy">🏅 Trofeo de Oro</option>
                    <option value="medal">🎖️ Condecoración</option>
                    <option value="star">⭐ Estrella</option>
                    <option value="crown">👑 Corona</option>
                    <option value="shield-check">🛡️ Escudo Verificado</option>
                </optgroup>
                <optgroup label="Comunidad y Otros">
                    <option value="flame">🔥 Fuego / Racha</option>
                    <option value="zap">⚡ Rayo / Rápido</option>
                    <option value="paw-print">🐾 Huella de Mascota</option>
                    <option value="users">👥 Líder de Comunidad</option>
                    <option value="message-square">💬 Top Comentarista</option>
                    <option value="rocket">🚀 Pionero / Cohete</option>
                </optgroup>
            </select>
        </div>
    </div>

    <!-- SECCIÓN DE REQUISITOS DINÁMICOS -->
    <div x-data="{ reqType: 'manual' }" class="bg-slate-50 p-4 rounded-xl border border-slate-200 mt-4 space-y-3">
        <div>
            <label class="block text-sm font-bold text-slate-800 mb-1">¿Cómo se gana esta insignia?</label>
            <select name="requirement_type" x-model="reqType" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
                <option value="manual">🌟 Única (Asignación Manual por el Admin)</option>
                <option value="statuses">📝 Notas de Muro (Por cantidad de notas)</option>
                <option value="posts">💬 Posts de Foro (Por temas creados en el foro)</option>
                <option value="likes">❤️ Likes (Por cantidad de likes recibidos)</option>
            </select>
        </div>

        <div x-show="reqType !== 'manual'" x-transition style="display: none;">
            <label class="block text-sm font-bold text-slate-800 mb-1">Meta necesaria para ganarla:</label>
            <input type="number" name="requirement_goal" min="1" placeholder="Ej. 10" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
            <p class="text-xs text-slate-500 mt-1">El sistema asignará la insignia al llegar a esta meta.</p>
        </div>
    </div>
    
    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2.5 rounded-xl text-sm transition shadow-sm mt-2">
        Guardar Insignia
    </button>
</form>
    </div>

    <!-- COLUMNA DERECHA: Listado de Insignias Existentes -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
            <i data-lucide="list" class="w-5 h-5 text-emerald-600"></i> Insignias Registradas en el Sistema
        </h2>

        <div class="grid grid-cols-1 gap-4">
            @forelse($badges ?? [] as $badge)
                <!-- Tarjeta individual con Alpine para manejar la edición y los datos actuales -->
                <div x-data="{ editMode: false, reqType: '{{ $badge->requirement_type }}', colorBadge: '{{ $badge->color }}', iconBadge: '{{ $badge->icon }}' }" class="border border-slate-200 rounded-xl p-4 bg-slate-50 transition-all">
                    
                    <!-- VISTA DE LECTURA -->
                    <div x-show="!editMode" class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <!-- Icono dinámico con el color correcto -->
                            <div :class="'bg-' + colorBadge + '-100 text-' + colorBadge + '-600'" class="w-12 h-12 rounded-xl flex items-center justify-center font-bold shadow-sm border border-white">
                                <i data-lucide="{{ $badge->icon ?? 'award' }}" class="w-6 h-6"></i>
                            </div>
                            
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">{{ $badge->name }}</h3>
                                
                                <!-- Detalles agrupados correctamente -->
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold border-r border-slate-300 pr-2">Color: {{ $badge->color }}</span>
                                    
                                    <span class="text-[10px] text-slate-600 font-medium bg-white px-2 py-0.5 rounded border border-slate-200 shadow-sm flex items-center gap-1">
                                        @if($badge->requirement_type === 'manual')
                                            🌟 Asignación Manual
                                        @elseif($badge->requirement_type === 'statuses')
                                            📝 {{ $badge->requirement_goal }} notas en muro
                                        @elseif($badge->requirement_type === 'posts')
                                            💬 {{ $badge->requirement_goal }} posts en foro
                                        @elseif($badge->requirement_type === 'likes')
                                            ❤️ {{ $badge->requirement_goal }} likes
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción (Editar y Eliminar) -->
                        <div class="flex items-center gap-3">
                            <button @click="editMode = true" type="button" class="text-slate-400 hover:text-blue-500 transition" title="Editar insignia">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>

                            <form action="{{ route('admin.badges.destroy', $badge) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta insignia?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition" title="Eliminar insignia">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- MODO EDICIÓN (Formulario Desplegable) -->
                    <div x-show="editMode" x-transition style="display: none;" class="mt-4 pt-4 border-t border-slate-200">
                        <form action="{{ route('admin.badges.update', $badge) }}" method="POST" class="space-y-3">
                            @csrf @method('PUT')
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nombre de la Insignia</label>
                                <input type="text" name="name" value="{{ $badge->name }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-blue-500 bg-white">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Color</label>
                                    <select name="color" x-model="colorBadge" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-blue-500 bg-white">
                                        <option value="emerald">Esmeralda</option>
                                        <option value="blue">Azul</option>
                                        <option value="amber">Dorado</option>
                                        <option value="purple">Púrpura</option>
                                        <option value="rose">Rosa</option>
                                        <option value="orange">Naranja</option>
                                        <option value="slate">Plata / Gris</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Icono</label>
                                    <select name="icon" x-model="iconBadge" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-blue-500 bg-white">
                                        <option value="stethoscope">🩺 Estetoscopio</option>
                                        <option value="bone">🦴 Hueso</option>
                                        <option value="syringe">💉 Jeringa</option>
                                        <option value="activity">📈 Latido</option>
                                        <option value="microscope">🔬 Microscopio</option>
                                        <option value="heart">❤️ Corazón</option>
                                        <option value="droplet">💧 Gota</option>
                                        <option value="award">🏆 Medalla</option>
                                        <option value="trophy">🏅 Trofeo</option>
                                        <option value="medal">🎖️ Condecoración</option>
                                        <option value="star">⭐ Estrella</option>
                                        <option value="crown">👑 Corona</option>
                                        <option value="shield-check">🛡️ Escudo</option>
                                        <option value="flame">🔥 Fuego</option>
                                        <option value="zap">⚡ Rayo</option>
                                        <option value="paw-print">🐾 Huella</option>
                                        <option value="users">👥 Comunidad</option>
                                        <option value="message-square">💬 Comentarios</option>
                                        <option value="rocket">🚀 Cohete</option>
                                    </select>
                                </div>
                            </div>

                            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                                <div>
                                    <label class="block text-xs font-bold text-blue-900 mb-1">Requisito</label>
                                    <select name="requirement_type" x-model="reqType" class="w-full border border-blue-200 rounded-md px-2 py-1.5 text-xs focus:outline-none focus:border-blue-500">
                                        <option value="manual">🌟 Única (Manual)</option>
                                        <option value="statuses">📝 Notas de Muro</option>
                                        <option value="posts">💬 Posts de Foro</option>
                                        <option value="likes">❤️ Likes</option>
                                    </select>
                                </div>
                                <div x-show="reqType !== 'manual'" class="mt-2" style="display: none;">
                                    <label class="block text-xs font-bold text-blue-900 mb-1">Meta necesaria</label>
                                    <input type="number" name="requirement_goal" value="{{ $badge->requirement_goal }}" min="1" class="w-full border border-blue-200 rounded-md px-2 py-1.5 text-xs focus:outline-none focus:border-blue-500">
                                </div>
                            </div>
                            
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="editMode = false" class="text-xs px-4 py-2 font-bold text-slate-500 hover:bg-slate-200 rounded-lg transition">Cancelar</button>
                                <button type="submit" class="text-xs px-4 py-2 font-bold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>

                </div>
            @empty
                <div class="text-center py-12 text-slate-400 text-sm border-2 border-dashed border-slate-200 rounded-xl">
                    Aún no hay insignias creadas. ¡Usa el formulario de la izquierda para agregar la primera!
                </div>
            @endforelse
        </div>
    </div>
</div>