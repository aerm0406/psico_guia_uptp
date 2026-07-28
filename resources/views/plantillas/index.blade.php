<x-app-layout>
    <div class="py-12 bg-slate-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div x-data="{ search: '', items: {{ json_encode($plantillas->pluck('titulo')->map(function($t) { return strtolower($t); })) }} }">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Anexos Clínicos</h2>
                        <p class="text-sm font-bold text-slate-500 dark:text-gray-400 mt-2 uppercase tracking-widest">Campos adicionales para evaluaciones específicas según las necesidades del paciente.</p>
                    </div>
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <div class="relative w-full md:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" x-model="search" class="w-full bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 rounded-2xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-sm text-gray-900 dark:text-white" placeholder="Buscar por título...">
                        </div>
                        <a href="{{ route('plantillas.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-sm font-bold shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30 transition-all active:scale-95 whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @forelse($plantillas as $plantilla)
                        <div x-show="search === '' || '{{ strtolower($plantilla->titulo) }}'.includes(search.toLowerCase())" class="bg-white dark:bg-gray-800 rounded-[32px] p-8 shadow-sm border border-slate-100 dark:border-gray-700 relative group hover:shadow-xl transition-all duration-300 flex flex-col h-full">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">{{ $plantilla->titulo }}</h3>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-gray-400 flex-grow mb-8">{{ $plantilla->descripcion_general ?? 'Sin descripción adicional.' }}</p>

                            <div class="flex items-center justify-between pt-6 border-t border-slate-100 dark:border-gray-700 mt-auto">
                                @if($plantilla->esta_en_uso)
                                    <span class="text-xs font-medium text-amber-600 bg-amber-50 px-3 py-1 rounded-full flex items-center gap-1" title="Esta plantilla ya está siendo utilizada por un paciente y no puede ser modificada.">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        En uso
                                    </span>
                                @else
                                    <div></div>
                                @endif
                                <div class="flex gap-3">
                                    @if(!$plantilla->esta_en_uso)
                                        <a href="{{ route('plantillas.edit', $plantilla->id) }}" class="px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl hover:bg-indigo-600 hover:text-white transition-colors" title="Editar Plantilla">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                        <form action="{{ route('plantillas.destroy', $plantilla->id) }}" method="POST" class="inline-block" onsubmit="event.preventDefault(); AppModal.confirm('Confirmar', '¿Estás seguro de eliminar esta plantilla?').then(c => { if(c) this.submit(); });">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-4 py-2 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-xl hover:bg-rose-600 hover:text-white transition-colors" title="Eliminar Plantilla">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <div class="bg-white dark:bg-gray-800 rounded-[32px] border-2 border-dashed border-slate-200 dark:border-gray-700 p-16 text-center shadow-sm">
                                <div class="w-20 h-20 bg-slate-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 dark:text-gray-500">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No tienes plantillas creadas</h3>
                                <p class="text-slate-500 dark:text-gray-400 max-w-sm mx-auto">Crea plantillas de secciones para agilizar la redacción de los historiales clínicos de tus pacientes.</p>
                            </div>
                        </div>
                    @endforelse
                    
                    <div class="col-span-full" x-show="search !== '' && !items.some(t => t.includes(search.toLowerCase()))" x-cloak>
                        <div class="bg-white dark:bg-gray-800 rounded-[32px] border border-slate-100 dark:border-gray-700 p-16 text-center shadow-sm">
                            <div class="w-16 h-16 bg-slate-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 dark:text-gray-500">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">Sin anexos clínicos encontrados</p>
                        </div>
                    </div>
                </div>

                @if($plantillas->hasPages())
                    <div class="mt-8">
                        {{ $plantillas->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
