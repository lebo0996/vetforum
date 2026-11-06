<div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800">Gestión de Especialidades</h2>
        <p class="text-sm text-slate-500">Crea y administra las áreas oficiales que los médicos podrán elegir en su perfil.</p>
    </div>

    <!-- Formulario para agregar -->
    <form action="{{ route('admin.specialties.store') }}" method="POST" class="flex flex-col sm:flex-row gap-3 mb-8 bg-slate-50 p-4 rounded-xl border border-slate-200">
        @csrf
        <div class="flex-1 relative">
            <i data-lucide="activity" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
            <input type="text" name="name" placeholder="Ej: Fisioterapia Veterinaria" required class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 bg-white">
        </div>
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-2 rounded-lg text-sm transition shadow-sm whitespace-nowrap">
            Agregar Especialidad
        </button>
    </form>

    <!-- Tabla de listado -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600 border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-700">
                <tr>
                    <th class="px-4 py-3 font-semibold">Nombre de la Especialidad</th>
                    <th class="px-4 py-3 font-semibold text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($specialties as $specialty)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $specialty->name }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('admin.specialties.delete', $specialty) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta especialidad?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition" title="Eliminar Especialidad">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-8 text-center text-slate-500">
                            No hay especialidades registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>