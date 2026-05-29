<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="w-full px-4 sm:px-6 lg:px-4">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('img/LOGO-DE-PSICOLOGIA-GRISOSCURO.png') }}" alt="Logo Psico-Guía UPTP" class="h-8 w-auto object-contain" />
                        <span class="font-bold text-base text-gray-900">Psico-Guía UPTP</span>
                    </a>
                </div>

                <!-- Navigation Links remain in left sidebar on desktop -->
            </div>

            <!-- Notifications & Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                
                <!-- Notifications Dropdown -->
                @php
                    $unreadCount = \Illuminate\Support\Facades\DB::table('notifications')
                        ->where('notifiable_type', 'App\Models\User')
                        ->where('notifiable_id', auth()->id())
                        ->whereNull('read_at')
                        ->count();

                    $allNotifications = \Illuminate\Support\Facades\DB::table('notifications')
                        ->where('notifiable_type', 'App\Models\User')
                        ->where('notifiable_id', auth()->id())
                        ->orderBy('created_at', 'desc')
                        ->take(20)
                        ->get()
                        ->map(function ($notif) {
                            $notif->data = json_decode($notif->data, true);
                            return $notif;
                        });
                @endphp
                <div class="relative" x-data="{
                    openNotif: false,
                    optionsOpen: false,
                    filter: 'all'
                }" @click.away="openNotif = false; optionsOpen = false">
                    <!-- Bell Button -->
                    <button @click.stop="openNotif = !openNotif"
                            class="relative p-2 text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-full transition-all duration-200 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        @if($unreadCount > 0)
                            <span id="main-notif-badge" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-0.5 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold border-2 border-white shadow">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="openNotif"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute right-0 mt-3 w-[380px] bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden"
                         style="display: none;">

                        <!-- Header -->
                        <div class="px-4 pt-4 pb-2 flex items-center justify-between">
                            <h3 class="text-[17px] font-black text-gray-900 tracking-tight">Notificaciones</h3>
                            <!-- Three-dot Options button -->
                            <div class="relative">
                                <button @click.stop="optionsOpen = !optionsOpen"
                                        class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full transition">
                                     <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                         <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                     </svg>
                                </button>
                                <!-- Options Menu -->
                                <div x-show="optionsOpen"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     class="absolute right-0 mt-1 w-56 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden"
                                     style="display: none;">
                                    <button type="button"
                                            @click.stop="
                                                fetch('{{ route('notifications.readAll') }}', {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                                        'Accept': 'application/json',
                                                        'Content-Type': 'application/json'
                                                    }
                                                }).then(() => {
                                                    document.querySelectorAll('[data-unread=\'true\']').forEach(el => {
                                                        el.dataset.unread = 'false';
                                                        el.classList.remove('bg-blue-50/40');
                                                        const dot = el.querySelector('.notif-dot');
                                                        if (dot) dot.remove();
                                                        const time = el.querySelector('.notif-time');
                                                        if (time) { time.classList.remove('text-blue-600', 'font-bold'); time.classList.add('text-gray-400'); }
                                                        const body = el.querySelector('.notif-body');
                                                        if (body) body.classList.remove('font-semibold');
                                                    });
                                                    document.querySelectorAll('.notif-badge').forEach(b => b.remove());
                                                    const mb = document.getElementById('main-notif-badge');
                                                    if(mb) mb.remove();
                                                    optionsOpen = false;
                                                })
                                            "
                                            class="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition text-left">
                                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Marcar todo como leído
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="px-4 pb-2 flex gap-2">
                            <button @click.stop="filter = 'all'"
                                    :class="filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                    class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap">
                                Todas
                            </button>
                            <button @click.stop="filter = 'unread'"
                                    :class="filter === 'unread' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                    class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap">
                                No leídas
                                @if($unreadCount > 0)
                                    <span class="ml-1 bg-red-500 text-white rounded-full px-1.5 py-0.5 text-[9px] notif-badge">{{ $unreadCount }}</span>
                                @endif
                            </button>
                        </div>

                        <!-- Lista -->
                        <div x-ref="notifList" class="max-h-[400px] overflow-y-auto divide-y divide-gray-50">
                            @forelse($allNotifications as $notification)
                                <a href="{{ route('notifications.read', $notification->id) }}"
                                   data-notif="{{ $notification->id }}"
                                   data-unread="{{ is_null($notification->read_at) ? 'true' : 'false' }}"
                                   x-show="filter === 'all' || (filter === 'unread' && {{ is_null($notification->read_at) ? 'true' : 'false' }})"
                                   class="block px-4 py-3 hover:bg-gray-50 transition group relative {{ is_null($notification->read_at) ? 'bg-blue-50/40' : '' }}">
                                    <div class="flex items-start gap-3">
                                        <!-- Icono -->
                                        <div class="relative flex-shrink-0">
                                            @if(($notification->data['type_id'] ?? '') === 'new_message')
                                                {{-- Icono: Sobre / Carta --}}
                                                <div class="w-11 h-11 bg-indigo-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @elseif(($notification->data['type_id'] ?? '') === 'cita_confirmed')
                                                {{-- Icono: Calendario con check --}}
                                                <div class="w-11 h-11 bg-emerald-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                                                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="2" stroke-linecap="round"/>
                                                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="2" stroke-linecap="round"/>
                                                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 16l2 2 4-4"/>
                                                    </svg>
                                                </div>
                                            @elseif(($notification->data['type_id'] ?? '') === 'cita_cancelled')
                                                {{-- Icono: Calendario con X --}}
                                                <div class="w-11 h-11 bg-red-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                                                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="2" stroke-linecap="round"/>
                                                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="2" stroke-linecap="round"/>
                                                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l4 4M14 14l-4 4"/>
                                                    </svg>
                                                </div>
                                            @elseif(($notification->data['type_id'] ?? '') === 'cita_requested')
                                                {{-- Icono: Calendario con reloj --}}
                                                <div class="w-11 h-11 bg-amber-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                                                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="2" stroke-linecap="round"/>
                                                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="2" stroke-linecap="round"/>
                                                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/>
                                                        <circle cx="12" cy="16" r="3" stroke-width="1.5"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14.5v1.5l1 1"/>
                                                    </svg>
                                                </div>
                                            @else
                                                {{-- Icono: Campana genérica --}}
                                                <div class="w-11 h-11 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <!-- Texto -->
                                        <div class="flex-1 min-w-0">
                                            <p class="notif-body text-[13px] text-gray-800 leading-snug {{ is_null($notification->read_at) ? 'font-semibold' : '' }}">
                                                {{ $notification->data['body'] ?? '' }}
                                            </p>
                                            <p class="notif-time text-[11px] mt-0.5 {{ is_null($notification->read_at) ? 'text-blue-600 font-bold' : 'text-gray-400' }}">
                                                {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                            </p>
                                        </div>
                                        <!-- Punto unread -->
                                        @if(is_null($notification->read_at))
                                            <div class="notif-dot w-2.5 h-2.5 bg-red-500 rounded-full flex-shrink-0 self-center mt-0.5"></div>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="py-10 flex flex-col items-center gap-2 text-gray-400">
                                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    <p class="text-sm font-medium">Sin notificaciones</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <span class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 text-gray-600">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z" />
                                    <path fill-rule="evenodd" d="M4 20c0-3.31 2.69-6 6-6h4c3.31 0 6 2.69 6 6v1H4v-1z" />
                                </svg>
                            </span>
                            <span class="ps-2 border-l border-gray-200"></span>
                            <svg class="fill-current h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
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

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Inicio') }}
            </x-responsive-nav-link>

            @if(auth()->user()->role === 'psicologo')
                <x-responsive-nav-link :href="route('agenda.index')" :active="request()->routeIs('agenda.*')">
                    {{ __('Agenda') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('horarios.index')" :active="request()->routeIs('horarios.*')">
                    {{ __('Horarios') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('pacientes.index')" :active="request()->routeIs('pacientes.*')">
                    {{ __('Pacientes') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('historias.index')" :active="request()->routeIs('historias.*')">
                    {{ __('Historias Clínicas') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('enfermedades.index')" :active="request()->routeIs('enfermedades.*')">
                    {{ __('Enfermedades') }}
                </x-responsive-nav-link>
            @elseif(auth()->user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Usuarios') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('citas.index')" :active="request()->routeIs('citas.*')">
                    {{ __('Citas Globales') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('pacientes.index')" :active="request()->routeIs('pacientes.*')">
                    {{ __('Pacientes') }}
                </x-responsive-nav-link>
            @elseif(auth()->user()->role === 'paciente')
                <x-responsive-nav-link :href="route('citas.index')" :active="request()->routeIs('citas.*')">
                    {{ __('Mis Citas') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->nombres }} {{ Auth::user()->apellidos }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout', absolute: false) }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout', absolute: false)"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
