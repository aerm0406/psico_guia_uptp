@php
    $themeOptions = [
        'light' => [
            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
            'label' => 'Claro',
            'description' => 'Modo de luz brillante'
        ],
        'dark' => [
            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>',
            'label' => 'Oscuro',
            'description' => 'Modo nocturno'
        ],
        'auto' => [
            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>',
            'label' => 'Automático',
            'description' => 'Sigue al sistema'
        ]
    ];
@endphp

<div x-data="{ themeOpen: false }" @close-theme-dropdown.window="themeOpen = false" class="relative" @click.away="themeOpen = false">
    {{-- Botón principal --}}
    <button
        @click="themeOpen = !themeOpen"
        class="relative p-2 text-gray-500 hover:text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
        title="Cambiar tema"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
        </svg>
    </button>

    {{-- Dropdown de temas --}}
    <div
        x-show="themeOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
        class="absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden dark:bg-gray-800 dark:border-gray-700"
        style="display: none;"
    >
        <div class="p-3">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 px-2 dark:text-white">Selecciona un tema</h3>
            <div class="space-y-1">
                @foreach($themeOptions as $themeKey => $themeData)
                    <button
                        id="theme-option-{{ $themeKey }}"
                        onclick="window.changeTheme('{{ $themeKey }}');"
                        class="theme-option w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <span class="text-gray-500 dark:text-gray-400">{!! $themeData['icon'] !!}</span>
                        <div class="flex-1 text-left">
                            <div class="text-sm font-medium">{{ $themeData['label'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $themeData['description'] }}</div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    // Función global para cambiar tema
    window.changeTheme = function(theme) {
        const html = document.documentElement;

        // Remover clases existentes
        html.classList.remove('dark', 'light');

        // Aplicar tema
        if (theme === 'auto') {
            const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (isDark) {
                html.classList.add('dark');
            }
            localStorage.setItem('theme', 'auto');
        } else if (theme === 'dark') {
            html.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }

        // Actualizar atributo data-theme
        if (html.classList.contains('dark')) {
            html.setAttribute('data-theme', 'dark');
        } else {
            html.setAttribute('data-theme', 'light');
        }
        html.setAttribute('data-user-theme', theme);

        // Actualizar clases activas en los botones
        document.querySelectorAll('.theme-option').forEach(btn => {
            const onclickAttr = btn.getAttribute('onclick');
            if (onclickAttr) {
                const match = onclickAttr.match(/'([^']+)'/);
                if (match) {
                    const btnTheme = match[1];
                    if (btnTheme === theme) {
                        btn.classList.add('bg-blue-50', 'text-blue-600', 'dark:bg-blue-900', 'dark:text-blue-300');
                        btn.classList.remove('text-gray-700', 'hover:bg-gray-50', 'dark:text-gray-300', 'dark:hover:bg-gray-700');
                    } else {
                        btn.classList.remove('bg-blue-50', 'text-blue-600', 'dark:bg-blue-900', 'dark:text-blue-300');
                        btn.classList.add('text-gray-700', 'hover:bg-gray-50', 'dark:text-gray-300', 'dark:hover:bg-gray-700');
                    }
                }
            }
        });

        // Cerrar el dropdown enviando un evento de Alpine
        window.dispatchEvent(new CustomEvent('close-theme-dropdown'));
    };

    // Inicializar tema - Esta función ya no es necesaria porque el script crítico en app.blade.php ya lo hace
    // Pero la mantenemos para sincronizar el estado activo de los botones
    function updateActiveThemeButton() {
        const savedTheme = localStorage.getItem('theme') || 'auto';
        document.querySelectorAll('.theme-option').forEach(btn => {
            const onclickAttr = btn.getAttribute('onclick');
            if (onclickAttr) {
                const match = onclickAttr.match(/'([^']+)'/);
                if (match) {
                    const btnTheme = match[1];
                    if (btnTheme === savedTheme) {
                        btn.classList.add('bg-blue-50', 'text-blue-600', 'dark:bg-blue-900', 'dark:text-blue-300');
                        btn.classList.remove('text-gray-700', 'hover:bg-gray-50', 'dark:text-gray-300', 'dark:hover:bg-gray-700');
                    } else {
                        btn.classList.remove('bg-blue-50', 'text-blue-600', 'dark:bg-blue-900', 'dark:text-blue-300');
                        btn.classList.add('text-gray-700', 'hover:bg-gray-50', 'dark:text-gray-300', 'dark:hover:bg-gray-700');
                    }
                }
            }
        });
    }

    // Actualizar el botón activo cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        updateActiveThemeButton();

        // Escuchar cambios del sistema
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            const userTheme = localStorage.getItem('theme');
            if (userTheme === 'auto') {
                const html = document.documentElement;
                if (e.matches) {
                    html.classList.add('dark');
                    html.setAttribute('data-theme', 'dark');
                } else {
                    html.classList.remove('dark');
                    html.setAttribute('data-theme', 'light');
                }
            }
        });
    });
</script>
