<div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800">Gestión de Usuarios</h2>
        <p class="text-sm text-slate-500">Administra los roles, verifica cuentas, asigna insignias especiales y modera el acceso a la plataforma.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600 border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-700">
                <tr>
                    <th class="px-4 py-3 font-semibold">Usuario</th>
                    <th class="px-4 py-3 font-semibold">Rol</th>
                    <th class="px-4 py-3 font-semibold">Estado</th>
                    <th class="px-4 py-3 font-semibold text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                    <!-- Alpine x-data ahora maneja tanto el modal de edición como el menú de opciones de moderación -->
                    <tr class="hover:bg-slate-50 transition" x-data="{ showModal: false, openOptions: false }">
                        
                        <!-- Info del Usuario -->
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-bold text-slate-800 flex items-center gap-1">
                                        {{ $user->name }}
                                        @if($user->is_verified)
                                            <i data-lucide="badge-check" class="w-4 h-4 text-blue-500 fill-blue-100"></i>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500">{{ '@' . ($user->username ?? 'sin_usuario') }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Rol -->
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>

                        <!-- Estado Visual (Incluye Baneo y Suspensión) -->
                        <td class="px-4 py-4">
                            @if(!$user->is_active)
                                <span class="text-rose-600 font-semibold text-xs flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-rose-500"></div> Baneado</span>
                            @elseif($user->suspended_until && now()->lessThan($user->suspended_until))
                                <span class="text-amber-600 font-semibold text-xs flex items-center gap-1" title="Hasta: {{ $user->suspended_until->format('d/m/Y H:i') }}"><div class="w-2 h-2 rounded-full bg-amber-500"></div> Suspendido</span>
                            @elseif($user->is_verified)
                                <span class="text-emerald-600 font-semibold text-xs flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-emerald-500"></div> Verificado</span>
                            @else
                                <span class="text-slate-400 font-semibold text-xs flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-slate-300"></div> Estándar</span>
                            @endif
                        </td>

                        <!-- Acciones -->
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <!-- Botón Toggle Verificación Inmediata -->
                                <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg transition {{ $user->is_verified ? 'text-blue-500 hover:bg-blue-50' : 'text-slate-400 hover:text-blue-500 hover:bg-blue-50' }}" title="{{ $user->is_verified ? 'Quitar Verificación' : 'Verificar Usuario' }}">
                                        <i data-lucide="{{ $user->is_verified ? 'shield-minus' : 'shield-check' }}" class="w-4 h-4"></i>
                                    </button>
                                </form>

                                <!-- Botón para abrir Modal de Edición (Roles e Insignias) -->
                                <button type="button" @click="showModal = true" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Editar Rol e Insignias">
                                    <i data-lucide="settings-2" class="w-4 h-4"></i>
                                </button>

                                <!-- Menú Desplegable de Sanciones / Moderación -->
                                <div class="relative inline-block text-left">
                                    <button type="button" @click="openOptions = !openOptions" @click.away="openOptions = false" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Opciones de Moderación">
                                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                                    </button>

                                    <div x-show="openOptions" style="display: none;" class="origin-top-right absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-slate-100 z-50">
                                        
                                        <!-- Opciones de Suspensión -->
                                        <div class="p-2 space-y-1">
                                            <span class="block px-2 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-left">Suspender</span>
                                            <form action="{{ route('admin.users.suspend', $user) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="days" value="1">
                                                <button type="submit" class="w-full text-left px-2 py-1.5 text-xs text-amber-600 hover:bg-amber-50 rounded-lg transition font-bold">Por 24 horas</button>
                                            </form>
                                            <form action="{{ route('admin.users.suspend', $user) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="days" value="7">
                                                <button type="submit" class="w-full text-left px-2 py-1.5 text-xs text-amber-600 hover:bg-amber-50 rounded-lg transition font-bold">Por 7 días</button>
                                            </form>
                                        </div>

                                        <!-- Opciones de Baneo -->
                                        <div class="p-2">
                                            <form action="{{ route('admin.users.ban', $user) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas @if($user->is_active) desactivar @else reactivar @endif esta cuenta?')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="w-full text-left px-2 py-1.5 text-xs {{ $user->is_active ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded-lg transition font-bold flex items-center gap-2">
                                                    <i data-lucide="{{ $user->is_active ? 'ban' : 'user-check' }}" class="w-3.5 h-3.5"></i>
                                                    {{ $user->is_active ? 'Banear Cuenta' : 'Reactivar Cuenta' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODAL DE EDICIÓN DEL USUARIO -->
                            <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                    
                                    <div x-show="showModal" x-transition.opacity class="fixed inset-0 transition-opacity bg-slate-900/50" @click="showModal = false"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                    <div x-show="showModal" x-transition class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg w-full sm:p-6 text-slate-800">
                                        <div class="absolute top-0 right-0 hidden pt-4 pr-4 sm:block">
                                            <button @click="showModal = false" type="button" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                                                <i data-lucide="x" class="w-5 h-5"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                            <h3 class="text-lg font-black leading-6 text-slate-900" id="modal-title">
                                                Editar Perfil de {{ $user->name }}
                                            </h3>
                                            
                                            <div class="mt-6 space-y-6">
                                                <!-- Formulario para Editar Rol -->
                                                <form action="{{ route('admin.users.role', $user) }}" method="POST" class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                                    @csrf
                                                    <label class="block text-sm font-bold text-slate-700 mb-2">Rol del Usuario</label>
                                                    <div class="flex gap-2">
                                                        <select name="role" class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Usuario Normal</option>
                                                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                                                        </select>
                                                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition">
                                                            Actualizar Rol
                                                        </button>
                                                    </div>
                                                </form>

                                                <!-- Formulario para Asignar Insignias -->
                                                <form action="{{ route('admin.badges.assign', $user) }}" method="POST" class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                                    @csrf
                                                    <label class="block text-sm font-bold text-slate-700 mb-2">Insignias Manuales y Ganadas</label>
                                                    
                                                    <div class="grid grid-cols-2 gap-2 mb-4 max-h-40 overflow-y-auto p-2 bg-white border border-slate-200 rounded-lg">
                                                        @forelse($badges ?? [] as $badge)
                                                            <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-slate-50 p-1.5 rounded-lg transition border border-transparent hover:border-slate-200">
                                                                <input type="checkbox" name="badges[]" value="{{ $badge->id }}" 
                                                                    {{ $user->badges->contains($badge->id) ? 'checked' : '' }}
                                                                    class="text-blue-600 rounded focus:ring-blue-500 w-4 h-4 mt-0.5">
                                                                
                                                                <div class="flex items-center gap-1.5 bg-{{ $badge->color }}-100 text-{{ $badge->color }}-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border border-{{ $badge->color }}-200">
                                                                    <i data-lucide="{{ $badge->icon }}" class="w-3.5 h-3.5"></i>
                                                                    {{ $badge->name }}
                                                                </div>
                                                            </label>
                                                        @empty
                                                            <p class="text-xs text-slate-500 col-span-2 text-center py-2">No hay insignias creadas en el sistema.</p>
                                                        @endforelse
                                                    </div>
                                                    
                                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition">
                                                        Guardar Insignias Seleccionadas
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>