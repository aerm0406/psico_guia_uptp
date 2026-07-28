<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="pt-12 pb-4 bg-slate-50 dark:bg-gray-900 min-h-[calc(100vh-4rem)] flex flex-col">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col w-full">



            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Avances de Sesión</h1>
                    <p class="mt-2 text-slate-500 dark:text-gray-400 text-sm">Gestiona las opciones de avance configurables para las notas de evolución.</p>
                </div>
                <div class="flex items-center gap-3">
                    <form action="{{ route('avances_sesion.index') }}" method="GET" class="relative w-full md:w-64 lg:w-80">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o nivel..."
                            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-gray-900 dark:text-white">
                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-slate-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </form>
                    <a href="{{ route('avances_sesion.create') }}" class="group flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-sm font-bold shadow-md shadow-indigo-200 dark:shadow-indigo-900/30 transition-all active:scale-95 shrink-0">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Crear
                    </a>
                </div>
            </div>

            @if($avances->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-[32px] border-2 border-dashed border-slate-200 dark:border-gray-700 p-16 text-center shadow-sm">
                    <div class="w-20 h-20 bg-slate-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-slate-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No hay avances registrados</h3>
                    <p class="text-slate-500 dark:text-gray-400 max-w-sm mx-auto leading-relaxed">
                        Comienza creando opciones de avance para seleccionarlos en tus notas de evolución.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($avances as $avance)
                        @php
                            $valorColor = match(true) {
                                $avance->valor >= 4 => ['from-emerald-500 to-teal-600', 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800', 'text-emerald-500'],
                                $avance->valor >= 2 => ['from-amber-500 to-orange-600', 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-800', 'text-amber-500'],
                                default => ['from-rose-500 to-pink-600', 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-800', 'text-rose-500'],
                            };
                        @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-[32px] border border-slate-100 dark:border-gray-700 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 group overflow-hidden flex flex-col">
                            <div class="p-8 flex-1">
                                <!-- Icon & Title -->
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $valorColor[0] }} flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-200/50 dark:shadow-none group-hover:scale-110 transition-transform">
                                        {{ $avance->valor }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white truncate tracking-tight">{{ $avance->nombre }}</h3>
                                        <div class="flex gap-2 items-center flex-wrap mt-1">
                                            @if($avance->es_sistema)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 uppercase tracking-wider">
                                                    Sistema
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-gray-700 text-slate-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Personalizado
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Stats Row -->
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-slate-50 dark:bg-gray-700/50 rounded-2xl p-3">
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Valor</p>
                                        <p class="text-lg font-black {{ $valorColor[2] }}">{{ $avance->valor }}</p>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-gray-700/50 rounded-2xl p-3">
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Tipo</p>
                                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $avance->es_sistema ? 'Sistema' : 'Propio' }}</p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <p class="text-sm text-slate-500 dark:text-gray-400 font-medium leading-relaxed line-clamp-3">
                                    {{ $avance->descripcion ?? 'Sin descripción proporcionada para este avance.' }}
                                </p>
                            </div>

                            <!-- Footer Actions -->
                            @if(!$avance->es_sistema)
                                @php
                                    $enUso = \App\Models\AvanceSesion::enUsoUltimaNota($avance->id, Auth::id());
                                @endphp
                                @if(!$enUso)
                                    <div class="flex items-center border-t border-slate-50 dark:border-gray-700">
                                        <a href="{{ route('avances_sesion.edit', $avance->id) }}" class="flex-1 flex items-center justify-center gap-2 p-4 text-sm font-bold text-slate-500 dark:text-gray-400 hover:bg-indigo-600 hover:text-white transition-colors" title="Editar Avance">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Editar
                                        </a>
                                        <div class="w-px h-8 bg-slate-100 dark:bg-gray-700"></div>
                                        <form action="{{ route('avances_sesion.destroy', $avance->id) }}" method="POST" class="flex-1" onsubmit="event.preventDefault(); AppModal.confirm('Confirmar', '¿Estás seguro de eliminar este avance? Se borrará lógicamente.').then(c => { if(c) this.submit(); });">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full flex items-center justify-center gap-2 p-4 text-sm font-bold text-rose-500 dark:text-rose-400 hover:bg-rose-600 hover:text-white transition-colors" title="Eliminar Avance">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="bg-slate-50/50 dark:bg-gray-700/30 p-4 text-center border-t border-slate-50 dark:border-gray-700">
                                        <span class="text-xs font-bold text-slate-400 dark:text-gray-500 tracking-wider flex items-center justify-center gap-2" title="En uso en la última nota">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            EN USO (BLOQUEADO)
                                        </span>
                                    </div>
                                @endif
                            @else
                                <div class="bg-slate-50/50 dark:bg-gray-700/30 p-4 text-center border-t border-slate-50 dark:border-gray-700">
                                    <span class="text-sm font-bold text-slate-400 dark:text-gray-500 flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Predefinido del sistema
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-auto flex justify-center pb-2 pt-12">
                    {{ $avances->appends(request()->query())->links('avances_sesion.partials.pagination') }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
