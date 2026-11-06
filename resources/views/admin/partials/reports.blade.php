<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Gestión de Reportes</h2>
            <p class="text-sm text-slate-500">Revisa las denuncias de perfiles enviadas por los usuarios.</p>
        </div>
        <div class="flex gap-4 text-sm">
            <span class="text-amber-600 font-bold bg-amber-50 px-3 py-1 rounded-lg border border-amber-100">{{ $reports->where('status', 'pendiente')->count() }} Pendientes</span>
            <span class="text-emerald-600 font-bold bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100">{{ $reports->where('status', 'resuelto')->count() }} Resueltos</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold">
                    <th class="px-6 py-4">Fecha</th>
                    <th class="px-6 py-4">Denunciante</th>
                    <th class="px-6 py-4">Reportado</th>
                    <th class="px-6 py-4 min-w-[300px]">Motivo / Evidencia</th>
                    <th class="px-6 py-4 text-center">Estado</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($reports as $report)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-medium">
                            {{ $report->created_at->format('d/m/Y H:i') }}
                        </td>
                        
                        <td class="px-6 py-4">
                            <a href="{{ route('users.public', $report->reporter) }}" target="_blank" class="font-bold text-slate-800 hover:text-emerald-600 transition flex items-center gap-2">
                                <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-400"></i>
                                {{ $report->reporter->name }}
                            </a>
                        </td>
                        
                        <td class="px-6 py-4">
                            <a href="{{ route('users.public', $report->reported) }}" target="_blank" class="font-bold text-rose-600 hover:text-rose-700 transition flex items-center gap-2">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-400"></i>
                                {{ $report->reported->name }}
                            </a>
                        </td>
                        
                        <td class="px-6 py-4 min-w-[250px]">
                            <span class="font-bold text-slate-800 block mb-1">{{ $report->reason }}</span>
                            
                            <!-- EVIDENCIA: Muestra la publicación si fue reportada -->
                            @if($report->status_id && $report->statusPost)
                                <div class="mt-2 p-3 bg-rose-50 border border-rose-100 rounded-xl text-xs text-slate-700">
                                    <span class="font-bold text-rose-600 mb-1 flex items-center gap-1.5">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Publicación Reportada:
                                    </span>
                                    <p class="line-clamp-2 italic">"{{ $report->statusPost->content }}"</p>
                                </div>
                            @elseif($report->status_id && !$report->statusPost)
                                <span class="text-xs font-bold text-slate-400 italic block mt-2">La publicación ya fue eliminada.</span>
                            @endif

                            @if($report->description)
                                <p class="text-xs text-slate-500 mt-2 line-clamp-2" title="{{ $report->description }}">
                                    <span class="font-bold text-slate-400">Detalles:</span> "{{ $report->description }}"
                                </p>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if($report->status === 'pendiente')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i> Pendiente
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Resuelto
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                
                                @if($report->status === 'pendiente')
                                    <!-- Botón para BORRAR LA PUBLICACIÓN reportada -->
                                    @if($report->status_id && $report->statusPost)
                                        <form action="{{ route('admin.reports.deleteStatus', $report) }}" method="POST" onsubmit="return confirm('¿Borrar definitivamente esta publicación de la plataforma?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Eliminar Publicación">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Post
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Botón normal para solo marcar como resuelto -->
                                    <form action="{{ route('admin.reports.resolve', $report) }}" method="POST" onsubmit="return confirm('¿Marcar este reporte como resuelto sin borrar nada?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Solo marcar como Resuelto">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('admin.reports.destroy', $report) }}" method="POST" onsubmit="return confirm('¿Eliminar este reporte permanentemente de tu registro?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 rounded-lg text-xs font-bold transition shadow-sm" title="Eliminar Registro del Reporte">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 mb-3">
                                <i data-lucide="shield-check" class="w-6 h-6 text-emerald-500"></i>
                            </div>
                            <p class="text-slate-500 font-bold">Todo tranquilo. No hay reportes en el sistema.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>