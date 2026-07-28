<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-12 bg-slate-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Notificaciones (Solo Errores) --}}
            @if (session('error'))
                <div class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><strong class="font-black uppercase tracking-wider text-[10px] block mb-0.5">Error</strong>{{ session('error') }}</span>
            </div>
            @endif

            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Estados de Ánimo</h2>
                    <p class="text-sm font-bold text-slate-500 dark:text-gray-400 mt-2 uppercase tracking-widest">Escala emocional del 1 al 10</p>
            </div>
                <div class="flex items-center gap-4">
                    <form action="{{ route('agenda.estado_animos.index') }}" method="GET" class="relative w-full md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="w-full bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 rounded-2xl pl-9 pr-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-xs text-gray-900 dark:text-white shadow-sm" placeholder="Buscar por nombre o nivel...">
                    </form>
                    <a href="{{ route('agenda.estado_animos.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-sm font-bold shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30 transition-all active:scale-95 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Crear
                    </a>
            </div>
        </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @forelse($estados as $estado)
                    @php
                        // Color based on valor (1=red, 5=yellow, 10=green)
                        $colorClass = match(true) {
                            $estado->valor <= 2 => 'bg-rose-500',
                            $estado->valor <= 4 => 'bg-amber-500',
                            $estado->valor <= 6 => 'bg-yellow-400',
                            $estado->valor <= 8 => 'bg-emerald-400',
                            default => 'bg-emerald-600',
                        };
                        $barWidth = ($estado->valor / 10) * 100;
                    @endphp                    <div class="bg-white dark:bg-gray-800 rounded-[32px] p-8 shadow-sm border border-slate-100 dark:border-gray-700 relative group hover:shadow-xl transition-all duration-300 flex flex-col h-full">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center shrink-0">
                                <span class="font-black text-lg">{{ $estado->valor }}</span>
                        </div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight leading-tight uppercase">{{ $estado->nombre }}</h3>
                    </div>
                        
                        <div class="flex-grow mb-8 mt-2">
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest w-16">Valor:</span>
                                <div class="w-full bg-slate-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden flex-grow">
                                    <div class="h-full rounded-full {{ $colorClass }}" style="width: {{ $barWidth }}%"></div>
                            </div>
                                <span class="text-[10px] font-black text-slate-500 w-8 text-right">{{ $estado->valor }}/10</span>
                        </div>
                    </div>

                        @php
                            $enUso = \App\Models\EstadoAnimo::enUsoUltimaNota($estado->id);
                        @endphp
                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-gray-700 mt-auto">
                            @if(!$enUso)
                                <a href="{{ route('agenda.estado_animos.edit', $estado->id) }}" class="px-4 py-2 bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 rounded-xl hover:bg-sky-600 hover:text-white transition-colors" title="Editar Estado de Ánimo">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('agenda.estado_animos.destroy', $estado->id) }}" method="POST" class="inline-block" onsubmit="event.preventDefault(); AppModal.confirm('Confirmar', '¿Estás seguro de eliminar este estado de ánimo? El valor quedará libre.').then(c => { if(c) this.submit(); });">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-xl hover:bg-rose-600 hover:text-white transition-colors" title="Eliminar Estado de Ánimo">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest px-2" title="Este estado está siendo usado en la última nota de evolución de un paciente y no puede editarse ni eliminarse.">En uso (Bloqueado)</span>
                            @endif
                        </div>
                </div>
                @empty
                    <div class="col-span-full">
                        <div class="bg-white dark:bg-gray-800 rounded-[32px] border-2 border-dashed border-slate-200 dark:border-gray-700 p-16 text-center shadow-sm">
                            <div class="w-20 h-20 bg-slate-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 dark:text-gray-500">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No hay estados de ánimo registrados</h3>
                            <p class="text-slate-500 dark:text-gray-400 max-w-sm mx-auto">Crea estados de ánimo para asignar una escala emocional del 1 al 10 y usarla en las citas.</p>
                    </div>
                </div>
                @endforelse
            </div>

            @if($estados->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $estados->appends(request()->query())->links('agenda.partials.pagination') }}
            </div>
            @endif
    </div>
    </div>
    </div>
    </div>
</x-app-layout>
