<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        {{-- TEMA OSCURO - Script crítico para evitar flash (ejecuta ANTES de renderizar) --}}
        <script>
            (function() {
                const getStoredTheme = () => localStorage.getItem('theme');
                const getSystemTheme = () => window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

                let theme = getStoredTheme();
                if (!theme) theme = 'auto';

                if (theme === 'auto') {
                    const systemTheme = getSystemTheme();
                    document.documentElement.classList.add(systemTheme);
                    document.documentElement.setAttribute('data-theme', systemTheme);
                } else if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.setAttribute('data-theme', 'light');
                }

                document.documentElement.setAttribute('data-user-theme', theme);
            })();
        </script>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Psico-Guía') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .invisible-scrollbar::-webkit-scrollbar {
                display: none;
            }
            .invisible-scrollbar {
                -ms-overflow-style: none;  /* IE and Edge */
                scrollbar-width: none;  /* Firefox */
            }
        </style>

        <style>
            /* Transiciones suaves para el cambio de tema */
            * {
                transition-property: background-color, border-color, color, fill, stroke;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                transition-duration: 150ms;
            }

            .no-transition {
                transition-property: none !important;
            }

            .preload * {
                transition: none !important;
            }

            /* ========== ESTILOS OBLIGATORIOS PARA MODO OSCURO ========== */
            /* Forzar fondo oscuro en inputs, selects y textareas */
            .dark input:not([type="radio"]):not([type="checkbox"]):not([type="file"]),
            .dark select,
            .dark textarea {
                background-color: #1f2937 !important; /* gray-800 */
                border-color: #4b5563 !important; /* gray-600 */
                color: #f3f4f6 !important;
            }

            .dark input:focus,
            .dark select:focus,
            .dark textarea:focus {
                border-color: #3b82f6 !important; /* blue-500 */
                outline: none;
                box-shadow: 0 0 0 2px rgba(59,130,246,0.3);
            }

            .dark input::placeholder,
            .dark textarea::placeholder {
                color: #9ca3af !important; /* gray-400 */
            }

            /* Forzar fondos oscuros para clases comunes */
            .dark .bg-white {
                background-color: #1f2937 !important;
            }

            .dark .border-slate-200 {
                border-color: #374151 !important;
            }

            .dark .bg-slate-50 {
                background-color: #111827 !important;
            }

            .dark .bg-slate-100 {
                background-color: #1f2937 !important;
            }

            /* Ajustes para elementos específicos del modal de filtro */
            .dark #patientFilterModal .bg-white {
                background-color: #1f2937 !important;
            }

            .dark #patientFilterModal input,
            .dark #patientFilterModal select {
                background-color: #374151 !important;
                border-color: #4b5563 !important;
                color: #f3f4f6 !important;
            }

            /* Prevent transitions on load (fallback) */
            .preload, .preload * {
                transition: none !important;
            }

            /* ========== PREVENCIÓN DE FOUC Y COLAPSO DE SIDEBAR ========== */
            html.sidebar-collapsed #main-sidebar { width: 4rem !important; }
            html.sidebar-expanded #main-sidebar { width: 14rem !important; }

            html.sidebar-collapsed #main-sidebar .sidebar-toggle-container { justify-content: center !important; }
            html.sidebar-expanded #main-sidebar .sidebar-toggle-container { justify-content: flex-end !important; }

            html.sidebar-collapsed #main-sidebar .sidebar-toggle-arrow { transform: rotate(0deg) !important; }
            html.sidebar-expanded #main-sidebar .sidebar-toggle-arrow { transform: rotate(180deg) !important; }

            html.sidebar-collapsed #main-sidebar a.group, 
            html.sidebar-collapsed #main-sidebar button.group { justify-content: center !important; padding-left: 0 !important; padding-right: 0 !important; }
            html.sidebar-expanded #main-sidebar a.group, 
            html.sidebar-expanded #main-sidebar button.group { justify-content: flex-start !important; padding-left: 0.75rem !important; padding-right: 0.75rem !important; }

            html.sidebar-collapsed #main-sidebar span.whitespace-nowrap { opacity: 0 !important; max-width: 0px !important; }
            html.sidebar-expanded #main-sidebar span.whitespace-nowrap { opacity: 1 !important; max-width: 160px !important; }

            html.sidebar-collapsed #main-sidebar .chat-badge { right: -0.125rem !important; }
            html.sidebar-expanded #main-sidebar .chat-badge { right: 0.5rem !important; }
        </style>
        <script>
            (function() {
                // Sidebar state (Aplica clases de estado inmediatamente)
                let sidebarOpen = localStorage.getItem('sidebarOpen');
                if (sidebarOpen === null) sidebarOpen = 'true';
                if (sidebarOpen === 'true') {
                    document.documentElement.classList.add('sidebar-expanded');
                } else {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            })();
        </script>
    </head>
    <body class="preload font-sans antialiased overflow-hidden bg-gray-100 dark:bg-gray-900">
    <div class="w-full bg-gray-100 dark:bg-gray-900 flex flex-col overflow-hidden" style="height: 100dvh;" @toggle-chat.window="isChatOpen = !isChatOpen" x-data="{ 
        isChatOpen: false, 
        open: false, 
        sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false' 
    }" x-init="$watch('sidebarOpen', val => { 
        localStorage.setItem('sidebarOpen', val);
        if (val) {
            document.documentElement.classList.remove('sidebar-collapsed');
            document.documentElement.classList.add('sidebar-expanded');
        } else {
            document.documentElement.classList.remove('sidebar-expanded');
            document.documentElement.classList.add('sidebar-collapsed');
        }
    })">

        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm z-40 flex-shrink-0 relative">
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

            <div class="flex flex-1 overflow-hidden relative" style="min-height: 0;">
                <aside id="main-sidebar"
                    :class="sidebarOpen ? 'w-56' : 'w-16'"
                    class="hidden lg:flex lg:flex-col w-16 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-sm py-4 flex-shrink-0 transition-all duration-300 ease-in-out overflow-hidden z-20 relative"
                >
                    {{-- Toggle Button --}}
                    <div class="sidebar-toggle-container flex items-center px-2 mb-3" :class="sidebarOpen ? 'justify-end' : 'justify-center'">
                        <button
                            @click="sidebarOpen = !sidebarOpen"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-xl text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 transition-all duration-200 focus:outline-none"
                            :title="sidebarOpen ? 'Contraer menú' : 'Expandir menú'"
                        >
                            <svg
                                class="sidebar-toggle-arrow h-5 w-5 transition-transform duration-300"
                                :class="sidebarOpen ? 'rotate-180' : ''"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            >
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>

                    {{-- Separator --}}
                    <div class="mx-3 mb-3 border-t border-gray-100 dark:border-gray-700"></div>

                    {{-- Navigation Links --}}
                    <nav class="flex flex-col gap-0.5 px-2 flex-1 overflow-y-auto overflow-x-hidden [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                        {{-- Inicio (no sub-items) --}}
                        <a href="{{ route('dashboard') }}"
                           class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-300' }}"
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
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('agenda.*') ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-300' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Agenda"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Agenda</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 dark:border-blue-800 mt-0.5 space-y-0.5">
                                    <a href="{{ route('agenda.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('agenda.index') && !request()->has('view') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span class="whitespace-nowrap">Vista de Agenda</span>
                                    </a>
                                    <a href="{{ route('agenda.index', ['view' => 'list']) }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('agenda.index') && request()->query('view') === 'list' ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 3v5h5M16 13H8M16 17H8M10 9H8"/></svg>
                                        <span class="whitespace-nowrap">Historial de Citas</span>
                                    </a>
                                    <a href="{{ route('agenda.estadisticas', ['format' => 'html']) }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('agenda.estadisticas') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><rect x="7" y="14" width="4" height="4"/><rect x="15" y="8" width="4" height="10"/></svg>
                                        <span class="whitespace-nowrap">Estadísticas</span>
                                    </a>
                                    <a href="{{ route('agenda.prioridades.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('agenda.prioridades.*') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        <span class="whitespace-nowrap">Prioridades de Atención</span>
                                    </a>
                                </div>
                            </div>

                            {{-- ══════ HORARIOS ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('horarios.*') || request()->routeIs('grupos_horarios.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('horarios.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('horarios.*') || request()->routeIs('grupos_horarios.*') ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-300' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Horarios"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Horarios</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 dark:border-blue-800 mt-0.5 space-y-0.5">
                                    <a href="{{ route('horarios.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('horarios.index') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                                        <span class="whitespace-nowrap">Bloques de Horario</span>
                                    </a>
                                    <a href="{{ route('horarios.create') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('horarios.create') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span class="whitespace-nowrap">Crear Bloque</span>
                                    </a>
                                    <a href="{{ route('grupos_horarios.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('grupos_horarios.*') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                        <span class="whitespace-nowrap">Grupos de Horarios</span>
                                    </a>
                                </div>
                            </div>

                            {{-- ══════ HISTORIAS CLÍNICAS ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('historias.*') || request()->routeIs('plantillas.*') || request()->routeIs('campos-evolucion.*') || request()->routeIs('plantillas-globales.*') || request()->routeIs('enfermedades.*') || request()->routeIs('avances_sesion.*') || request()->routeIs('agenda.estado_animos.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('historias.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('historias.*') || request()->routeIs('plantillas.*') || request()->routeIs('campos-evolucion.*') || request()->routeIs('plantillas-globales.*') || request()->routeIs('enfermedades.*') || request()->routeIs('avances_sesion.*') || request()->routeIs('agenda.estado_animos.*') ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-300' }}"
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
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 dark:border-blue-800 mt-0.5 space-y-0.5">
                                    <a href="{{ route('historias.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('historias.index') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <span class="whitespace-nowrap">Ver historias</span>
                                    </a>
                                    <a href="{{ route('plantillas-globales.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('plantillas-globales.*') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        <span class="whitespace-nowrap">Esquema General</span>
                                    </a>
                                    <a href="{{ route('plantillas.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('plantillas.*') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                                        <span class="whitespace-nowrap">Anexos Clínicos</span>
                                    </a>
                                    <a href="{{ route('campos-evolucion.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('campos-evolucion.*') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"/></svg>
                                        <span class="whitespace-nowrap">Campos de Evolución</span>
                                    </a>
                                    <a href="{{ route('enfermedades.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('enfermedades.index') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                        <span class="whitespace-nowrap">Enfermedades</span>
                                    </a>
                                    <a href="{{ route('avances_sesion.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('avances_sesion.*') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                                        <span class="whitespace-nowrap">Avances de Sesión</span>
                                    </a>
                                    <a href="{{ route('agenda.estado_animos.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('agenda.estado_animos.*') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                                        <span class="whitespace-nowrap">Estados de Ánimo</span>
                                    </a>
                                </div>
                            </div>

                            {{-- ══════ PUBLICACIONES ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('publicaciones.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('publicaciones.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('publicaciones.*') ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-300' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Publicaciones"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                        </svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Publicaciones</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 dark:border-blue-800 mt-0.5 space-y-0.5">
                                    <a href="{{ route('publicaciones.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('publicaciones.index') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                                        <span class="whitespace-nowrap">Mis Publicaciones</span>
                                    </a>
                                    <a href="{{ route('publicaciones.create') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('publicaciones.create') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span class="whitespace-nowrap">Nueva Publicación</span>
                                    </a>
                                </div>
                            </div>
                        @elseif(auth()->user()->role === 'admin')
                            {{-- ══════ USUARIOS ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('admin.users.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('admin.users.*') ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-300' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Usuarios"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        </svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Usuarios</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 dark:border-blue-800 mt-0.5 space-y-0.5">
                                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('admin.users.index') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                                        <span class="whitespace-nowrap">Lista de Usuarios</span>
                                    </a>
                                    <a href="{{ route('admin.users.create') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('admin.users.create') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span class="whitespace-nowrap">Crear Usuario</span>
                                    </a>
                                </div>
                            </div>



                        @elseif(auth()->user()->role === 'paciente')
                            {{-- ══════ MIS CITAS ══════ --}}
                            <div x-data="{ subOpen: {{ request()->routeIs('citas.*') ? 'true' : 'false' }} }">
                                <div class="flex items-center">
                                    <a :href="sidebarOpen ? '#' : '{{ route('citas.index') }}'"
                                       @click="if(sidebarOpen) { $event.preventDefault(); subOpen = !subOpen; }"
                                       class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 flex-1 min-w-0 {{ request()->routeIs('citas.*') ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-300' }}"
                                       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                                       title="Mis Citas"
                                    >
                                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300 flex-1"
                                              :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                        >Mis Citas</span>
                                        <svg x-show="sidebarOpen" x-cloak class="h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="subOpen ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </div>
                                <div x-show="subOpen && sidebarOpen" x-collapse x-cloak class="ml-5 pl-3 border-l-2 border-blue-100 dark:border-blue-800 mt-0.5 space-y-0.5">
                                    <a href="{{ route('citas.index') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('citas.index') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                                        <span class="whitespace-nowrap">Mis Citas Activas</span>
                                    </a>
                                    <a href="{{ route('citas.index') }}#historial" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 3v5h5M16 13H8M16 17H8M10 9H8"/></svg>
                                        <span class="whitespace-nowrap">Historial de Citas</span>
                                    </a>
                                    <a href="{{ route('citas.create') }}" class="flex items-center gap-2 h-8 px-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('citas.create') ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-900/30' }}">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span class="whitespace-nowrap">Solicitar Cita</span>
                                    </a>
                                </div>
                            </div>

                            {{-- ══════ MURAL DE AVISOS ══════ --}}
                            <a href="{{ route('mural.index') }}"
                               class="group flex items-center gap-3 h-11 rounded-xl transition-all duration-200 {{ request()->routeIs('mural.*') ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-300' }}"
                               :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
                               title="Mural de Avisos"
                            >
                                <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                </svg>
                                <span class="text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300"
                                      :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                >Mural de Avisos</span>
                            </a>
                        @endif
                    </nav>

                    {{-- Bottom section: Mensajería --}}
                    @if(auth()->user()->role !== 'admin')
                        <div class="mt-auto px-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <button
                                type="button"
                                @if(!request()->routeIs('chat.*'))
                                    @click="$dispatch('toggle-chat')"
                                @endif
                                class="group flex items-center gap-3 h-11 w-full rounded-xl transition-all duration-200 relative"
                                :class="[
                                    (isChatOpen || {{ request()->routeIs('chat.*') ? 'true' : 'false' }}) ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-300',
                                    sidebarOpen ? 'px-3' : 'justify-center px-0'
                                ]"
                                title="Mensajes"
                            >
                                <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span class="sidebar-text text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300"
                                      :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'"
                                >Mensajes</span>
                                @php $unreadMsgs = \App\Models\User::contarMensajesNoLeidos(auth()->id()); @endphp
                                <span class="chat-badge absolute -top-0.5 min-w-[18px] h-[18px] px-0.5 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold border-2 border-white dark:border-gray-800 shadow"
                                      data-count="{{ $unreadMsgs }}"
                                      style="{{ $unreadMsgs > 0 ? '' : 'display: none;' }}"
                                >
                                    {{ $unreadMsgs > 99 ? '99+' : $unreadMsgs }}
                                </span>
                            </button>
                        </div>
                    @endif
                </aside>

                <main class="flex-1 overflow-y-auto invisible-scrollbar p-6 scroll-smooth">
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
                    class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-40 transition-all duration-300"
                    style="display: none;"
                ></div>

                {{-- Ventana de Chat (Lateral) --}}
                <x-chat-window />
            </div>
        </div>

        <div id="globalAppModal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 transition-all duration-200">
            <div class="bg-white dark:bg-gray-800 w-full max-w-sm rounded-[32px] shadow-2xl shadow-slate-200/50 dark:shadow-gray-900/50 flex flex-col overflow-hidden border border-slate-100 dark:border-gray-700 animate-in zoom-in-95 duration-200 p-8 text-center">
                <div id="globalAppModalIconBox" class="w-16 h-16 bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                    <svg id="globalAppModalIconSvg" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 id="globalAppModalTitle" class="text-xl font-black text-slate-800 dark:text-white tracking-tight uppercase mb-2">Mensaje</h3>
                <p id="globalAppModalText" class="text-sm text-slate-500 dark:text-gray-400 font-medium mb-8">¿Estás seguro?</p>
                <div class="flex gap-3">
                    <button id="globalAppModalCancel" class="flex-1 py-4 px-6 bg-slate-100 dark:bg-gray-700 hover:bg-slate-200 dark:hover:bg-gray-600 text-slate-600 dark:text-gray-300 rounded-2xl font-black text-xs uppercase tracking-widest transition-colors">
                        Cancelar
                    </button>
                    <button id="globalAppModalAccept" class="flex-1 py-4 px-6 bg-sky-700 hover:bg-sky-800 shadow-lg shadow-sky-200 dark:shadow-none text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.AppModal = {
                    show: function(title, text, options = {}) {
                        return new Promise((resolve) => {
                            const m = document.getElementById('globalAppModal');
                            const t = document.getElementById('globalAppModalTitle');
                            const p = document.getElementById('globalAppModalText');
                            const y = document.getElementById('globalAppModalAccept');
                            const n = document.getElementById('globalAppModalCancel');
                            const iconBox = document.getElementById('globalAppModalIconBox');
                            const iconSvg = document.getElementById('globalAppModalIconSvg');

                            t.innerText = title || 'Aviso';
                            p.innerText = text || '';

                            const type = options.type || 'confirm';
                            if (type === 'alert') {
                                n.style.display = 'none';
                            } else {
                                n.style.display = 'block';
                            }
                            y.innerText = options.btnText || 'ACEPTAR';

                            // Determine semantic style
                            let styleCategory = 'blue'; // default
                            const lowerTitle = (title || '').toLowerCase();
                            if (lowerTitle.includes('éxito') || lowerTitle.includes('exito') || lowerTitle.includes('completado') || lowerTitle.includes('confirmada')) {
                                styleCategory = 'green';
                            } else if (lowerTitle.includes('error') || lowerTitle.includes('advertencia') || lowerTitle.includes('rechazar') || lowerTitle.includes('rechazo') || lowerTitle.includes('cancelar')) {
                                styleCategory = 'red';
                            } else if (lowerTitle.includes('atención') || lowerTitle.includes('atencion') || lowerTitle.includes('importante') || lowerTitle.includes('aviso')) {
                                styleCategory = 'yellow';
                            }

                            if (options.intent === 'danger') styleCategory = 'red';
                            if (options.intent === 'success') styleCategory = 'green';
                            if (options.intent === 'warning') styleCategory = 'yellow';
                            if (options.intent === 'info') styleCategory = 'blue';

                            switch (styleCategory) {
                                case 'green':
                                    iconBox.className = 'w-16 h-16 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mb-6 mx-auto';
                                    iconSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />';
                                    y.className = 'flex-1 py-4 px-6 bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-200 dark:shadow-none text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all';
                                    break;
                                case 'red':
                                    iconBox.className = 'w-16 h-16 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center mb-6 mx-auto';
                                    iconSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>';
                                    y.className = 'flex-1 py-4 px-6 bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-200 dark:shadow-none text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all';
                                    break;
                                case 'yellow':
                                    iconBox.className = 'w-16 h-16 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center mb-6 mx-auto';
                                    iconSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                                    y.className = 'flex-1 py-4 px-6 bg-amber-500 hover:bg-amber-600 shadow-lg shadow-amber-200 dark:shadow-none text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all';
                                    break;
                                case 'blue':
                                default:
                                    iconBox.className = 'w-16 h-16 bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 rounded-2xl flex items-center justify-center mb-6 mx-auto';
                                    iconSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                                    y.className = 'flex-1 py-4 px-6 bg-sky-700 hover:bg-sky-800 shadow-lg shadow-sky-200 dark:shadow-none text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all';
                                    break;
                            }

                            if(options.iconColor) iconBox.className = `w-16 h-16 ${options.iconColor} rounded-2xl flex items-center justify-center mb-6 mx-auto`;
                            if(options.btnColor) y.className = `flex-1 py-4 px-6 ${options.btnColor} text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all`;
                            if(options.icon) iconSvg.innerHTML = options.icon;

                            m.classList.remove('hidden');
                            m.classList.add('flex');

                            const cleanup = (val) => {
                                m.classList.add('hidden');
                                m.classList.remove('flex');
                                y.onclick = null;
                                n.onclick = null;
                                resolve(val);
                            };

                            y.onclick = () => cleanup(true);
                            n.onclick = () => cleanup(false);
                        });
                    },
                    confirm: function(title, text) { return this.show(title, text, { type: 'confirm' }); },
                    alert: function(title, text) { return this.show(title, text, { type: 'alert' }); }
                };

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

                        var doSubmit = function() {
                            var method = (form.getAttribute('method') || 'POST').toUpperCase();
                            var formData = new FormData(form);
                            var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                            if (method !== 'POST') {
                                formData.set('_method', method);
                            }

                            var submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button:not([type="button"])');
                            var originalBtnHtml = '';
                            if (submitBtn) {
                                originalBtnHtml = submitBtn.innerHTML;
                                submitBtn.disabled = true;
                                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                                submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 inline-block mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...';
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
                                    if (!card && form.dataset.targetCardId) {
                                        card = document.getElementById('cita-card-' + form.dataset.targetCardId);
                                    }
                                    if (card) {
                                        card.remove();
                                        // Empty state dynamic check
                                        if (document.querySelectorAll('[data-ajax-remove-card="true"]').length === 0) {
                                            var emptyState = document.getElementById('emptyCitasState');
                                            var container = document.getElementById('citasCardsContainer');
                                            if (emptyState) emptyState.classList.remove('hidden');
                                            if (container) container.classList.add('hidden');
                                        }
                                    }
                                }

                                if (form.dataset.ajaxSuccessMessage) {
                                    showToast(form.dataset.ajaxSuccessMessage, 'success');
                                }
                                
                                if (form.dataset.ajaxCloseModal) {
                                    var m = document.getElementById(form.dataset.ajaxCloseModal);
                                    if(m) {
                                        m.classList.add('hidden');
                                        m.classList.remove('flex');
                                    }
                                }
                            })
                            .catch(function (error) {
                                console.error('AJAX form error:', error);
                                AppModal.alert('Error', error.message || 'Error al enviar el formulario.');
                            })
                            .finally(function() {
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                                    submitBtn.innerHTML = originalBtnHtml;
                                }
                            });
                        };

                        if (form.dataset.confirm) {
                            AppModal.confirm('Confirmación', form.dataset.confirm).then(function(res) {
                                if (res) doSubmit();
                            });
                        } else {
                            doSubmit();
                        }
                    });
                }

                document.querySelectorAll('form[data-ajax="true"]').forEach(handleAjaxForm);
            });
        </script>
        <script>
            document.addEventListener('alpine:initialized', function() {
                requestAnimationFrame(function() {
                    document.body.classList.remove('preload');
                });
            });
            window.addEventListener('load', function() {
                // Fallback en caso de que alpine:initialized no se dispare a tiempo
                setTimeout(function() {
                    document.body.classList.remove('preload');
                }, 300);
            });
        </script>
    </body>
</html>
