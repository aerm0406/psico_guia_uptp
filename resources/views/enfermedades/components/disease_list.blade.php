<div id="disease-list-container" class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Nombre</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Variación / Tipo</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Categoría</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($enfermedades as $enfermedad)
                    <tr class="disease-row hover:bg-slate-50/50 transition-colors group"
                        data-search="{{ strtolower($enfermedad->nombre . ' ' . $enfermedad->tipo . ' ' . $enfermedad->categoria) }}">
                        <td class="px-8 py-5">
                            <span class="text-sm font-bold text-slate-700">{{ $enfermedad->nombre }}</span>
                        </td>
                        <td class="px-8 py-5">
                            <p class="text-xs text-slate-500 italic">{{ $enfermedad->tipo ?: 'Sin variación' }}</p>
                        </td>
                        <td class="px-8 py-5">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider 
                                {{ $enfermedad->categoria === 'mental' ? 'bg-indigo-50 text-indigo-600' : ($enfermedad->categoria === 'biopsicosocial' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600') }}">
                                @if($enfermedad->categoria === 'mental')
                                    Psiquiátrica
                                @elseif($enfermedad->categoria === 'biopsicosocial')
                                    Biopsicosocial
                                @else
                                    Médica
                                @endif
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex items-center justify-end gap-2 transition-opacity">
                                <!-- Botón Vincular a Historia -->
                                @if($returnTo && $historiaId)
                                    <button type="button" 
                                        onclick="seleccionarEnfermedad({{ $enfermedad->id }}, '{{ $enfermedad->nombre }}')"
                                        class="p-2 text-emerald-500 hover:bg-emerald-50 rounded-xl transition-all group/select" 
                                        title="Vincular a Historia">
                                        <svg class="w-5 h-5 transition-transform group-hover/select:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                @endif

                                <!-- Botón Ver Detalles (Ojito) -->
                                <button type="button" 
                                    onclick="verEnfermedad({{ json_encode($enfermedad) }})"
                                    class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all" 
                                    title="Ver Detalles">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>

                                <a href="{{ route('enfermedades.edit', ['enfermedad' => $enfermedad->id, 'tipo' => $tipo, 'return_to' => $returnTo, 'editing' => $editing]) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('enfermedades.destroy', $enfermedad->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                                    <input type="hidden" name="editing" value="{{ $editing }}">
                                    <input type="hidden" name="tipo_contexto" value="{{ $tipo }}">
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" onclick="return confirm('¿Eliminar esta enfermedad?')" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-8 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm font-medium">No hay enfermedades que coincidan con la búsqueda.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                
                <tr id="no-results-disease" class="hidden">
                    <td colspan="4" class="px-8 py-12 text-center text-slate-400">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="w-12 h-12 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <p class="text-sm font-medium">Buscando en todos los registros...</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @if($enfermedades->hasPages())
        <div id="disease-pagination" class="px-8 py-10 flex justify-center border-t border-slate-50">
            {{ $enfermedades->appends(request()->query())->links('pacientes.partials.pagination') }}
        </div>
    @endif
</div>
