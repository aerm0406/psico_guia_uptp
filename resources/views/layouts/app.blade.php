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
                <aside class="hidden lg:flex lg:flex-col w-16 bg-white border-r border-gray-200 shadow-sm py-4 flex-shrink-0">
                    <div class="flex flex-col items-center gap-3 px-1 text-center">
                        <a href="{{ route('dashboard') }}" title="Inicio" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-5.5a1.5 1.5 0 0 0-3 0V21H4a1 1 0 0 1-1-1V9.5z"/></svg>
                        </a>
                        @if(auth()->user()->role === 'psicologo')
                            <a href="{{ route('agenda.index') }}" title="Agenda" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('agenda.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </a>
                            <a href="{{ route('horarios.index') }}" title="Horarios" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('horarios.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8"/><polyline points="12 6 12 12 16 14"/></svg>
                            </a>
                            {{-- <a href="{{ route('pacientes.index') }}" title="Pacientes" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('pacientes.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z" />
                                    <path d="M4 20c0-3.31 2.69-6 6-6h4c3.31 0 6 2.69 6 6v1H4v-1z" />
                                </svg>
                            </a> --}}
                            <a href="{{ route('historias.index') }}" title="Historias Clínicas" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('historias.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 2H15a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1V4a2 2 0 0 1 2-2z" />
                                    <path d="M9 7h6" />
                                    <path d="M9 11h6" />
                                    <path d="M9 15h6" />
                                </svg>
                            </a>
                            <a href="{{ route('enfermedades.index') }}" title="Enfermedades" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('enfermedades.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('plantillas.index') }}" title="Plantillas de Secciones" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('plantillas.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="3" y1="9" x2="21" y2="9"></line>
                                    <line x1="9" y1="21" x2="9" y2="9"></line>
                                </svg>
                            </a>
                        @elseif(auth()->user()->role === 'admin')
                            {{-- Gestión de Usuarios (Nuevo) --}}
                            <a href="{{ route('admin.users.index') }}" title="Gestionar Usuarios" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('admin.users.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </a>
                            {{-- Citas Globales --}}
                            <a href="{{ route('citas.index') }}" title="Todas las Citas" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('citas.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </a>
                            {{-- Pacientes Globales --}}
                            {{-- <a href="{{ route('pacientes.index') }}" title="Todos los Pacientes" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('pacientes.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z" />
                                    <path d="M4 20c0-3.31 2.69-6 6-6h4c3.31 0 6 2.69 6 6v1H4v-1z" />
                                </svg>
                            </a> --}}
                        @elseif(auth()->user()->role === 'paciente')
                            <a href="{{ route('citas.index') }}" title="Mis citas" class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition hover:bg-blue-50 {{ request()->routeIs('citas.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-500' }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10h-6V4H9v6H3"/><path d="M3 18h18"/><path d="M18 14V6"/></svg>
                            </a>
                        @endif

                        {{-- Botón de Mensajería Global --}}
                        @if(auth()->user()->role !== 'admin')
                            <button 
                                type="button"
                                @click="isChatOpen = !isChatOpen" 
                                title="Mensajes" 
                                class="inline-flex items-center justify-center h-12 w-12 rounded-2xl transition relative"
                                :class="isChatOpen ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-blue-50'"
                            >
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                @php $unreadMsgs = \App\Models\User::contarMensajesNoLeidos(auth()->id()); @endphp
                                @if($unreadMsgs > 0)
                                    <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-0.5 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold border-2 border-white shadow">
                                        {{ $unreadMsgs > 99 ? '99+' : $unreadMsgs }}
                                    </span>
                                @endif
                            </button>
                        @endif
                    </div>
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