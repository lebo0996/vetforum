<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->site_name ?? 'Veterinaria Ideal' }}</title>
    <!-- Favicon Dinámico -->
    @if(isset($settings) && $settings->favicon)
        <link rel="icon" href="{{ asset('storage/' . $settings->favicon) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $settings->favicon) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#059669">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen font-sans" x-data="{ mobileMenuOpen: false }">

    <!-- NAVBAR -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between max-w-7xl">
            <div class="flex items-center gap-3 sm:gap-6">
                
                <!-- BOTÓN MENÚ HAMBURGUESA (MÓVIL) -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600 hover:text-emerald-600 hover:bg-slate-100 rounded-xl transition focus:outline-none">
                    <i data-lucide="menu" class="w-6 h-6" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-6 h-6" x-show="mobileMenuOpen" style="display: none;"></i>
                </button>

                <!-- LOGO Y NOMBRE -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-emerald-600 font-bold text-xl">
                    @if(isset($settings) && $settings->logo)
                        <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="h-8 sm:h-9 w-auto object-contain">
                    @else
                        <i data-lucide="stethoscope" class="w-6 h-6"></i>
                    @endif
                    <span class="hidden sm:inline-block">{{ $settings->site_name ?? 'Veterinaria Ideal' }}</span>
                </a>

                <!-- NAVEGACIÓN ESCRITORIO -->
                <nav class="hidden md:flex items-center gap-2 text-sm font-medium text-slate-600">
                    <a href="{{ route('home') }}" class="flex items-center gap-1.5 hover:text-emerald-600 px-3 py-2 rounded-lg transition {{ request()->routeIs('home') ? 'text-emerald-600 bg-emerald-50 font-semibold' : '' }}">
                        <i data-lucide="home" class="w-4 h-4"></i> Inicio
                    </a>
                    <a href="{{ route('forum.index') }}" class="flex items-center gap-1.5 hover:text-emerald-600 px-3 py-2 rounded-lg transition {{ request()->routeIs('forum.*') ? 'text-emerald-600 bg-emerald-50 font-semibold' : '' }}">
                        <i data-lucide="message-square" class="w-4 h-4"></i> Foro Clínico
                    </a>
                    <a href="{{ route('statuses.index') }}" class="flex items-center gap-1.5 hover:text-emerald-600 px-3 py-2 rounded-lg transition {{ request()->routeIs('statuses.*') ? 'text-emerald-600 bg-emerald-50 font-semibold' : '' }}">
                        <i data-lucide="globe" class="w-4 h-4"></i> Muro Social
                    </a>
                    <a href="{{ route('users.directory') }}" class="flex items-center gap-1.5 hover:text-emerald-600 px-3 py-2 rounded-lg transition {{ request()->routeIs('users.directory') ? 'text-emerald-600 bg-emerald-50 font-semibold' : '' }}">
                        <i data-lucide="users" class="w-4 h-4"></i> Colegas
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                @auth
                    <!-- BOTÓN DE MENSAJES / CHAT -->
                    <a href="{{ route('chat.index') }}" class="relative p-2 text-slate-500 hover:text-emerald-600 hover:bg-slate-100 rounded-full transition focus:outline-none flex items-center justify-center" title="Mensajes Privados">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                        @php
                            $unreadMessagesCount = \App\Models\Message::where('receiver_id', auth()->id())->whereNull('read_at')->count();
                        @endphp
                        @if($unreadMessagesCount > 0)
                            <span class="absolute top-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm ring-2 ring-white">
                                {{ $unreadMessagesCount }}
                            </span>
                        @endif
                    </a>

                    <!-- CAMPANITA DE NOTIFICACIONES -->
                    <div class="relative" x-data="{ openNotifications: false }">
                        <button x-on:click="openNotifications = !openNotifications" x-on:click.away="openNotifications = false" class="relative p-2 text-slate-500 hover:text-emerald-600 hover:bg-slate-100 rounded-full transition focus:outline-none flex items-center justify-center">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm ring-2 ring-white">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>

                        <!-- CAJA DESPLEGABLE DE NOTIFICACIONES CORREGIDA -->
                        <div x-show="openNotifications" style="display: none;" class="absolute right-[-60px] sm:right-0 mt-2 w-72 sm:w-80 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50">
                            <div class="px-4 py-2.5 border-b border-slate-100 flex justify-between items-center">
                                <h3 class="font-bold text-slate-800 text-sm">Notificaciones</h3>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <form action="{{ route('notifications.read') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-emerald-600 hover:underline font-semibold">Marcar leídas</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                @forelse(auth()->user()->notifications as $notification)
                                    <a href="{{ $notification->data['url'] ?? '#' }}" class="flex items-start gap-3 p-3.5 hover:bg-slate-50 transition {{ $notification->read_at ? 'opacity-60' : 'bg-emerald-50/30' }}">
                                        @if(isset($notification->data['follower_name']))
                                            @if(isset($notification->data['follower_avatar']) && $notification->data['follower_avatar'])
                                                <img src="{{ asset('storage/' . $notification->data['follower_avatar']) }}" class="w-9 h-9 rounded-full object-cover shrink-0 border border-slate-200">
                                            @else
                                                <div class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                                    {{ strtoupper(substr($notification->data['follower_name'] ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="text-xs text-slate-700 leading-relaxed">
                                                    <span class="font-bold text-slate-900">{{ $notification->data['follower_name'] }}</span> 
                                                    {{ $notification->data['message'] ?? 'ha comenzado a seguirte' }}
                                                </p>
                                                <span class="text-[10px] text-slate-400 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                                            </div>
                                        @elseif(isset($notification->data['message']))
                                            <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                                                <i data-lucide="{{ $notification->data['icon'] ?? 'bell' }}" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-slate-700 leading-relaxed">
                                                    {!! str_replace('"', '<span class="font-bold text-slate-900">"', $notification->data['message']) !!}
                                                </p>
                                                <span class="text-[10px] text-slate-400 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                                            </div>
                                        @endif
                                    </a>
                                @empty
                                    <div class="p-6 text-center text-xs text-slate-400">No tienes notificaciones nuevas.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- MENÚ DESPLEGABLE DE USUARIO -->
                    <div class="relative" x-data="{ openMenu: false }">
                        <button x-on:click="openMenu = !openMenu" x-on:click.away="openMenu = false" class="flex items-center gap-2.5 focus:outline-none bg-slate-50 hover:bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200 transition">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                            @else
                                <span class="w-8 h-8 rounded-full bg-emerald-600 text-white font-semibold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            @endif
                            <span class="text-sm font-semibold text-slate-700 hidden sm:inline">{{ auth()->user()->name }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                        </button>

                        <div x-show="openMenu" x-transition style="display: none;" class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-100 sm:hidden">
                                <p class="text-xs text-slate-400 font-bold">Conectado como</p>
                                <p class="text-sm font-black text-slate-800 truncate">{{ auth()->user()->name }}</p>
                            </div>

                            <a href="{{ route('users.public', auth()->user()) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 font-medium">
                                <i data-lucide="eye" class="w-4 h-4 text-slate-400"></i> Ver Mi Perfil
                            </a>
                            <a href="{{ route('profile.show') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 font-medium">
                                <i data-lucide="user" class="w-4 h-4 text-slate-400"></i> Editar Mi Perfil
                            </a>

                            <!-- ENLACES DE MODERACIÓN Y ADMIN EN EL MENÚ -->
                            @if(in_array(auth()->user()->role, ['admin', 'moderator']))
                                <div class="border-t border-slate-100 my-1"></div>
                                <a href="{{ route('moderator.groups.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-amber-600 hover:bg-amber-50 font-bold">
                                    <i data-lucide="shield" class="w-4 h-4 text-amber-500"></i> Panel Moderación
                                </a>
                            @endif

                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-purple-600 hover:bg-purple-50 font-bold">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-purple-500"></i> Panel Admin Global
                                </a>
                            @endif

                            <div class="border-t border-slate-100 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 text-left font-semibold">
                                    <i data-lucide="log-out" class="w-4 h-4"></i> Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="text-slate-700 hover:bg-slate-100 px-3 py-2 rounded-md text-sm font-medium flex items-center gap-1.5 border border-slate-200">
                        <i data-lucide="log-in" class="w-4 h-4"></i> <span class="hidden sm:inline">Iniciar Sesión</span>
                    </a>
                    <a href="{{ route('register') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                        Registrarse
                    </a>
                @endguest
            </div>
        </div>

        <!-- MENÚ MÓVIL DESPLEGABLE (HAMBURGUESA) -->
        <div x-show="mobileMenuOpen" x-transition style="display: none;" class="md:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-2 shadow-lg">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm font-bold {{ request()->routeIs('home') ? 'text-emerald-600 bg-emerald-50' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="home" class="w-5 h-5"></i> Inicio
            </a>
            <a href="{{ route('forum.index') }}" class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm font-bold {{ request()->routeIs('forum.*') ? 'text-emerald-600 bg-emerald-50' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="message-square" class="w-5 h-5"></i> Foro Clínico
            </a>
            <a href="{{ route('statuses.index') }}" class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm font-bold {{ request()->routeIs('statuses.*') ? 'text-emerald-600 bg-emerald-50' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="globe" class="w-5 h-5"></i> Muro Social
            </a>
            <a href="{{ route('users.directory') }}" class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm font-bold {{ request()->routeIs('users.directory') ? 'text-emerald-600 bg-emerald-50' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="users" class="w-5 h-5"></i> Colegas
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="container mx-auto px-4 mt-4 max-w-7xl">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2 shadow-sm">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200 mt-16">
        <div class="container mx-auto px-4 py-12 max-w-7xl">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2 text-emerald-600 font-bold text-lg mb-3">
                        @if(isset($settings) && $settings->logo)
                            <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="h-8 w-auto object-contain">
                        @else
                            <i data-lucide="stethoscope" class="w-5 h-5"></i>
                        @endif
                        <span class="text-slate-900">{{ $settings->site_name ?? 'Veterinaria Ideal' }}</span>
                    </div>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        {{ $settings->site_description ?? 'Comunidad dedicada a profesionales y estudiantes de medicina veterinaria y ganadería.' }}
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-900 text-sm mb-3">Enlaces Rápidos</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="{{ route('home') }}" class="hover:text-emerald-600">Inicio</a></li>
                        <li><a href="{{ route('forum.index') }}" class="hover:text-emerald-600">Foro Clínico</a></li>
                        <li><a href="{{ route('statuses.index') }}" class="hover:text-emerald-600">Muro Social</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-900 text-sm mb-3">Categorías Principales</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li>Bovinos y Ganadería</li>
                        <li>Pequeñas Especies</li>
                        <li>Farmacología</li>
                        <li>Casos Clínicos</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-900 text-sm mb-3">Contacto</h4>
                    <p class="text-sm text-slate-500 flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4 text-emerald-600"></i>
                        {{ $settings->contact_email ?? 'contacto@veterinariaideal.com' }}
                    </p>
                </div>
            </div>
            <div class="border-t border-slate-100 mt-8 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400">
                <p>© {{ date('Y') }} {{ $settings->site_name ?? 'Veterinaria Ideal' }}. Todos los derechos reservados.</p>
                <p>Hecho con ❤️ para la comunidad veterinaria</p>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    console.log('PWA Service Worker registrado', reg.scope);
                }).catch(err => {
                    console.log('Error al registrar PWA:', err);
                });
            });
        }
    </script>
</body>
</html>