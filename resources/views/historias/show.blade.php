<x-app-layout>


    <script>
        const _routeDesvincular = @js(route('historias.enfermedad.desvincular'));
        const _routeSeccionDestroy = @js(route('historias.secciones.destroy', 'PLACEHOLDER'));
        const _routeSeccionReorder = @js(route('historias.secciones.reorder', 'PLACEHOLDER'));
        const _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    </script>
    <style>
        .seccion-dinamica:first-of-type .btn-subir {
            display: none !important;
        }
        .seccion-dinamica:last-of-type .btn-bajar {
            display: none !important;
        }
    </style>
    <div class="py-12 bg-slate-50 min-h-screen" x-data="{ 
        tab: '{{ request()->query('tab', 'expediente') }}', 
        showStats: false, 
        isEditing: true,
        hasUnsavedChanges: false,
        showUnsavedModal: false,
        pendingUrl: null,
        vinculados: @js($enfermedadesVinculadas->mapWithKeys(fn($items, $key) => [$key => $items->map(fn($v) => ['link_id' => $v->link_id, 'nombre' => $v->nombre])])),
        searchQuery: '',
        matchesSearch(title) {
            if (!this.searchQuery) return true;
            const normalize = (str) => str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            return normalize(title).includes(normalize(this.searchQuery));
        },
        
        init() {
            window.addEventListener('beforeunload', (e) => {
                if (this.hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        },
        handleNavigation(e) {
            let link = e.target.closest('a');
            if (link && link.href && !link.href.includes('#') && link.target !== '_blank' && !link.hasAttribute('download')) {
                if (this.hasUnsavedChanges) {
                    e.preventDefault();
                    this.pendingUrl = link.href;
                    this.showUnsavedModal = true;
                }
            }
        },
        confirmLeave() {
            this.hasUnsavedChanges = false;
            if (this.pendingUrl) {
                window.location.href = this.pendingUrl;
            }
        },
        desvincular(linkId) {
            if(!confirm('¿Desvincular esta condición?')) return;
            fetch(_routeDesvincular, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': _csrfToken, 
                    'Content-Type': 'application/json' 
                },
                body: JSON.stringify({ link_id: linkId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    this.hasUnsavedChanges = true;
                    for (let key in this.vinculados) {
                        this.vinculados[key] = this.vinculados[key].filter(v => v.link_id !== linkId);
                    }
                }
            });
        },
        deleteSection(id) {
            if (!confirm('¿Estás seguro de eliminar esta sección? Se perderán todos los segmentos y datos guardados.')) return;
            let url = _routeSeccionDestroy.replace('PLACEHOLDER', id);
            fetch(url, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': _csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                this.hasUnsavedChanges = false;
                window.location.reload();
            });
        },
        reorderSection(id, direction) {
            let url = _routeSeccionReorder.replace('PLACEHOLDER', id);
            fetch(url, {
                method: 'PATCH',
                headers: { 
                    'X-CSRF-TOKEN': _csrfToken,
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ direccion: direction })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    let seccionActual = document.getElementById('seccion-' + id);
                    if (seccionActual) {
                        if (direction === 'up' && seccionActual.previousElementSibling && seccionActual.previousElementSibling.classList.contains('seccion-dinamica')) {
                            seccionActual.parentNode.insertBefore(seccionActual, seccionActual.previousElementSibling);
                        } else if (direction === 'down' && seccionActual.nextElementSibling && seccionActual.nextElementSibling.classList.contains('seccion-dinamica')) {
                            seccionActual.parentNode.insertBefore(seccionActual.nextElementSibling, seccionActual);
                        }
                    }
                } else {
                    alert('Error al reordenar la sección.');
                }
            });
        },
        vincular(enfermedadId, contexto) {
            fetch(@js(route('historias.enfermedad.vincular')), {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': _csrfToken, 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    historia_clinica_id: {{ $historia->id }},
                    enfermedad_id: enfermedadId,
                    contexto: contexto
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if(!this.vinculados[contexto]) this.vinculados[contexto] = [];
                    this.vinculados[contexto].push({
                        link_id: data.link_id,
                        nombre: data.nombre
                    });
                    this.hasUnsavedChanges = true;
                    this.$dispatch('linked-' + contexto);
                } else {
                    alert('Error al vincular: ' + (data.message || 'Desconocido'));
                }
            });
        }
    }" @click.window="handleNavigation($event)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumbs & Back -->
            <div class="mb-8">
                <a href="{{ route('historias.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors font-bold text-sm group">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Volver al listado
                </a>
            </div>

            <!-- Profile Header -->
            <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100 mb-8 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50 rounded-full -mr-32 -mt-32 opacity-50"></div>
                
                <div class="relative flex flex-col md:flex-row md:items-center gap-8">
                    @php
                        // Datos para el modal
                        $fechaCita = $paciente->primera_cita ? \Carbon\Carbon::parse($paciente->primera_cita)->format('d/m/Y') : 'No disponible';
                        $edad = $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age : 'No disponible';
                        $nacimiento = $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') : 'No disponible';
                        $nombreCompleto = $paciente->name ?? '';
                        $partes = explode(' ', trim($nombreCompleto));
                        $primerNombre = $partes[0] ?? '';
                        $primerApellido = $partes[1] ?? '';
                        $iniciales = strtoupper(substr($primerNombre, 0, 1) . substr($primerApellido, 0, 1));
                    @endphp
                    <button type="button" 
                            class="open-patient-modal w-24 h-24 bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl flex items-center justify-center text-white font-bold text-3xl shadow-xl shadow-indigo-200 hover:scale-105 transition-transform active:scale-95"
                            data-patient-type="user"
                            data-patient-name="{{ $paciente->name }}" 
                            data-patient-email="{{ $paciente->email ?? 'No disponible' }}" 
                            data-patient-phone="{{ $paciente->telefono ?? 'No disponible' }}" 
                            data-patient-created="{{ $fechaCita }}"
                            data-patient-cedula="{{ $paciente->cedula ?? 'No disponible' }}"
                            data-patient-genero="{{ $paciente->genero ?? 'No disponible' }}"
                            data-patient-nacimiento="{{ $nacimiento }}"
                            data-patient-ubicacion="{{ $paciente->ubicacion ?? 'No disponible' }}"
                            data-patient-discapacidad="{{ ($paciente->discapacidad ?? 'No') == 'Si' ? $paciente->tipo_discapacidad : 'Ninguna' }}"
                            data-patient-hijos="{{ ($paciente->tiene_hijos ?? 'No') == 'Si' ? $paciente->numero_hijos : 'Ninguno' }}"
                            data-patient-civil="{{ $paciente->estado_civil ?? 'No disponible' }}"
                            data-patient-perfil-academico="{{ $paciente->perfil_academico ?? 'Sin definir' }}"
                            data-patient-pnf="{{ $paciente->pnf ?? 'No aplica' }}"
                            data-patient-semestre="{{ $paciente->semestre ? $paciente->semestre . '° Semestre' : 'No aplica' }}"
                            data-patient-edad="{{ $edad }}"
                            title="Ver perfil completo">
                        {{ $iniciales }}
                    </button>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ $paciente->name }}</h2>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 uppercase tracking-widest">Paciente Activo</span>
                        </div>
                        <div class="flex flex-wrap gap-4 text-sm text-slate-500 font-medium tracking-wide">
                            <div class="flex items-center gap-3 bg-indigo-50 px-4 py-2 rounded-2xl border border-indigo-100/50 shadow-sm">
                                <span class="flex items-center gap-2 text-indigo-600 font-black" title="Sesiones completadas con éxito">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    {{ $stats['realizadas'] }} Sesiones Realizadas
                                </span>
                                <div class="w-px h-4 bg-indigo-200"></div>
                                <button @click="showStats = true" class="flex items-center gap-1.5 text-indigo-500 hover:text-indigo-700 transition-colors group/btn" title="Ver historial de inasistencias y cancelaciones">
                                    <svg class="w-5 h-5 group-hover/btn:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <span class="text-[10px] uppercase font-black tracking-widest">Resumen de Actividad</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('historias.downloadZip', $paciente->id) }}" title="Descargar Expediente Completo (ZIP)" class="p-3 bg-slate-50 text-slate-600 rounded-2xl hover:bg-slate-100 transition shadow-sm border border-slate-200 inline-block">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2-2v4a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Dashboard Navigation & Search -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                <div class="flex gap-1 bg-white p-1.5 rounded-[24px] shadow-sm border border-slate-100 w-fit">
                    <button type="button" @click="tab = 'expediente'" :class="tab === 'expediente' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-slate-500 hover:bg-slate-50'" class="px-8 py-3 rounded-2xl text-sm font-bold transition-all duration-300">
                        Expediente General
                    </button>
                    <button type="button" @click="tab = 'evolucion'" :class="tab === 'evolucion' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-slate-500 hover:bg-slate-50'" class="px-8 py-3 rounded-2xl text-sm font-bold transition-all duration-300">
                        Línea de Evolución
                    </button>
                </div>

                <!-- Buscador de secciones -->
                <div x-show="tab === 'expediente'" x-transition class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text"
                           id="buscador-secciones"
                           x-model="searchQuery"
                           @input="searchQuery = $event.target.value"
                           placeholder="Buscar sección..."
                           class="w-full bg-white border border-slate-200 rounded-[20px] py-3 pl-12 pr-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all shadow-sm placeholder-slate-400">
                    <span x-show="searchQuery" x-cloak class="absolute inset-y-0 right-0 flex items-center pr-4">
                        <button type="button" @click="searchQuery = ''" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </span>
                </div>
            </div>

            <!-- Tab Content: Expediente -->
            <div x-show="tab === 'expediente'">
                <form action="{{ route('historias.update', $paciente->id) }}" method="POST" @input="hasUnsavedChanges = true" @submit="hasUnsavedChanges = false">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-12">
                        @foreach($seccionesPersonalizadas as $indexKey => $seccion)
                            <div id="seccion-{{ $seccion->id }}" 
                                 class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100 relative group seccion-dinamica"
                                 x-show="matchesSearch('{{ addslashes($seccion->titulo) }}')"
                                 x-transition>
                                <div class="flex items-center justify-between mb-8">
                                    <div>
                                        <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ $seccion->titulo }}</h3>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $seccion->descripcion_general ?? 'Sección Personalizada' }}</p>
                                    </div>

                                    <!-- Reorder & Delete Buttons (Only in edit mode) -->
                                    <div x-show="isEditing" class="flex items-center gap-1">
                                        <!-- Reorder Up -->
                                        <button type="button" @click.stop.prevent="reorderSection({{ $seccion->id }}, 'up')" class="btn-subir p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="Subir sección">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"></path></svg>
                                        </button>
                                        <!-- Reorder Down -->
                                        <button type="button" @click.stop.prevent="reorderSection({{ $seccion->id }}, 'down')" class="btn-bajar p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="Bajar sección">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>
                                        <!-- Delete -->
                                        <button type="button" @click.stop.prevent="deleteSection({{ $seccion->id }})" class="p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Eliminar sección">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 {{ $seccion->segmentos->count() > 1 ? 'md:grid-cols-2' : '' }} gap-10">
                                    @foreach($seccion->segmentos as $segmento)
                                        <div class="space-y-4">
                                            <div class="mb-4">
                                                <div x-show="isEditing">
                                                    <input type="text" name="segmentos_metadata[{{ $segmento->id }}][titulo]" value="{{ $segmento->titulo }}" 
                                                           class="bg-transparent border-none p-0 text-xs font-black text-indigo-500 uppercase tracking-widest focus:ring-0 w-full" placeholder="Título del segmento">
                                                </div>
                                                <div x-show="!isEditing">
                                                    <label class="text-xs font-black text-indigo-500 uppercase tracking-widest">{{ $segmento->titulo ?? 'Información' }}</label>
                                                </div>
                                            </div>

                                            {{-- [NUEVO] Etiquetas de Enfermedades Dinámicas --}}
                                            <div class="flex flex-wrap gap-2 mb-3">
                                                <template x-for="vinculo in (vinculados['seg_{{ $segmento->id }}'] || [])" :key="vinculo.link_id">
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-wider rounded-full border border-indigo-100 group/tag">
                                                        <span x-text="vinculo.nombre"></span>
                                                        <button type="button" @click="desvincular(vinculo.link_id)" x-show="isEditing" class="hover:text-indigo-600 transition-colors">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </span>
                                                </template>
                                            </div>
                                            
                                            <textarea name="segmentos_extra[{{ $segmento->id }}]" rows="4" :readonly="!isEditing" :class="isEditing ? 'bg-white border-indigo-200' : 'bg-slate-50 border-none pointer-events-none text-slate-600'" class="w-full border rounded-2xl p-5 text-sm focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder-slate-300" placeholder="Escribe aquí los detalles...">{{ $segmento->contenido }}</textarea>

                                            <!-- [NUEVO] Disease Searcher para Segmento Dinámico -->
                                            <div class="flex justify-end -mt-2 pr-2" x-show="isEditing" x-transition
                                                 x-data="{ 
                                                    isOpen: false, query: '', results: [], loading: false,
                                                    search() {
                                                        if(this.query.length < 2) return this.results = [];
                                                        this.loading = true;
                                                        fetch(`{{ route('enfermedades.api.search') }}?q=${encodeURIComponent(this.query)}`)
                                                            .then(r => r.json()).then(d => { this.results = d; this.loading = false; });
                                                    }
                                                 }" @linked-seg-{{ $segmento->id }}.window="query = ''; results = []; isOpen = false" @click.away="isOpen = false">
                                                
                                                <div class="flex items-center gap-2">
                                                    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 -translate-x-4" x-transition:enter-end="opacity-100 scale-100 translate-x-0" class="relative">
                                                        <input type="text" x-model="query" 
                                                               @input.debounce.300ms="search()" 
                                                               @keydown.enter.prevent="if(results.length > 0) vincular(results[0].id, 'seg_{{ $segmento->id }}')"
                                                               placeholder="Buscar..." 
                                                               class="w-40 border-slate-200 rounded-full py-1.5 px-4 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all shadow-sm">
                                                        
                                                        <div x-show="query.length >= 2" x-cloak
                                                             class="absolute bottom-full right-0 mb-3 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 p-2 z-50">
                                                            <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                                <template x-if="loading">
                                                                    <div class="p-2 text-[10px] text-slate-400 text-center">Buscando...</div>
                                                                </template>
                                                                <template x-for="item in results" :key="item.id">
                                                                    <button type="button" @click="vincular(item.id, 'seg_{{ $segmento->id }}')" 
                                                                            class="w-full text-left p-2 hover:bg-indigo-50 rounded-xl transition-colors group">
                                                                        <div class="flex items-center gap-2">
                                                                            <div class="w-1.5 h-1.5 rounded-full" :class="item.categoria === 'mental' ? 'bg-indigo-400' : 'bg-indigo-400'"></div>
                                                                            <div class="text-[10px] font-bold text-slate-700 group-hover:text-indigo-600" x-text="item.nombre"></div>
                                                                        </div>
                                                                    </button>
                                                                </template>
                                                                <template x-if="results.length === 0 && !loading">
                                                                    <div class="p-2 text-[10px] text-slate-400 text-center italic">No hay resultados</div>
                                                                </template>
                                                            </div>
                                                            <div class="mt-2 pt-2 border-t border-slate-50">
                                                                <a href="{{ route('enfermedades.create', ['tipo' => 'mental', 'return_to' => $paciente->id, 'editing' => 1]) }}" class="block text-center text-[9px] font-black text-indigo-500 hover:text-indigo-700 uppercase tracking-widest">¿No aparece? Crear nueva</a>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <button type="button" @click="isOpen = !isOpen" 
                                                            class="w-8 h-8 rounded-full flex items-center justify-center shadow-lg transition-all transform hover:scale-110 active:scale-95"
                                                            :class="isOpen ? 'bg-slate-100 text-slate-400 rotate-45' : 'bg-indigo-600 text-white shadow-indigo-100'"
                                                            title="Añadir condición">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>



                        <!-- Action Buttons -->
                        <div class="fixed bottom-6 right-6 z-40 flex items-center gap-3 bg-white/80 backdrop-blur-md p-3 rounded-full shadow-2xl border border-slate-100/80">
                            <!-- Toggle Edit Button 
                            <button type="button" 
                                    @click="isEditing = !isEditing"
                                    class="w-14 h-14 rounded-full transition-all shadow-lg group border-2 border-white flex items-center justify-center"
                                    :class="isEditing ? 'bg-rose-100 text-rose-600' : 'bg-white text-slate-400 hover:text-indigo-600'"
                                    title="Cancelar Edición">
                                <svg class="w-7 h-7 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isEditing"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="isEditing" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            -->

                            <!-- [NUEVO] Add Section Button (Circular) -->
                            <button type="button" 
                                    x-show="isEditing"
                                    x-transition
                                    @click="$dispatch('open-modal-seccion')"
                                    class="w-14 h-14 bg-emerald-100 hover:bg-emerald-600 text-emerald-600 hover:text-white rounded-full flex items-center justify-center transition-all shadow-lg border-2 border-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                    title="Añadir Nueva Sección">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            </button>

                            <!-- Save Button -->
                            <button type="submit" 
                                    x-show="isEditing"
                                    x-transition
                                    class="w-14 h-14 bg-indigo-100 hover:bg-indigo-600 text-indigo-600 hover:text-white rounded-full flex items-center justify-center transition-all shadow-lg border-2 border-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                    title="Actualizar Expediente">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab Content: Evolucion -->
            <div x-show="tab === 'evolucion'">
                <div class="mb-6 flex justify-end">
                    <form action="{{ route('historias.evolucion.store', $paciente->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="group flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-200 transition-all">
                            <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </form>
                </div>
                
                @if($citasPaciente->isEmpty())
                     <div class="bg-white rounded-[32px] border-2 border-dashed border-slate-200 p-16 text-center shadow-sm">
                        <h3 class="text-xl font-bold text-slate-900 mb-2">No hay sesiones aún</h3>
                        <p class="text-slate-500 max-w-sm mx-auto">Las sesiones aparecerán aquí como una línea de tiempo a medida que se completen.</p>
                    </div>
                @else
                    <div class="space-y-8 relative before:absolute before:inset-y-0 before:left-4 md:before:left-1/2 before:w-1 before:bg-slate-100 before:-translate-x-1/2">
                        @foreach($citasPaciente as $index => $cita)
                            <div class="relative flex flex-col md:flex-row gap-8 md:gap-0 items-start md:items-center">
                                <!-- Marker -->
                                <div class="absolute left-4 md:left-1/2 w-8 h-8 bg-white border-4 border-indigo-600 rounded-full z-10 -translate-x-1/2 shadow-lg shadow-indigo-100 animate-pulse"></div>
                                
                                <!-- Content Card -->
                                <div class="w-full md:w-[45%] {{ $index % 2 == 0 ? 'md:mr-auto md:pr-12 text-left md:text-right' : 'md:ml-auto md:pl-12 order-last md:order-none' }}">
                                    <div class="bg-white rounded-[32px] p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
                                        <div class="flex {{ $index % 2 == 0 ? 'md:flex-row-reverse' : '' }} items-center gap-3 mb-4">
                                            <span class="text-xs font-black text-indigo-600 uppercase tracking-widest">{{ $cita->fecha?->translatedFormat('d M, Y') }}</span>
                                            <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                            <span class="text-xs font-bold text-slate-400">Sesión #{{ $citasPaciente->count() - $index }}</span>
                                        </div>
                                        <h4 class="text-lg font-bold text-slate-900 mb-3 tracking-tight">{{ $cita->motivo ?? 'Consulta General' }}</h4>
                                        <p class="text-sm text-slate-600 leading-relaxed italic mb-4">
                                            "{{ Str::limit($cita->notas_limpias, 150) ?: 'No se registraron notas específicas.' }}"
                                        </p>
                                        <div class="flex {{ $index % 2 == 0 ? 'md:flex-row-reverse' : '' }} gap-2">
                                            <a href="{{ route('citas.edit.note', $cita->id) }}" class="px-5 py-2 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-colors">
                                                Editar Nota
                                            </a>
                                            <a href="{{ route('citas.download.pdf', $cita->id) }}" target="_blank" class="px-5 py-2 bg-slate-50 text-slate-400 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-colors">
                                                Descargar PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>


        <!-- Modal de Estadísticas Detalladas -->
        <div x-show="showStats" 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showStats" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" 
                     @click="showStats = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showStats" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-[32px] sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-8 border border-slate-100">
                    
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">Resumen de Actividad</h3>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Historial de asistencia</p>
                            </div>
                        </div>
                        <button @click="showStats = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <!-- 1. Realizadas -->
                        <div class="flex items-center justify-between p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-emerald-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-700 block">Sesiones logradas</span>
                                    <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-widest">Éxitos</span>
                                </div>
                            </div>
                            <span class="text-2xl font-black text-emerald-600">{{ $stats['realizadas'] }}</span>
                        </div>

                        <!-- 2. Inasistencias -->
                        <div class="flex items-center justify-between p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-indigo-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-700 block">Inasistencias (Faltas)</span>
                                    <span class="text-[10px] text-rose-600 font-bold uppercase tracking-widest">No llegó</span>
                                </div>
                            </div>
                            <span class="text-2xl font-black text-rose-600">{{ $stats['inasistencias'] }}</span>
                        </div>

                        <div class="h-px bg-slate-100 my-2"></div>

                        <!-- 3. Canceladas Paciente Post -->
                        <div class="flex items-center justify-between p-4 bg-amber-50/30 rounded-2xl border border-amber-100 border-dashed">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-amber-500 text-white rounded-xl flex items-center justify-center shadow-lg shadow-amber-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-700 block text-sm">Cancelada por paciente (Con horario)</span>
                                    <span class="text-[10px] text-amber-600 font-bold uppercase tracking-widest">Incumplimiento</span>
                                </div>
                            </div>
                            <span class="text-xl font-black text-amber-600">{{ $stats['paciente_cancel_post'] }}</span>
                        </div>

                        <!-- 4. Canceladas Psicologo (Post) -->
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-200 border-dashed">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-400 text-white rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-700 block text-sm">Cancelada por mí (Psicólogo)</span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Razones profesionales</span>
                                </div>
                            </div>
                            <span class="text-xl font-black text-slate-500">{{ $stats['psicologo_cancel'] }}</span>
                        </div>

                        <div class="h-px bg-slate-100 my-2"></div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- 5. Canceladas Paciente Pre -->
                            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest block mb-1">Cans. sin horario</span>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-500">Por paciente</span>
                                    <span class="text-lg font-black text-slate-600">{{ $stats['paciente_cancel_pre'] }}</span>
                                </div>
                            </div>

                            <!-- 6. Rechazadas Pre -->
                            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest block mb-1">No aceptadas</span>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-500">Rechazadas</span>
                                    <span class="text-lg font-black text-slate-600">{{ $stats['rechazadas'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

        </div>

        <!-- Modal Salir sin guardar -->
        <div x-show="showUnsavedModal" 
             class="fixed inset-0 z-[120] overflow-y-auto" 
             x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div x-show="showUnsavedModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" 
                     @click="showUnsavedModal = false"></div>

                <div x-show="showUnsavedModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                     class="relative inline-block w-full max-w-sm p-8 overflow-hidden text-center transition-all transform bg-white shadow-2xl rounded-[32px] border border-slate-100 z-10">
                    
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-50 mb-6 text-[#006699]">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    
                    <h3 class="text-xl font-black text-slate-900 mb-2 tracking-tight">¿Estás seguro que deseas salir?</h3>
                    <p class="text-sm text-slate-500 mb-8 font-medium">Hay información aún no guardada. Si sales ahora, perderás los cambios realizados.</p>
                    
                    <div class="flex justify-center gap-4">
                        <button type="button" @click="showUnsavedModal = false" class="px-6 py-3 bg-slate-50 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-100 transition-colors uppercase tracking-widest w-full">
                            Cancelar
                        </button>
                        <button type="button" @click="confirmLeave()" class="px-6 py-3 bg-[#006699] hover:bg-[#005580] text-white font-bold text-sm rounded-xl transition-colors shadow-lg shadow-blue-900/20 uppercase tracking-widest w-full">
                            Salir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [NUEVO] Modal para añadir secciones personalizadas -->
    <div x-data="{ 
            isOpen: false, 
            search: '',
            descripcion: '',
            numCampos: 1,
            segmentos: [''],
            
            actualizarSegmentos() {
                let n = parseInt(this.numCampos);
                if (n < 1) n = 1;
                if (n > 10) n = 10;
                this.numCampos = n;
                
                while (this.segmentos.length < n) {
                    this.segmentos.push('');
                }
                while (this.segmentos.length > n) {
                    this.segmentos.pop();
                }
            },

            selectTemplate(t) {
                this.search = t.titulo;
                // Al seleccionar plantilla, mantenemos la configuración de segmentos actual
                // o podríamos resetearla si la plantilla tuviera segmentos guardados (futura mejora)
            }
         }" 
         @open-modal-seccion.window="isOpen = true"
         x-show="isOpen" 
         class="fixed inset-0 z-[110] overflow-y-auto" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" 
                 @click="isOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="isOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-[32px] sm:my-8 sm:align-middle sm:max-w-xl sm:w-full sm:p-8 border border-slate-100">
                
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-indigo-600 text-white rounded-2xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Nueva Sección</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Configuración del Historial</p>
                        </div>
                    </div>
                    <button @click="isOpen = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form x-ref="formSeccion" action="{{ route('historias.secciones.store', $paciente->id) }}" method="POST">
                    @csrf
                    <div class="space-y-6 max-h-[60vh] overflow-y-auto px-2 custom-scrollbar">
                        
                        {{-- Datos Principales --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Título de la Sección</label>
                            <input type="text" name="titulo" x-model="search" required
                                   class="w-full border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                   placeholder="Ej: Prueba de Inteligencia">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Descripción General (Opcional)</label>
                            <input type="text" name="descripcion_general" x-model="descripcion"
                                   class="w-full border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                   placeholder="Ej: Evaluación cognitiva detallada">
                        </div>

                        <hr class="border-slate-100">

                        {{-- Configuración de Segmentos --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">¿Cuántos campos (segmentos)?</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="numCampos--; actualizarSegmentos()" class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-100">-</button>
                                    <span class="text-sm font-black text-slate-900 w-4 text-center" x-text="numCampos"></span>
                                    <button type="button" @click="numCampos++; actualizarSegmentos()" class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-100">+</button>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(seg, index) in segmentos" :key="index">
                                    <div class="flex items-center gap-3 animate-fade-in-up">
                                        <div class="w-8 h-8 bg-indigo-50 text-indigo-500 rounded-lg flex items-center justify-center text-[10px] font-black" x-text="index + 1"></div>
                                        <input type="text" :name="'segmentos_titulos[]'" x-model="segmentos[index]" required
                                               class="flex-1 border-slate-100 rounded-xl p-3 text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                               :placeholder="'Título del campo ' + (index + 1)">
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Sugerencias de Plantillas --}}
                        @if($plantillas->count() > 0)
                            <div class="pt-2">
                                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Reutilizar títulos de mis plantillas</h4>
                                <div class="flex flex-wrap gap-2 pr-2">
                                    @foreach($plantillas as $plantilla)
                                        <button type="button" 
                                                @click="
                                                    search = '{{ addslashes($plantilla->titulo) }}';
                                                    descripcion = '{{ addslashes($plantilla->descripcion_general) }}';
                                                    segmentos = JSON.parse('{{ addslashes($plantilla->segmentos ?? '[]') }}');
                                                    numCampos = segmentos.length > 0 ? segmentos.length : 1;
                                                    if (segmentos.length === 0) segmentos = [''];
                                                "
                                                class="px-3 py-2 bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 rounded-xl text-[10px] font-bold transition-all border border-slate-100 hover:border-indigo-100 flex items-center gap-2">
                                            {{ $plantilla->titulo }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="pt-8">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-indigo-100 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Crear Sección
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('pacientes.partials.modal')
</x-app-layout>
