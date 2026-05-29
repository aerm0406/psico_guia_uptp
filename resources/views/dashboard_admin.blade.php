<x-app-layout>
    <div class="pt-12 pb-4 bg-slate-50 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-10 text-slate-900">
                <h1 class="text-3xl font-black tracking-tight uppercase">Panel de Control</h1>
                <p class="mt-2 text-slate-500 font-medium">Resumen general del área Psico-Guía </p>
            </div>
            <!-- Management Tools Quick Access -->
            <div class="bg-white rounded-[2.5rem] p-10 border border-slate-100 shadow-sm">
                <h2 class="text-xl font-black text-slate-900 mb-8 uppercase tracking-tight">Atajos de navegación</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="{{ route('admin.users.create') }}" class="p-8 bg-slate-50 border border-slate-100 rounded-[2rem] hover:bg-white hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-50 transition-all group">
                        <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight">Nuevo Usuario</h4>
                        <p class="text-xs text-slate-500 mt-1">Registrar Admin/Psi/Pac</p>
                    </a>
                    
                    <a href="{{ route('admin.users.index') }}" class="p-8 bg-slate-50 border border-slate-100 rounded-[2rem] hover:bg-white hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-50 transition-all group">
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight">Ver Usuarios</h4>
                        <p class="text-xs text-slate-500 mt-1">Listado maestro completo</p>
                    </a>
                    
                    <a href="{{ route('citas.index') }}" class="p-8 bg-slate-50 border border-slate-100 rounded-[2rem] hover:bg-white hover:border-blue-200 hover:shadow-lg hover:shadow-blue-50 transition-all group">
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight">Gestionar Citas</h4>
                        <p class="text-xs text-slate-500 mt-1">Control global de solicitudes</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
