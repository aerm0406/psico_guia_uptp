{{-- Encabezado Estilo "Perfil" --}}
<div class="flex items-start justify-between gap-4 border-b border-gray-100 px-8 py-7 bg-white">
    <div>
        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-700">Información del Usuario</p>
        <h2 class="mt-2 text-2xl font-black text-slate-900 tracking-tight leading-tight">{{ $usuario->name }}</h2>
        <p class="mt-1 text-sm text-slate-500 font-medium italic">Cuenta del {{ ucfirst($usuario->role) }} </p>
    </div>
    <button type="button" @click="show = false" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition" aria-label="Cerrar">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>

<div class="p-8 space-y-10">
    {{-- Sección: Información Personal --}}
    <section>
        <div class="flex items-center gap-3 mb-6">
            <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 tracking-tight">Información Personal</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 transition-colors hover:bg-white hover:border-indigo-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Cédula</p>
                <p class="text-slate-900 font-medium">{{ $usuario->cedula ?? 'No disponible' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 transition-colors hover:bg-white hover:border-indigo-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Email</p>
                <p class="text-slate-900 font-medium break-all">
                    {{ $usuario->email ?? 'No disponible' }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 transition-colors hover:bg-white hover:border-indigo-200 shadow-sm relative overflow-hidden group">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Registrado</p>
                <div class="flex items-center gap-2">
                    <p class="text-slate-900 font-medium">
                        {{ \Carbon\Carbon::parse($usuario->created_at)->format('d/m/Y') }}
                    </p>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1 bg-slate-100 group-hover:bg-indigo-500 transition-colors"></div>
            </div>
        </div>
    </section>

    {{-- Sección: Seguridad y Estatus --}}
    <section class="pt-4 border-t border-slate-50">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 tracking-tight">Seguridad y Estatus</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 transition-colors hover:bg-white hover:border-emerald-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Estatus Cuenta</p>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse shadow-sm shadow-emerald-200"></span>
                        <p class="text-emerald-700 font-bold text-xs uppercase tracking-tight">ACTIVA</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 transition-colors hover:bg-white hover:border-emerald-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Compleción de Perfil</p>
                <p class="text-slate-900 font-medium">
                    {{ $usuario->profile_completed ? 'Completado (100%)' : 'Pendiente por completar' }}
                </p>
            </div>
        </div>
    </section>

    {{-- Acciones Finales --}}
    <div class="pt-8 border-t border-slate-100 flex flex-col sm:flex-row justify-end gap-3">
        <button type="button" @click="show = false" class="px-8 py-3 bg-slate-100 text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-600 hover:text-white transition-all">
            Cerrar
        </button>
        {{--
        <a href="{{ route('admin.users.edit', $usuario->id) }}" class="px-8 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center justify-center gap-2 group">
            Gestionar Perfil
            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
        </a>
        --}}
    </div>
</div>
