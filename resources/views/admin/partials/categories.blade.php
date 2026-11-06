<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Categorías del Foro</h2>
        </div>
        <button x-on:click="modalCat = true" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Nueva Categoría
        </button>
    </div>

    <table class="w-full text-left border-collapse text-sm">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <th class="py-3 px-4">Nombre</th>
                <th class="py-3 px-4 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($categories as $cat)
            <tr>
                <td class="py-3 px-4 font-semibold flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full" @style(['background-color: ' . $cat->color])></span>
                    {{ $cat->name }}
                </td>
                <td class="py-3 px-4 text-right">
                    <form action="{{ route('admin.categories.delete', $cat) }}" method="POST" onsubmit="return confirm('¿Eliminar categoría?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-500 hover:underline text-xs font-medium">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal -->
    <div x-show="modalCat" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" style="display: none;">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl" x-on:click.outside="modalCat = false">
            <h3 class="text-lg font-bold mb-4">Nueva Categoría</h3>
            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Nombre</label>
                    <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Descripción</label>
                    <textarea name="description" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Color</label>
                    <input type="color" name="color" value="#10b981" class="w-12 h-9 border rounded cursor-pointer">
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" x-on:click="modalCat = false" class="px-4 py-2 border rounded-lg text-sm">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>