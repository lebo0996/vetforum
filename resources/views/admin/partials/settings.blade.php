<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- IMPORTANTE: Agregamos enctype="multipart/form-data" -->
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-900">Configuración General del Foro</h2>
            <p class="text-sm text-slate-500">Estos cambios se guardan directamente en la base de datos.</p>
        </div>
        
        <div class="p-6 space-y-5">
            
            <!-- ========================================== -->
            <!-- SECCIÓN NUEVA: IDENTIDAD VISUAL            -->
            <!-- ========================================== -->
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl space-y-4 mb-2">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="image" class="w-4 h-4 text-emerald-600"></i> Identidad Visual
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Logo Principal -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Logo Principal (PNG, SVG, WEBP)</label>
                        <div class="flex items-center gap-4">
                            @if(isset($settings) && $settings->logo)
                                <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex items-center justify-center p-2 shrink-0">
                                    <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="max-w-full max-h-full object-contain">
                                </div>
                            @endif
                            <input type="file" name="logo" accept=".png, .jpg, .jpeg, .webp, .svg" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition">
                        </div>
                    </div>

                    <!-- Favicon -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Favicon (.ICO, .PNG)</label>
                        <div class="flex items-center gap-4">
                            @if(isset($settings) && $settings->favicon)
                                <div class="w-12 h-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center p-1 shrink-0">
                                    <img src="{{ asset('storage/' . $settings->favicon) }}" alt="Favicon" class="max-w-full max-h-full object-contain">
                                </div>
                            @endif
                            <input type="file" name="favicon" accept=".ico, .png" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TEXTOS ORIGINALES                          -->
            <!-- ========================================== -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del Foro</label>
                    <input type="text" name="site_name" value="{{ $settings->site_name ?? '' }}" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Correo de Contacto</label>
                    <input type="email" name="contact_email" value="{{ $settings->contact_email ?? '' }}" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm focus:outline-none focus:border-emerald-500">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción del Foro</label>
                <textarea name="site_description" rows="3" class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm focus:outline-none focus:border-emerald-500">{{ $settings->site_description ?? '' }}</textarea>
            </div>
            
            <div class="pt-4 border-t border-slate-100 space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="allow_registrations" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500" @if($settings->allow_registrations ?? true) checked @endif>
                    <span class="text-sm font-medium text-slate-800">Permitir el registro público</span>
                </label>
            </div>
            
        </div>
        
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-5 py-2 rounded-lg text-sm transition">
                Guardar Ajustes
            </button>
        </div>
    </form>
</div>