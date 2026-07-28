<nav x-data="{ open: false }" class="relative z-50 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="w-full px-4 sm:px-6 lg:px-4">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('img/LOGO-DE-PSICOLOGIA-GRISOSCURO.png') }}" alt="Logo Psico-Guía UPTP" class="h-8 w-auto object-contain dark:hidden" />
                        <img src="{{ asset('img/LOGO-DE-PSICOLOGIA-BLANCO.png') }}" alt="Logo Psico-Guía UPTP" class="h-8 w-auto object-contain hidden dark:block" />
                        <span class="font-bold text-base text-gray-900 dark:text-white">Psico-Guía</span>
                    </a>
                </div>
            </div>

            <!-- Notifications, Theme Switcher & User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <!-- Notifications Dropdown -->
                <x-notifications-dropdown />

                {{-- THEME SWITCHER - Botón para cambiar tema --}}
                <x-theme-switcher />

                {{-- NUEVO BOTÓN DE USUARIO CON NOMBRE, ROL Y FOTO --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="group flex items-center gap-3 px-3 py-1.5 rounded-full bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200 border border-transparent hover:border-gray-200 dark:hover:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            {{-- Info del usuario (nombre + rol) --}}
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-800 dark:text-white leading-tight">
                                    {{ Auth::user()->nombres }} {{ Auth::user()->apellidos }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 leading-tight">
                                    @php
                                        $roleLabels = [
                                            'estudiante' => 'Estudiante',
                                            'obrero' => 'Obrero',
                                            'psicologo' => 'Psicólogo',
                                            'admin' => 'Administrador',
                                        ];
                                    @endphp
                                    {{ $roleLabels[Auth::user()->role] ?? ucfirst(Auth::user()->role) }}
                                </div>
                            </div>

                            {{-- Foto de perfil o iniciales --}}
                            <div class="flex-shrink-0">
                                @php
                                    $user = Auth::user();
                                    $photoPath = $user->profile_photo_path;

                                    // Verificar si existe la foto
                                    $hasPhoto = !empty($photoPath);

                                    // Obtener iniciales
                                    $initials = strtoupper(
                                        mb_substr($user->nombres, 0, 1) . mb_substr($user->apellidos, 0, 1)
                                    );
                                @endphp

                                @if($hasPhoto)
                                    <img class="h-9 w-9 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 shadow-sm"
                                        src="{{ route('media.profile_photos', basename($photoPath)) }}"
                                        alt="{{ $user->nombres }} {{ $user->apellidos }}">
                                @else
                                    <div class="h-9 w-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-bold shadow-sm ring-2 ring-white dark:ring-gray-800">
                                        {{ $initials }}
                                    </div>
                                @endif
                            </div>

                            {{-- Icono flecha abajo --}}
                            <svg class="fill-current h-4 w-4 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout', absolute: false) }}">
                            @csrf
                            <x-dropdown-link :href="route('logout', absolute: false)"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger and Mobile Actions -->
            <div class="-me-2 flex items-center sm:hidden gap-1">
                <!-- Notifications & Theme (Mobile) -->
                <x-notifications-dropdown />
                <x-theme-switcher />

                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-300 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (mobile) -->
    <div :class="{'block': open, 'hidden': ! open}"
         x-data="{ activeSection: '{{ request()->routeIs('agenda.*') ? 'agenda' : (request()->routeIs('horarios.*') || request()->routeIs('grupos_horarios.*') ? 'horarios' : (request()->routeIs('historias.*') || request()->routeIs('plantillas.*') || request()->routeIs('campos-evolucion.*') || request()->routeIs('plantillas-globales.*') || request()->routeIs('enfermedades.*') || request()->routeIs('avances_sesion.*') || request()->routeIs('agenda.estado_animos.*') ? 'historias' : (request()->routeIs('publicaciones.*') ? 'publicaciones' : ''))) }}' }"
         class="hidden sm:hidden absolute w-full left-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-md mobile-nav-scroll custom-scrollbar z-50"
         style="max-height: calc(100vh - 4rem); max-height: calc(100dvh - 4rem); overflow-y: auto !important; -webkit-overflow-scrolling: touch;">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Inicio') }}
            </x-responsive-nav-link>

            @if(auth()->user()->role === 'psicologo')
                {{-- AGENDA --}}
                <div>
                    <div class="flex items-center justify-between pe-4">
                        <x-responsive-nav-link :href="route('agenda.index')" :active="request()->routeIs('agenda.*')" class="flex-1">
                            {{ __('Agenda') }}
                        </x-responsive-nav-link>
                        <button @click.prevent="activeSection = (activeSection === 'agenda' ? '' : 'agenda')" class="p-2 text-gray-500">
                            <svg class="h-4 w-4 transition-transform" :class="activeSection === 'agenda' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                    </div>
                    <div x-show="activeSection === 'agenda'" class="bg-gray-50 dark:bg-gray-800/50 py-1 space-y-1">
                        <x-responsive-nav-link :href="route('agenda.index')" :active="request()->routeIs('agenda.index') && !request()->has('view')" class="ps-8 text-sm py-1.5">
                            Vista de Agenda
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('agenda.index', ['view' => 'list'])" :active="request()->routeIs('agenda.index') && request()->query('view') === 'list'" class="ps-8 text-sm py-1.5">
                            Historial de Citas
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('agenda.estadisticas', ['format' => 'html'])" :active="request()->routeIs('agenda.estadisticas')" class="ps-8 text-sm py-1.5">
                            Estadísticas
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('agenda.prioridades.index')" :active="request()->routeIs('agenda.prioridades.*')" class="ps-8 text-sm py-1.5">
                            Prioridades de Atención
                        </x-responsive-nav-link>
                    </div>
                </div>

                {{-- HORARIOS --}}
                <div>
                    <div class="flex items-center justify-between pe-4">
                        <x-responsive-nav-link :href="route('horarios.index')" :active="request()->routeIs('horarios.*') || request()->routeIs('grupos_horarios.*')" class="flex-1">
                            {{ __('Horarios') }}
                        </x-responsive-nav-link>
                        <button @click.prevent="activeSection = (activeSection === 'horarios' ? '' : 'horarios')" class="p-2 text-gray-500">
                            <svg class="h-4 w-4 transition-transform" :class="activeSection === 'horarios' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                    </div>
                    <div x-show="activeSection === 'horarios'" class="bg-gray-50 dark:bg-gray-800/50 py-1 space-y-1">
                        <x-responsive-nav-link :href="route('horarios.index')" :active="request()->routeIs('horarios.index')" class="ps-8 text-sm py-1.5">
                            Bloques de Horario
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('horarios.create')" :active="request()->routeIs('horarios.create')" class="ps-8 text-sm py-1.5">
                            Crear Bloque
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('grupos_horarios.index')" :active="request()->routeIs('grupos_horarios.*')" class="ps-8 text-sm py-1.5">
                            Grupos de Horarios
                        </x-responsive-nav-link>
                    </div>
                </div>



                {{-- HISTORIAS CLINICAS --}}
                <div>
                    <div class="flex items-center justify-between pe-4">
                        <x-responsive-nav-link :href="route('historias.index')" :active="request()->routeIs('historias.*') || request()->routeIs('plantillas.*') || request()->routeIs('campos-evolucion.*') || request()->routeIs('plantillas-globales.*') || request()->routeIs('enfermedades.*') || request()->routeIs('avances_sesion.*') || request()->routeIs('agenda.estado_animos.*')" class="flex-1">
                            {{ __('Historias Clínicas') }}
                        </x-responsive-nav-link>
                        <button @click.prevent="activeSection = (activeSection === 'historias' ? '' : 'historias')" class="p-2 text-gray-500">
                            <svg class="h-4 w-4 transition-transform" :class="activeSection === 'historias' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                    </div>
                    <div x-show="activeSection === 'historias'" class="bg-gray-50 dark:bg-gray-800/50 py-1 space-y-1">
                        <x-responsive-nav-link :href="route('historias.index')" :active="request()->routeIs('historias.index')" class="ps-8 text-sm py-1.5">
                            Ver historias
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('plantillas-globales.index')" :active="request()->routeIs('plantillas-globales.*')" class="ps-8 text-sm py-1.5">
                            Esquema General
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('plantillas.index')" :active="request()->routeIs('plantillas.*')" class="ps-8 text-sm py-1.5">
                            Anexos Clínicos
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('campos-evolucion.index')" :active="request()->routeIs('campos-evolucion.*')" class="ps-8 text-sm py-1.5">
                            Campos de Evolución
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('enfermedades.index')" :active="request()->routeIs('enfermedades.index')" class="ps-8 text-sm py-1.5">
                            Enfermedades
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('avances_sesion.index')" :active="request()->routeIs('avances_sesion.*')" class="ps-8 text-sm py-1.5">
                            Avances de Sesión
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('agenda.estado_animos.index')" :active="request()->routeIs('agenda.estado_animos.*')" class="ps-8 text-sm py-1.5">
                            Estados de Ánimo
                        </x-responsive-nav-link>
                    </div>
                </div>

                {{-- PUBLICACIONES --}}
                <div>
                    <div class="flex items-center justify-between pe-4">
                        <x-responsive-nav-link :href="route('publicaciones.index')" :active="request()->routeIs('publicaciones.*')" class="flex-1">
                            {{ __('Publicaciones') }}
                        </x-responsive-nav-link>
                        <button @click.prevent="activeSection = (activeSection === 'publicaciones' ? '' : 'publicaciones')" class="p-2 text-gray-500">
                            <svg class="h-4 w-4 transition-transform" :class="activeSection === 'publicaciones' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                    </div>
                    <div x-show="activeSection === 'publicaciones'" class="bg-gray-50 dark:bg-gray-800/50 py-1 space-y-1">
                        <x-responsive-nav-link :href="route('publicaciones.index')" :active="request()->routeIs('publicaciones.index')" class="ps-8 text-sm py-1.5">
                            Mis Publicaciones
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('publicaciones.create')" :active="request()->routeIs('publicaciones.create')" class="ps-8 text-sm py-1.5">
                            Nueva Publicación
                        </x-responsive-nav-link>
                    </div>
                </div>

            @elseif(auth()->user()->role === 'admin')
                {{-- USUARIOS --}}
                <div>
                    <div class="flex items-center justify-between pe-4">
                        <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="flex-1">
                            {{ __('Usuarios') }}
                        </x-responsive-nav-link>
                        <button @click.prevent="activeSection = (activeSection === 'admin_users' ? '' : 'admin_users')" class="p-2 text-gray-500">
                            <svg class="h-4 w-4 transition-transform" :class="activeSection === 'admin_users' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                    </div>
                    <div x-show="activeSection === 'admin_users'" class="bg-gray-50 dark:bg-gray-800/50 py-1 space-y-1">
                        <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')" class="ps-8 text-sm py-1.5">
                            Lista de Usuarios
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.users.create')" :active="request()->routeIs('admin.users.create')" class="ps-8 text-sm py-1.5">
                            Crear Usuario
                        </x-responsive-nav-link>
                    </div>
                </div>



            @elseif(auth()->user()->role === 'paciente')
                {{-- MIS CITAS --}}
                <div>
                    <div class="flex items-center justify-between pe-4">
                        <x-responsive-nav-link :href="route('citas.index')" :active="request()->routeIs('citas.*')" class="flex-1">
                            {{ __('Mis Citas') }}
                        </x-responsive-nav-link>
                        <button @click.prevent="activeSection = (activeSection === 'citas' ? '' : 'citas')" class="p-2 text-gray-500">
                            <svg class="h-4 w-4 transition-transform" :class="activeSection === 'citas' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                    </div>
                    <div x-show="activeSection === 'citas'" class="bg-gray-50 dark:bg-gray-800/50 py-1 space-y-1">
                        <x-responsive-nav-link :href="route('citas.index')" :active="request()->routeIs('citas.index')" class="ps-8 text-sm py-1.5">
                            Mis Citas Activas
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('citas.index') . '#historial'" :active="false" class="ps-8 text-sm py-1.5">
                            Historial de Citas
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('citas.create')" :active="request()->routeIs('citas.create')" class="ps-8 text-sm py-1.5">
                            Solicitar Cita
                        </x-responsive-nav-link>
                    </div>
                </div>

                {{-- MURAL DE AVISOS --}}
                <x-responsive-nav-link :href="route('mural.index')" :active="request()->routeIs('mural.*')">
                    {{ __('Mural de Avisos') }}
                </x-responsive-nav-link>
            @endif
            
            {{-- MENSAJERÍA MÓVIL --}}
            @if(auth()->user()->role !== 'admin')
                <div class="px-2 pt-2 pb-1">
                    <button type="button"
                            @click="isChatOpen = true; open = false"
                            class="w-full flex items-center justify-between px-3 py-2 text-base font-medium text-gray-600 dark:text-gray-300 rounded-md hover:text-gray-800 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 ease-in-out relative">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            {{ __('Mensajes') }}
                        </div>
                        @php $unreadMsgs = \App\Models\User::contarMensajesNoLeidos(auth()->id()); @endphp
                        @if($unreadMsgs > 0)
                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1.5 rounded-full bg-red-500 text-white text-xs font-bold">
                                {{ $unreadMsgs > 99 ? '99+' : $unreadMsgs }}
                            </span>
                        @endif
                    </button>
                </div>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-white">{{ Auth::user()->nombres }} {{ Auth::user()->apellidos }}</div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout', absolute: false) }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout', absolute: false)"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
