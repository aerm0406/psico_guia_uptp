<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Psico-Guía') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased overflow-hidden">
        <div class="h-screen bg-gray-100 flex flex-col overflow-hidden" x-data="{ isChatOpen: false }">
            
            <header class="bg-white border-b border-gray-200 shadow-sm z-30 flex-shrink-0">
                @include('layouts.navigation')
            </header>

            {{-- Toast/sistema de mensajes breve --}}
            @if (session('success') || session('error') || $errors->any())
                <div id="toast" class="fixed top-6 right-6 z-50">
                    @if (session('success'))
                        <div class="max-w-sm w-full bg-green-600 text-white shadow-lg rounded-2xl border border-green-700 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">¡Listo!</p>
                                    <p class="text-sm mt-1">{!! session('success') !!}</p>
                                </div>
                                <button onclick="document.getElementById('toast')?.remove()" class="text-white opacity-70 hover:opacity-100">✕</button>
                            </div>
                        </div>
                    @elseif (session('error'))
                        <div class="max-w-sm w-full bg-red-600 text-white shadow-lg rounded-2xl border border-red-700 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">Error</p>
                                    <p class="text-sm mt-1">{!! session('error') !!}</p>
                                </div>
                                <button onclick="document.getElementById('toast')?.remove()" class="text-white opacity-70 hover:opacity-100">✕</button>
                            </div>
                        </div>
                    @elseif ($errors->any())
                        <div class="max-w-sm w-full bg-red-600 text-white shadow-lg rounded-2xl border border-red-700 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">Revisa los datos</p>
                                    <p class="text-sm mt-1">{{ $errors->first() }}</p>
                                </div>
                                <button onclick="document.getElementById('toast')?.remove()" class="text-white opacity-70 hover:opacity-100">✕</button>
                            </div>
                        </div>
                    @endif
                </div>

                <script>
                    setTimeout(() => {
                        document.getElementById('toast')?.remove();
                    }, 5000);
                </script>
            @endif

            <div class="flex flex-1 overflow-hidden relative">
                <aside 
                    x-data="{ sidebarOpen: false }"
                    :class="sidebarOpen ? 'w-56' : 'w-16'"
                    class="hidden lg:flex lg:flex-col w-16 bg-white border-r border-gray-200 shadow-sm py-4 flex-shrink-0 transition-all duration-300 ease-in-out overflow-hidden z-20 relative"
                >
                    {{-- Toggle Button --}}
                    <div class="flex items-center px-2 mb-3" :class="sidebarOpen ? 'justify-end' : 'justify-center'">
                        <button 
                            @click="sidebarOpen = !sidebarOpen" 
                            class="inline-flex items-center justify-center h-9 w-9 rounded-xl text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 focus:outline-none"
                            :title="sidebarOpen ? 'Contraer menú' : 'Expandir menú'"
                        >
                            <svg 
                                class="h-5 w-5 transition-transform duration-300" 
                                :class="sidebarOpen ? 'rotate-180' : ''"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            >
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>

                    {{-- Separator --}}
                    <div class="mx-3 mb-3 border-t border-gray-100"></div>

                    {{-- Navigation Links --}}
                    <nav class="flex flex-col gap-0.5 px-2 flex-1 overflow-y-auto overflow-x-hidden">
                        {{-- Inicio (no sub-items) --}}
                        <a href="{{ route('dashboard') }}" 
                           class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}"
                           :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                           title="Inicio"
                        >
                            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-5.5a1.5 1.5 0 0 0-3 0V21H4a1 1 0 0 1-1-1V9.5z"/></svg>
                            <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300"
                                  :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                            >Inicio</span>
                        </a>

                        @if(auth()->user()->role === 'psicologo')
                            {{-- ══════ AGENDA ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('agenda.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('agenda.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('agenda.*') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Agenda"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Agenda</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 mt-0.5 space-y-0.5">
                                    <a href="{{ route('agenda.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('agenda.index') && !request()->has('view') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span class="whitespace-nowrap">Vista de Agenda</span>
                                    </a>
                                    <a href="{{ route('agenda.pending.list') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('agenda.pending.list') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <span class="whitespace-nowrap">Solicitudes Pendientes</span>
                                    </a>
                                </div>
                            </div>

                            {{-- ══════ HORARIOS ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('horarios.*') || request()->routeIs('grupos_horarios.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('horarios.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('horarios.*') || request()->routeIs('grupos_horarios.*') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Horarios"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Horarios</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 mt-0.5 space-y-0.5">
                                    <a href="{{ route('horarios.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('horarios.index') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                                        <span class="whitespace-nowrap">Bloques de Horario</span>
                                    </a>
                                    <a href="{{ route('horarios.create') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('horarios.create') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span class="whitespace-nowrap">Crear Bloque</span>
                                    </a>
                                    <a href="{{ route('grupos_horarios.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('grupos_horarios.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                        <span class="whitespace-nowrap">Grupos de Horarios</span>
                                    </a>
                                </div>
                            </div>

                            {{-- ══════ HISTORIAS CLÍNICAS ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('historias.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('historias.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('historias.*') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Historias Clínicas"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 2H15a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1V4a2 2 0 0 1 2-2z" />
                                            <path d="M9 7h6" /><path d="M9 11h6" /><path d="M9 15h6" />
                                        </svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Historias Clínicas</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 mt-0.5 space-y-0.5">
                                    <a href="{{ route('historias.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('historias.index') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <span class="whitespace-nowrap">Buscar Paciente</span>
                                    </a>
                                    <a href="{{ route('plantillas.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('plantillas.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                                        <span class="whitespace-nowrap">Secciones del Historial</span>
                                    </a>
                                </div>
                            </div>

                            {{-- ══════ ENFERMEDADES ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('enfermedades.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('enfermedades.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('enfermedades.*') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Enfermedades"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Enfermedades</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 mt-0.5 space-y-0.5">
                                    <a href="{{ route('enfermedades.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('enfermedades.index') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                                        <span class="whitespace-nowrap">Ver Catálogo</span>
                                    </a>
                                    <a href="{{ route('enfermedades.create') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('enfermedades.create') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span class="whitespace-nowrap">Registrar Enfermedad</span>
                                    </a>
                                </div>
                            </div>

                        @elseif(auth()->user()->role === 'admin')
                            {{-- ══════ USUARIOS ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('admin.users.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('admin.users.*') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Usuarios"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        </svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Usuarios</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 mt-0.5 space-y-0.5">
                                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('admin.users.index') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                                        <span class="whitespace-nowrap">Lista de Usuarios</span>
                                    </a>
                                    <a href="{{ route('admin.users.create') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('admin.users.create') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span class="whitespace-nowrap">Crear Usuario</span>
                                    </a>
                                </div>
                            </div>

                            {{-- ══════ CITAS GLOBALES ══════ --}}
                            <a href="{{ route('citas.index') }}" 
                               class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 {{ request()->routeIs('citas.*') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}"
                               :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                               title="Citas Globales"
                            >
                                <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300"
                                      :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                >Citas Globales</span>
                            </a>

                        @elseif(auth()->user()->role === 'paciente')
                            {{-- ══════ MIS CITAS ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('citas.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('citas.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('citas.*') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Mis Citas"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Mis Citas</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 mt-0.5 space-y-0.5">
                                    <a href="{{ route('citas.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('citas.index') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                                        <span class="whitespace-nowrap">Mis Citas Activas</span>
                                    </a>
                                    <a href="{{ route('citas.create') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('citas.create') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50/60' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span class="whitespace-nowrap">Solicitar Cita</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </nav>

                    {{-- Bottom section: Mensajería --}}
                    @if(auth()->user()->role !== 'admin')
                        <div class="mt-auto px-2 pt-3 border-t border-gray-100 mx-2">
                            <button 
                                type="button"
                                @click="isChatOpen = !isChatOpen" 
                                class="group flex items-center gap-3 h-11 w-full rounded-xl transition-all duration-200 relative"
                                :class="[
                                    isChatOpen ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600',
                                    sidebarOpen ? 'px-3' : 'justify-center px-0'
                                ]"
                                title="Mensajes"
                            >
                                <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300"
                                      :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                >Mensajes</span>
                                @php $unreadMsgs = \App\Models\User::contarMensajesNoLeidos(auth()->id()); @endphp
                                @if($unreadMsgs > 0)
                                    <span class="absolute -top-0.5 min-w-[18px] h-[18px] px-0.5 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold border-2 border-white shadow"
                                          :class="sidebarOpen ? 'right-2' : '-right-0.5'"
                                    >
                                        {{ $unreadMsgs > 99 ? '99+' : $unreadMsgs }}
                                    </span>
                                @endif
                            </button>
                        </div>
                    @endif
                </aside>


                <main class="flex-1 overflow-y-auto p-6 scroll-smooth">
                    @isset($header)
                        <div class="max-w-7xl mx-auto mb-6">
                            {{ $header }}
                        </div>
                    @endisset

                    {{ $slot }}
                </main>

                {{-- Overlay de fondo para enfoque --}}
                <div 
                    x-show="isChatOpen" 
                    @click="isChatOpen = false"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-40 lg:left-16"
                    style="display: none;"
                ></div>

                {{-- Ventana de Chat (Lateral) --}}
                <x-chat-window />
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.showToast = function(message, type) {
                    var toast = document.createElement('div');
                    toast.className = 'max-w-sm w-full shadow-lg rounded-2xl border px-4 py-3 fixed top-6 right-6 z-50 transition duration-200 ' +
                        (type === 'error' ? 'bg-red-600 text-white border-red-700' : 'bg-green-600 text-white border-green-700');
                    toast.innerHTML = '<div class="flex items-start justify-between gap-3"><div><p class="font-semibold">' +
                        (type === 'error' ? 'Error' : '¡Listo!') + '</p><p class="text-sm mt-1">' +
                        message + '</p></div><button type="button" class="text-white opacity-70 hover:opacity-100">✕</button></div>';
                    document.body.appendChild(toast);
                    toast.querySelector('button')?.addEventListener('click', function () { toast.remove(); });
                    setTimeout(function () { toast.remove(); }, 5000);
                };

                function handleAjaxForm(form) {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        var action = form.getAttribute('action');
                        if (!action) {
                            return;
                        }
                        var method = (form.getAttribute('method') || 'POST').toUpperCase();
                        var formData = new FormData(form);
                        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        if (method !== 'POST') {
                            formData.set('_method', method);
                        }

                        fetch(action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                            .then(function (response) {
                                return response.json().then(function (data) {
                                    if (!response.ok) {
                                        var error = data.message || 'Ocurrió un error al procesar la acción.';
                                        throw new Error(error);
                                    }
                                    return data;
                                });
                            })
                            .then(function (data) {
                                if (data.success === false) {
                                    throw new Error(data.message || 'Ocurrió un error en la acción.');
                                }

                                if (form.dataset.ajaxRemove === 'true') {
                                    var card = form.closest('[data-ajax-remove-card="true"]');
                                    if (card) {
                                        card.remove();
                                    }
                                }

                                if (form.dataset.ajaxSuccessMessage) {
                                    showToast(form.dataset.ajaxSuccessMessage, 'success');
                                }
                            })
                            .catch(function (error) {
                                console.error('AJAX form error:', error);
                                showToast(error.message || 'Error al enviar el formulario.', 'error');
                            });
                    });
                }

                document.querySelectorAll('form[data-ajax="true"]').forEach(handleAjaxForm);
            });
        </script>
    </body>
</html>