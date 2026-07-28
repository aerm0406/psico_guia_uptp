<x-app-layout>
    <div class="pt-12 pb-4 bg-slate-50 dark:bg-gray-900 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs / Back Link -->
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-gray-200 text-sm font-medium mb-6 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Volver al listado
            </a>

            <!-- Header -->
            <div class="mb-10">
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Nuevo Usuario</h1>
                <p class="mt-2 text-slate-500 dark:text-gray-400 text-sm">Registra un nuevo integrante en el sistema definiendo su rol y credenciales.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-slate-100 dark:border-gray-700 shadow-sm overflow-hidden text-slate-900 dark:text-gray-100">
                <form action="{{ route('admin.users.store') }}" method="POST" class="p-8">
                    @csrf
                    @include('admin.users.components.user_form')

                    <div class="mt-10 flex items-center justify-end gap-3 border-t border-slate-50 dark:border-gray-700 pt-8">
                        <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 text-sm font-bold text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-gray-200 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 dark:shadow-indigo-900/30 transition-all">
                            Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
