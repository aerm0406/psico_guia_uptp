<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <!--
            <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                Gestión de Prioridades
            </h2>
            
            <a href="{{ route('agenda.index') }}" class="text-sm font-bold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-4 py-2 rounded-xl transition-colors">Volver a la Agenda</a>
        </div>
        -->
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 rounded-[32px] shadow-sm border border-slate-100 dark:border-gray-700 overflow-hidden relative">
                <!-- Decorative blue border on left similar to Historial -->
                <div class="absolute top-0 left-0 bottom-0 w-2 bg-sky-600 dark:bg-sky-500 z-10"></div>
                
                <div class="p-8 pl-10">
                    <div class="mb-6 flex flex-col sm:flex-row justify-between items-center border-b pb-6 dark:border-gray-700">
                        <div>
                            <h2 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Listado de Prioridades</h2>
                            <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] mt-0.5">Niveles de gravedad en agenda</p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex items-center gap-3">
                            <form action="{{ route('agenda.prioridades.index') }}" method="GET" class="relative">
                                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o nivel..." class="h-10 pl-9 pr-4 text-xs rounded-xl border border-slate-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-700 dark:text-gray-200 focus:ring-sky-500 focus:border-sky-500 w-48 transition-all">
                                <svg class="w-4 h-4 absolute left-3 top-3 text-slate-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </form>
                        <!--    <span class="text-[10px] font-black text-slate-300 dark:text-gray-600 uppercase tracking-widest">TOTAL: {{ $prioridades->count() }} NIVELES</span> -->
                            <a href="{{ route('agenda.prioridades.create') }}" class="flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 h-10 rounded-xl shadow-sm transition-all" title="Crear Prioridad">
                                <svg class="w-4 h-4 opacity-80 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                <span class="text-[10px] font-black uppercase tracking-wide">Crear</span>
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-[32px] border border-slate-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                        <table class="min-w-full divide-y divide-slate-100 dark:divide-gray-700 text-sm">
                            <thead class="bg-slate-50/50 dark:bg-gray-700/30">
                                <tr>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Nombre</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest text-center">Nivel</th>
                                <!--<th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest text-center">Origen</th>-->
                                    <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-gray-700">
                                @forelse($prioridades as $prioridad)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/30 transition-colors group">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full {{ 
                                                    $prioridad->nombre === 'crítica' ? 'bg-rose-500' : 
                                                    ($prioridad->nombre === 'alta' ? 'bg-amber-500' : 
                                                    ($prioridad->nombre === 'media' ? 'bg-sky-500' : 
                                                    ($prioridad->nombre === 'baja' ? 'bg-emerald-500' : 'bg-indigo-500')))
                                                }}"></div>
                                                <span class="font-bold text-slate-700 dark:text-gray-200 uppercase tracking-wide text-xs">
                                                    {{ $prioridad->nombre }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-slate-500 bg-slate-50 dark:bg-gray-700 px-3 py-1 rounded-lg border border-slate-200 dark:border-gray-600">
                                                {{ $prioridad->nivel_gravedad }}
                                            </span>
                                        </td>

                                    <!--     <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($prioridad->psicologo_id === null)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded-md uppercase tracking-wider">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                                    Sistema
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-md uppercase tracking-wider">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                    Personalizada
                                                </span>
                                            @endif
                                        </td>

                                    -->    
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                @if($prioridad->psicologo_id === null)
                                                    <div class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-300 dark:text-gray-600" title="Prioridad del sistema (no modificable)">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                    </div>
                                                @else
                                                    <form id="form-delete-{{$prioridad->id}}" action="{{ route('agenda.prioridades.destroy', $prioridad->id) }}" method="POST" class="inline">
                                                        @csrf @method('DELETE')
                                                        <button type="button" onclick="confirmDeletePrioridad({{$prioridad->id}}, '{{$prioridad->nivel_gravedad}}', {{$prioridad->uso_count}})" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition-colors" title="Eliminar Prioridad">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">No hay prioridades registradas</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($prioridades->hasPages())
                        <div class="mt-8 flex justify-center">
                            {{ $prioridades->appends(request()->query())->links('agenda.partials.pagination') }}
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<script>
    function confirmDeletePrioridad(id, nivel, usoCount) {
        if (usoCount > 0) {
            window.AppModal.show(
                'Acción no permitida',
                'No puedes eliminar esta prioridad porque está siendo utilizada por uno o más pacientes.',
                { type: 'alert', btnText: 'Entendido' }
            );
        } else {
            window.AppModal.show(
                '¿Eliminar Prioridad?',
                '¿Seguro que deseas eliminar esta prioridad? El nivel ' + nivel + ' quedará libre.',
                { type: 'confirm', btnText: 'Sí, eliminar', intent: 'danger' }
            ).then(result => {
                if (result) document.getElementById('form-delete-' + id).submit();
            });
        }
    }
</script>
