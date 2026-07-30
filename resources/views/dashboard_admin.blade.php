<x-app-layout>
    <div class="pt-12 pb-4 bg-slate-50 dark:bg-gray-900 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-10 text-slate-900 dark:text-white">
                <h1 class="text-3xl font-black tracking-tight uppercase">Panel de Control</h1>
                <p class="mt-2 text-slate-500 dark:text-gray-400 font-medium">Resumen general del área Psico-Guía</p>
            </div>
            <!-- Management Tools Quick Access -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-10 border border-slate-100 dark:border-gray-700 shadow-sm">
                <h2 class="text-xl font-black text-slate-900 dark:text-white mb-8 uppercase tracking-tight">Atajos de navegación</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6">
                    <a href="{{ route('admin.users.create') }}" class="p-8 bg-slate-50 dark:bg-gray-700/50 border border-slate-100 dark:border-gray-700 rounded-[2rem] hover:bg-white dark:hover:bg-gray-700 hover:border-indigo-200 dark:hover:border-indigo-800 hover:shadow-lg hover:shadow-indigo-50 dark:hover:shadow-indigo-900/30 transition-all group">
                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Nuevo Usuario</h4>
                        <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Registrar Admin/Psi/Pac</p>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="p-8 bg-slate-50 dark:bg-gray-700/50 border border-slate-100 dark:border-gray-700 rounded-[2rem] hover:bg-white dark:hover:bg-gray-700 hover:border-emerald-200 dark:hover:border-emerald-800 hover:shadow-lg hover:shadow-emerald-50 dark:hover:shadow-emerald-900/30 transition-all group">
                        <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">Ver Usuarios</h4>
                        <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Listado maestro completo</p>
                    </a>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
