<x-app-layout>
    <div class="py-12 bg-slate-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Header Section -->
            <div class="flex items-center justify-between px-4 sm:px-0">
                <div>
                    <h2 class="text-3xl font-extrabold text-indigo-900 dark:text-indigo-400 tracking-tight">
                        {{ __('Mi Perfil') }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-700 dark:text-gray-300 font-medium">
                        {{ __('Visualiza y gestiona tu información personal y de cuenta.') }}
                    </p>
                    <span class="inline-block mt-2 px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400 text-xs font-bold rounded-full uppercase tracking-wider shadow-sm">
                        Rol: {{ ucfirst(Auth::user()->role) }}
                    </span>
                </div>

                @php
                    $nombreCompleto = trim((Auth::user()->nombres ?? '') . ' ' . (Auth::user()->apellidos ?? ''));
                    $partes = explode(' ', trim($nombreCompleto));
                    $primerNombre = $partes[0] ?? '';
                    $primerApellido = $partes[1] ?? '';
                    $iniciales = strtoupper(substr($primerNombre, 0, 1) . substr($primerApellido, 0, 1));
                @endphp
                <div class="w-16 h-16 bg-white dark:bg-gray-800 border-2 border-slate-100 dark:border-gray-700 rounded-full flex items-center justify-center text-slate-800 dark:text-white font-bold text-2xl shadow-sm">
                    {{ $iniciales }}
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl border border-slate-100 dark:border-gray-700">
                <div class="w-full">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
