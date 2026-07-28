<x-app-layout>
    <div class="pt-12 pb-4 bg-slate-50 dark:bg-gray-900 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-6 mb-6 md:mb-10">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Gestión de Usuarios</h1>
                    <p class="mt-1 md:mt-2 text-slate-500 dark:text-gray-400 text-xs md:text-sm">Administra todos los psicólogos, pacientes y administradores del sistema.</p>
                </div>
                <div class="flex items-center gap-2 md:gap-3 flex-1 justify-end">
                    <div class="flex items-center bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-2xl shadow-sm pr-2 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition-all h-10 md:h-11 flex-1">
                        <div class="relative flex-1 h-full search-container">
                            <input id="user-search" type="text" value="{{ $buscar }}" placeholder="Buscar usuario..."
                                class="w-full pl-10 pr-4 py-0 h-full bg-transparent border-none text-sm focus:ring-0 outline-none text-gray-900 dark:text-white">
                            <div class="absolute left-3 top-2.5 md:top-3">
                                <svg id="search-icon" class="w-4 h-4 text-slate-400 dark:text-gray-500 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <div id="search-spinner" class="hidden animate-spin rounded-full h-4 w-4 border-2 border-indigo-500 border-t-transparent absolute top-0 left-0"></div>
                            </div>
                        </div>
                        <div class="h-4 w-px bg-slate-200 dark:bg-gray-700 mx-1"></div>
                        <select id="user-role-filter" class="bg-transparent border-none text-[10px] md:text-xs font-bold text-slate-500 dark:text-gray-400 py-0 h-full pl-2 pr-8 focus:ring-0 outline-none cursor-pointer">
                            <option value="">TODOS</option>
                            <option value="admin" {{ $role == 'admin' ? 'selected' : '' }}>ADMINS</option>
                            <option value="psicologo" {{ $role == 'psicologo' ? 'selected' : '' }}>PSICÓLOGOS</option>
                            <option value="paciente" {{ $role == 'paciente' ? 'selected' : '' }}>PACIENTES</option>
                        </select>
                    </div>

                    <a href="{{ route('admin.users.create') }}"
                        class="flex shrink-0 items-center justify-center w-10 h-10 md:w-11 md:h-11 bg-purple-700 text-white rounded-2xl hover:bg-purple-800 transition-all shadow-lg shadow-purple-100 dark:shadow-purple-900/30 active:scale-95"
                        title="Nuevo Usuario">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Quick Stats Summary -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6 md:mb-10">
                <!-- Total Card -->
                <div onclick="updateRoleFilter('')"
                     class="stat-card cursor-pointer bg-white dark:bg-gray-800 p-3 md:p-5 rounded-[1.5rem] md:rounded-3xl border transition-all duration-300 hover:shadow-xl hover:-translate-y-1 active:scale-95 flex items-center gap-3 md:gap-4 {{ $role == '' ? 'border-indigo-500 ring-4 ring-indigo-500/5 dark:ring-indigo-500/20 shadow-lg' : 'border-slate-100 dark:border-gray-700 shadow-sm hover:border-indigo-200 dark:hover:border-indigo-800' }}"
                     data-role="">
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-slate-50 dark:bg-gray-700 text-slate-600 dark:text-gray-400 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] md:text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-tighter truncate">Total</p>
                        <h3 class="text-lg md:text-xl font-black text-slate-900 dark:text-white leading-none">{{ $countTotal }}</h3>
                    </div>
                </div>

                <!-- Pacientes Card -->
                <div onclick="updateRoleFilter('paciente')"
                     class="stat-card cursor-pointer bg-white dark:bg-gray-800 p-3 md:p-5 rounded-[1.5rem] md:rounded-3xl border transition-all duration-300 hover:shadow-xl hover:-translate-y-1 active:scale-95 flex items-center gap-3 md:gap-4 {{ $role == 'paciente' ? 'border-emerald-500 ring-4 ring-emerald-500/5 dark:ring-emerald-500/20 shadow-lg' : 'border-slate-100 dark:border-gray-700 shadow-sm hover:border-emerald-200 dark:hover:border-emerald-800' }}"
                     data-role="paciente">
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] md:text-[10px] font-black text-emerald-400 dark:text-emerald-500 uppercase tracking-tighter truncate">Pacientes</p>
                        <h3 class="text-lg md:text-xl font-black text-slate-900 dark:text-white leading-none">{{ $countPacientes }}</h3>
                    </div>
                </div>

                <!-- Psicólogos Card -->
                <div onclick="updateRoleFilter('psicologo')"
                     class="stat-card cursor-pointer bg-white dark:bg-gray-800 p-3 md:p-5 rounded-[1.5rem] md:rounded-3xl border transition-all duration-300 hover:shadow-xl hover:-translate-y-1 active:scale-95 flex items-center gap-3 md:gap-4 {{ $role == 'psicologo' ? 'border-blue-500 ring-4 ring-blue-500/5 dark:ring-blue-500/20 shadow-lg' : 'border-slate-100 dark:border-gray-700 shadow-sm hover:border-blue-200 dark:hover:border-blue-800' }}"
                     data-role="psicologo">
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] md:text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-tighter truncate">Psicólogos</p>
                        <h3 class="text-lg md:text-xl font-black text-slate-900 dark:text-white leading-none">{{ $countPsicologos }}</h3>
                    </div>
                </div>

                <!-- Admins Card -->
                <div onclick="updateRoleFilter('admin')"
                     class="stat-card cursor-pointer bg-white dark:bg-gray-800 p-3 md:p-5 rounded-[1.5rem] md:rounded-3xl border transition-all duration-300 hover:shadow-xl hover:-translate-y-1 active:scale-95 flex items-center gap-3 md:gap-4 {{ $role == 'admin' ? 'border-purple-500 ring-4 ring-purple-500/5 dark:ring-purple-500/20 shadow-lg' : 'border-slate-100 dark:border-gray-700 shadow-sm hover:border-purple-200 dark:hover:border-purple-800' }}"
                     data-role="admin">
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] md:text-[10px] font-black text-purple-400 dark:text-purple-500 uppercase tracking-tighter truncate">Admins</p>
                        <h3 class="text-lg md:text-xl font-black text-slate-900 dark:text-white leading-none">{{ $countAdmins }}</h3>
                    </div>
                </div>
            </div>

            <!-- Users Content -->
            <div id="users-content">
                @include('admin.users.components.user_list')
            </div>
        </div>
    </div>

    {{-- Modal para Ver Detalles --}}
    <div x-data="{ show: false, loading: true, html: '' }"
         x-on:open-user-modal.window="show = true; loading = true; fetch(`/admin/users/${$event.detail.id}`).then(res => res.text()).then(data => { html = data; loading = false; })"
         x-on:keydown.escape.window="show = false"
         x-show="show"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm transition-all"
         style="display: none;"
         x-transition:enter="ease-out duration-100"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-75"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div x-show="show"
             x-on:click.away="show = false"
             class="w-full max-w-3xl bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl border-t-8 border-indigo-600 overflow-hidden transform transition-all"
             x-transition:enter="ease-out duration-100"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-75"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            <div x-show="loading" class="p-20 text-center">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-indigo-500 border-t-transparent mb-4"></div>
                <p class="text-slate-500 dark:text-gray-400 font-bold text-xs uppercase tracking-[0.2em]">Sincronizando información...</p>
            </div>

            <div x-show="!loading" x-html="html"></div>
        </div>
    </div>

    <script>
        const initUsersFilter = () => {
            const searchInput = document.getElementById('user-search');
            const roleFilter = document.getElementById('user-role-filter');
            const searchIcon = document.getElementById('search-icon');
            const searchSpinner = document.getElementById('search-spinner');
            let searchTimeout = null;

            window.updateRoleFilter = (role) => {
                if (roleFilter) {
                    roleFilter.value = role;
                    roleFilter.dispatchEvent(new Event('change'));
                }
            };

            const updateCardVisuals = (activeRole) => {
                document.querySelectorAll('.stat-card').forEach(card => {
                    const role = card.getAttribute('data-role');
                    const isTotal = role === '';
                    const isPaciente = role === 'paciente';
                    const isPsicologo = role === 'psicologo';
                    const isAdmin = role === 'admin';

                    card.classList.remove('border-indigo-500', 'border-emerald-500', 'border-blue-500', 'border-purple-500', 'ring-4', 'shadow-lg');
                    card.classList.add('border-slate-100', 'dark:border-gray-700', 'shadow-sm');

                    if (role === activeRole) {
                        card.classList.remove('border-slate-100', 'dark:border-gray-700', 'shadow-sm');
                        card.classList.add('ring-4', 'shadow-lg');

                        if (isTotal) card.classList.add('border-indigo-500', 'ring-indigo-500/5', 'dark:ring-indigo-500/20');
                        if (isPaciente) card.classList.add('border-emerald-500', 'ring-emerald-500/5', 'dark:ring-emerald-500/20');
                        if (isPsicologo) card.classList.add('border-blue-500', 'ring-blue-500/5', 'dark:ring-blue-500/20');
                        if (isAdmin) card.classList.add('border-purple-500', 'ring-purple-500/5', 'dark:ring-purple-500/20');
                    }
                });
            };

            const performHybridFilter = () => {
                if (!searchInput) return;
                const query = searchInput.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.user-row');
                const noResultsRow = document.getElementById('no-results-client');
                const pagination = document.getElementById('users-pagination');
                let visibleCount = 0;

                if (pagination) {
                    if (query.length > 0) {
                        pagination.classList.add('hidden');
                    } else {
                        pagination.classList.remove('hidden');
                    }
                }

                rows.forEach(row => {
                    const searchText = row.getAttribute('data-search') || '';
                    if (searchText.includes(query)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (noResultsRow) {
                    if (visibleCount === 0 && rows.length > 0) {
                        noResultsRow.classList.remove('hidden');
                    } else {
                        noResultsRow.classList.add('hidden');
                    }
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const roleRaw = roleFilter ? roleFilter.value : '';
                    const role = (roleRaw || '').toString().toLowerCase().trim();

                    if (searchIcon) searchIcon.classList.add('opacity-0');
                    if (searchSpinner) searchSpinner.classList.remove('hidden');

                    fetch(`/admin/users?buscar=${encodeURIComponent(query)}&role=${encodeURIComponent(role)}`, {
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        const contentArea = document.getElementById('users-content');
                        if (contentArea) contentArea.innerHTML = html;

                        if (searchIcon) searchIcon.classList.remove('opacity-0');
                        if (searchSpinner) searchSpinner.classList.add('hidden');
                    })
                    .catch(() => {
                        if (searchIcon) searchIcon.classList.remove('opacity-0');
                        if (searchSpinner) searchSpinner.classList.add('hidden');
                    });
                }, 400);
            };

            searchInput?.addEventListener('input', performHybridFilter);
            roleFilter?.addEventListener('change', () => {
                const query = searchInput ? searchInput.value : '';
                const roleRaw = roleFilter ? roleFilter.value : '';
                const role = (roleRaw || '').toString().toLowerCase().trim();

                updateCardVisuals(role);

                if (searchIcon) searchIcon.classList.add('opacity-0');
                if (searchSpinner) searchSpinner.classList.remove('hidden');

                fetch(`/admin/users?buscar=${encodeURIComponent(query)}&role=${encodeURIComponent(role)}`, {
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    const contentArea = document.getElementById('users-content');
                    if (contentArea) contentArea.innerHTML = html;
                    if (searchIcon) searchIcon.classList.remove('opacity-0');
                    if (searchSpinner) searchSpinner.classList.add('hidden');
                });
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initUsersFilter);
        } else {
            initUsersFilter();
        }
    </script>
</x-app-layout>
