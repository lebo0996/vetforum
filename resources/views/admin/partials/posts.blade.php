<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h2 class="text-lg font-bold text-slate-900 mb-4">Moderación de Publicaciones</h2>
    <table class="w-full text-left border-collapse text-sm">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <th class="py-3 px-4">Título</th>
                <th class="py-3 px-4 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($posts as $post)
            <tr>
                <td class="py-3 px-4 font-medium">
                    @if($post->is_pinned) 
                        <span class="bg-amber-500 text-white text-xs px-2 py-0.5 rounded mr-1">Fijado</span> 
                    @endif
                    <a href="{{ route('forum.show', $post->slug) }}" class="hover:text-emerald-600">{{ $post->title }}</a>
                </td>
                <td class="py-3 px-4 text-right space-x-2">
                    <form action="{{ route('admin.posts.pin', $post) }}" method="POST" class="inline">
                        @csrf
                        <button class="text-amber-600 hover:underline text-xs font-medium">Fijar</button>
                    </form>
                    <form action="{{ route('admin.posts.delete', $post) }}" method="POST" onsubmit="return confirm('¿Eliminar publicación?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-500 hover:underline text-xs font-medium">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>