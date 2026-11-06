@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto my-10 px-4 sm:px-6">
    
    <!-- ENCABEZADO Y BOTÓN DE REGRESAR AL PERFIL -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Editar Perfil</h1>
            <p class="text-sm text-slate-500 mt-1">Personaliza tu espacio, actualiza tu avatar y destaca en la comunidad.</p>
        </div>
        <a href="{{ route('users.public', auth()->user()) }}" class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2 shadow-sm text-sm">
            <i data-lucide="external-link" class="w-4 h-4"></i> Ver Perfil Público
        </a>
    </div>

    <!-- ALERTAS DE ERROR O ÉXITO -->
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm mb-6 flex items-start gap-3 shadow-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <div>
                <span class="font-bold">Hubo un problema:</span>
                <ul class="list-disc pl-5 mt-1 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-2xl text-sm mb-6 flex items-center gap-3 shadow-sm">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- ========================================== -->
        <!-- BLOQUE 1: APARIENCIA DEL PERFIL            -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                    <i data-lucide="image" class="w-5 h-5 text-emerald-600"></i> Apariencia del Perfil
                </h2>
            </div>
            
            <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <!-- Avatar -->
                <div class="flex items-center gap-5">
                    <div class="shrink-0">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover ring-4 ring-slate-50 shadow-sm border border-slate-200">
                        @else
                            <div class="w-20 h-20 rounded-full bg-emerald-600 text-white font-bold text-3xl flex items-center justify-center ring-4 ring-slate-50 shadow-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Fotografía de Perfil</label>
                        <input type="file" name="avatar" accept="image/*" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer transition">
                        <p class="text-[11px] text-slate-400 mt-2 font-medium">Formatos recomendados: JPG, PNG, WEBP.</p>
                    </div>
                </div>

                <!-- Color de Portada -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Color de Portada</label>
                    <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <div class="w-14 h-14 rounded-xl overflow-hidden border-2 border-white shadow-sm shrink-0">
                            <input type="color" name="banner_color" value="{{ auth()->user()->banner_color ?? '#1e293b' }}" class="w-16 h-16 -m-1 cursor-pointer">
                        </div>
                        <div class="text-xs text-slate-500">
                            <span class="font-bold text-slate-700 block text-sm">Fondo Personalizado</span>
                            Haz clic en el cuadro para abrir la paleta.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- BLOQUE 2: INFORMACIÓN PERSONAL             -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-blue-600"></i> Información Personal
                </h2>
            </div>
            
            <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Nombre Completo</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100 transition shadow-inner">
                </div>
                
                <!-- Nombre de Usuario (Username) -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nombre de Usuario (Único)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 font-black">@</span>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required class="w-full pl-10 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition" placeholder="ejemplo: l3bo09">
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Sin espacios, solo letras, números, guiones bajos o puntos.</p>
                </div>
                
                <!-- Firma / Biografía Completa -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Biografía / Presentación Profesional</label>
                    <textarea name="bio" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition resize-none" placeholder="Cuéntale a la comunidad sobre tu experiencia, enfoque clínico o trayectoria...">{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- BLOQUE 3: INFORMACIÓN PROFESIONAL Y CLÍNICA-->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden" x-data="{ status: '{{ old('professional_status', $user->professional_status ?? 'titulado') }}' }">
            <div class="bg-slate-50 border-b border-slate-100 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                    <i data-lucide="stethoscope" class="w-5 h-5 text-indigo-600"></i> Perfil Profesional y Clínica
                </h2>
            </div>
            
            <div class="p-6 sm:p-8 space-y-8">
                
                <!-- Credenciales y Especialidad -->
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Credenciales y Áreas de Interés</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Estatus Profesional</label>
                            <select name="professional_status" x-model="status" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition appearance-none bg-white">
                                <option value="estudiante">Estudiante / MVZ en Formación</option>
                                <option value="pasante">Pasante</option>
                                <option value="titulado">Médico Titulado</option>
                                <option value="especialista">Especialista</option>
                            </select>
                        </div>

                        <!-- Se oculta si es estudiante usando x-show -->
                        <div x-show="status !== 'estudiante'" x-transition>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Cédula Profesional</label>
                            <input type="text" name="cedula" value="{{ old('cedula', $user->cedula) }}" placeholder="Ej. 12345678" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Especialidad Principal</label>
                            <select name="specialty" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition appearance-none bg-white">
                                <option value="">Selecciona...</option>
                                @foreach($specialties as $item)
                                    <option value="{{ $item }}" {{ old('specialty', $user->specialty) == $item ? 'selected' : '' }}>
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Especialidad Secundaria (Opcional)</label>
                            <input type="text" name="specialty_secondary" value="{{ old('specialty_secondary', $user->specialty_secondary) }}" placeholder="Ej. Cirugía Ortopédica" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition">
                        </div>
                    </div>
                </div>

                <!-- Práctica Clínica -->
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Clínica y Servicios</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Nombre de Clínica / Hospital / Rancho</label>
                            <input type="text" name="clinic_name" value="{{ old('clinic_name', $user->clinic_name) }}" placeholder="Ej. Veterinaria Ideal" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition">
                        </div>

                        <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                            <input type="checkbox" name="offers_emergencies" value="1" {{ old('offers_emergencies', $user->offers_emergencies) ? 'checked' : '' }} class="w-5 h-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <span class="text-sm font-bold text-slate-700">Atiendo emergencias 24/7</span>
                        </label>

                        <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                            <input type="checkbox" name="home_visits" value="1" {{ old('home_visits', $user->home_visits) ? 'checked' : '' }} class="w-5 h-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <span class="text-sm font-bold text-slate-700">Realizo visitas a rancho / domicilio</span>
                        </label>
                    </div>
                </div>

                <!-- Ubicación -->
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Ubicación Geográfica</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Ciudad / Municipio</label>
                            <input type="text" name="city" value="{{ old('city', $user->city) }}" placeholder="Ej. Culiacán" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Estado</label>
                            <input type="text" name="state" value="{{ old('state', $user->state) }}" placeholder="Ej. Sinaloa" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">País</label>
                            <input type="text" name="country" value="{{ old('country', $user->country ?? 'México') }}" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition">
                        </div>
                    </div>
                </div>

                <!-- Contacto y Redes -->
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Contacto y Redes Sociales</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i data-lucide="message-circle" class="w-5 h-5 text-emerald-500"></i></span>
                            <input type="text" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="WhatsApp (Ej. 6671234567)" class="w-full pl-11 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition">
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i data-lucide="globe" class="w-5 h-5"></i></span>
                            <input type="url" name="website" value="{{ old('website', $user->website) }}" placeholder="Sitio Web de la clínica" class="w-full pl-11 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition">
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i data-lucide="facebook" class="w-5 h-5 text-blue-600"></i></span>
                            <input type="url" name="facebook" value="{{ old('facebook', $user->facebook) }}" placeholder="URL de Facebook" class="w-full pl-11 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition">
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i data-lucide="instagram" class="w-5 h-5 text-rose-500"></i></span>
                            <input type="text" name="instagram" value="{{ old('instagram', $user->instagram) }}" placeholder="Usuario de Instagram (Ej. @l3bo09)" class="w-full pl-11 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- BLOQUE 4: SEGURIDAD                        -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                    <i data-lucide="shield" class="w-5 h-5 text-rose-600"></i> Seguridad y Contraseña
                </h2>
            </div>
            
            <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Nueva Contraseña</label>
                    <input type="password" name="password" placeholder="Dejar en blanco para no cambiarla" class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100 transition shadow-inner">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" placeholder="Repite la nueva contraseña" class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100 transition shadow-inner">
                </div>
            </div>
        </div>

        <!-- BOTÓN DE GUARDAR CAMBIOS -->
        <div class="flex justify-end pt-2 pb-10">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-8 py-3.5 rounded-2xl text-sm transition shadow-lg shadow-emerald-200 flex items-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i> Guardar Cambios del Perfil
            </button>
        </div>
    </form>
</div>
@endsection