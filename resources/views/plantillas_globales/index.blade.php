<x-app-layout>
    <div class="py-12 bg-slate-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Esquema General del Expediente</h2>
                    <p class="text-sm font-bold text-slate-500 dark:text-gray-400 mt-2 uppercase tracking-widest">Define la estructura base de toda historia clínica.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('plantillas.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-slate-100 dark:bg-gray-700 hover:bg-slate-200 dark:hover:bg-gray-600 text-slate-600 dark:text-gray-300 rounded-2xl text-sm font-bold transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                        Volver a Plantillas
                    </a>
                </div>
            </div>

            {{-- Alerta informativa --}}
            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-2xl p-5 mb-8 flex flex-col sm:flex-row sm:items-start gap-4">
                <div class="flex items-center justify-between sm:justify-start gap-3 w-full sm:w-auto">
                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="sm:hidden">
                        @if($plantilla->status == 1)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            ACTIVA
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            INACTIVA / PREDETERMINADA
                        </span>
                        @endif
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-indigo-800 dark:text-indigo-300">¿Qué es el esquema general del expediente?</p>
                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 leading-relaxed">Este te permite estandarizar las secciones y campos fundamentales <strong>para todos los pacientes.</strong> Edita la estructura a continuación y luego asegúrate de guardar los cambios para activarla.</p>
                </div>
                <div class="hidden sm:flex shrink-0 items-center pt-1">
                    @if($plantilla->status == 1)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        ACTIVA
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        INACTIVA / PREDETERMINADA
                    </span>
                    @endif
                </div>
            </div>

            @php
                $seccionesData = $plantilla->secciones_decoded ?? [];
                // Preparar datos para Alpine.js
                $seccionesAlpine = [];
                foreach ($seccionesData as $seccion) {
                    $segs = $seccion['segmentos'] ?? [''];
                    if (empty($segs)) $segs = [''];
                    $seccionesAlpine[] = [
                        'titulo' => $seccion['titulo'] ?? '',
                        'descripcion_general' => $seccion['descripcion_general'] ?? '',
                        'numCampos' => count($segs),
                        'segmentos' => $segs,
                    ];
                }
                if (empty($seccionesAlpine)) {
                    $seccionesAlpine = [['titulo' => '', 'descripcion_general' => '', 'numCampos' => 1, 'segmentos' => ['']]];
                }
            @endphp

        <div class="bg-white dark:bg-gray-800 rounded-[32px] p-8 shadow-sm border border-slate-100 dark:border-gray-700 pb-24" x-data="{
                secciones: @js($seccionesAlpine),
                isEditing: {{ $plantilla->status == 2 ? 'true' : 'false' }},
                showModal: false,
                search: '',

                agregarSeccion() {
                    if(!this.isEditing) return;
                    this.secciones.push({
                        titulo: '',
                        descripcion_general: '',
                        numCampos: 1,
                        segmentos: ['']
                    });
                },

                eliminarSeccion(index) {
                    if(!this.isEditing) return;
                    if (this.secciones.length > 1) {
                        this.secciones.splice(index, 1);
                    }
                },

                moverSeccion(index, direccion) {
                    if(!this.isEditing) return;
                    if (direccion === -1 && index > 0) {
                        let temp = this.secciones[index];
                        this.secciones[index] = this.secciones[index - 1];
                        this.secciones[index - 1] = temp;
                    } else if (direccion === 1 && index < this.secciones.length - 1) {
                        let temp = this.secciones[index];
                        this.secciones[index] = this.secciones[index + 1];
                        this.secciones[index + 1] = temp;
                    }
                },

                showToast: false,
                toastMessage: '',
                toastSecIndex: -1,
                triggerToast(secIndex) {
                    this.toastSecIndex = secIndex;
                    this.showToast = true;
                    setTimeout(() => { this.showToast = false; }, 3000);
                },

                actualizarSegmentos(seccion) {
                    if(!this.isEditing) return;
                    let n = parseInt(seccion.numCampos);
                    if (n < 1) n = 1;
                    if (n > 4) {
                        n = 4;
                    }
                    seccion.numCampos = n;

                    while (seccion.segmentos.length < n) {
                        seccion.segmentos.push('');
                    }
                    if (seccion.segmentos.length > n) {
                        seccion.segmentos = seccion.segmentos.slice(0, n);
                    }
                },
                
                guardar() {
                    if('{{ $plantilla->status }}' == '1') {
                        this.showModal = true;
                    } else {
                        document.getElementById('formPlantilla').submit();
                    }
                },
                
                submitForm(aplicarTodos) {
                    document.getElementById('aplicar_a_todos_input').value = aplicarTodos ? '1' : '0';
                    document.getElementById('formPlantilla').submit();
                }
            }">
                <form id="formPlantilla" action="{{ route('plantillas-globales.update') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        {{-- Título y descripción de la plantilla --}}
                        <div>
                            <label class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Nombre de la Plantilla <span class="text-rose-500">*</span></label>
                            <input type="text" name="titulo" required x-bind:readonly="!isEditing" class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-gray-900 dark:text-white font-bold" :class="!isEditing ? 'border-transparent bg-transparent pl-0 focus:ring-0' : ''" placeholder="Ej: Evaluación Psicológica Integral" value="{{ old('titulo', $plantilla->titulo) }}">
                            @error('titulo') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Descripción General (Opcional)</label>
                            <input type="text" name="descripcion" x-bind:readonly="!isEditing" class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-gray-900 dark:text-white" :class="!isEditing ? 'border-transparent bg-transparent pl-0 focus:ring-0' : ''" placeholder="Ej: Plantilla estándar para evaluación inicial" value="{{ old('descripcion', $plantilla->descripcion) }}">
                        </div>

                        <hr class="border-slate-100 dark:border-gray-700 my-8">

                        {{-- Título de secciones --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-4">
                            <div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Secciones del Historial</h3>
                                <p class="text-xs text-slate-400 dark:text-gray-500 font-bold uppercase tracking-widest">Cada sección contiene campos (segmentos) editables</p>
                            </div>
                            <div class="relative w-full sm:w-64 shrink-0">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input type="text" x-model="search" class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-xl pl-9 pr-4 py-2 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-xs text-gray-900 dark:text-white" placeholder="Buscar sección...">
                            </div>
                        </div>

                        {{-- Lista de secciones --}}
                        <div class="space-y-6">
                            <template x-for="(seccion, secIndex) in secciones" :key="secIndex">
                                <div x-show="search === '' || seccion.titulo.toLowerCase().includes(search.toLowerCase())" class="bg-slate-50 dark:bg-gray-700/50 rounded-2xl p-6 border border-slate-200 dark:border-gray-600 relative">
                                    {{-- Header de la sección --}}
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400 rounded-xl flex items-center justify-center font-black text-xs" x-text="secIndex + 1"></div>
                                            <span class="text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Sección</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="moverSeccion(secIndex, -1)" x-show="isEditing && secIndex > 0" class="p-2 text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-xl transition-all" title="Mover arriba">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                            </button>
                                            <button type="button" @click="moverSeccion(secIndex, 1)" x-show="isEditing && secIndex < secciones.length - 1" class="p-2 text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-xl transition-all" title="Mover abajo">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>
                                            <button type="button" @click="eliminarSeccion(secIndex)" x-show="isEditing && secciones.length > 1" class="p-2 text-rose-400 dark:text-rose-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-xl transition-all" title="Eliminar sección">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Título de la sección --}}
                                    <div class="mb-4">
                                        <label class="block text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Título de la Sección <span class="text-rose-500">*</span></label>
                                        <input type="text" :name="'secciones_estructura['+secIndex+'][titulo]'" x-model="seccion.titulo" required x-bind:readonly="!isEditing" class="w-full bg-white dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-sm font-bold text-gray-900 dark:text-white" :class="!isEditing ? 'border-transparent bg-transparent pl-0 focus:ring-0' : ''"
                                               placeholder="Ej: Antecedentes Personales">
                                    </div>

                                    {{-- Descripción de la sección --}}
                                    <div class="mb-4">
                                        <label class="block text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Descripción (Opcional)</label>
                                        <input type="text" :name="'secciones_estructura['+secIndex+'][descripcion_general]'" x-model="seccion.descripcion_general" x-bind:readonly="!isEditing" class="w-full bg-white dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-sm text-gray-900 dark:text-white" :class="!isEditing ? 'border-transparent bg-transparent pl-0 focus:ring-0' : ''"
                                               placeholder="Ej: Historial médico y psicológico del paciente">
                                    </div>

                                    <hr class="border-slate-200 dark:border-gray-600 my-4">

                                    {{-- Segmentos --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <label class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Campos (Segmentos)</label>
                                            <div class="flex items-center gap-2">
                                                <div x-show="showToast && toastSecIndex === secIndex"
                                                     x-transition:enter="transition ease-out duration-300"
                                                     x-transition:enter-start="opacity-0 transform translate-x-4"
                                                     x-transition:enter-end="opacity-100 transform translate-x-0"
                                                     x-transition:leave="transition ease-in duration-300"
                                                     x-transition:leave-start="opacity-100 transform translate-x-0"
                                                     x-transition:leave-end="opacity-0 transform translate-x-4"
                                                     style="display: none;" 
                                                     class="bg-amber-50 dark:bg-amber-900/80 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-200 px-3 py-1 rounded-xl flex items-center gap-2">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                    <span class="text-[10px] font-bold">Máx. 4 campos</span>
                                                </div>
                                                <div class="flex items-center gap-2 bg-white dark:bg-gray-700 p-1 rounded-xl border border-slate-200 dark:border-gray-600">
                                                    <button type="button" x-show="isEditing" @click="if(seccion.numCampos > 1) { seccion.numCampos--; actualizarSegmentos(seccion); }" class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-gray-800 text-slate-600 dark:text-gray-300 shadow-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors text-xs font-bold">-</button>
                                                    <span class="w-6 text-center text-xs font-black text-slate-700 dark:text-gray-200" x-text="seccion.numCampos"></span>
                                                    <button type="button" x-show="isEditing" @click="if(seccion.numCampos < 4) { seccion.numCampos++; actualizarSegmentos(seccion); } else { triggerToast(secIndex); }" class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-gray-800 text-slate-600 dark:text-gray-300 shadow-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors text-xs font-bold">+</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <template x-for="(seg, segIndex) in seccion.segmentos" :key="segIndex">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-[10px] shrink-0" x-text="segIndex + 1"></div>
                                                    <input type="text" x-model="seccion.segmentos[segIndex]" :name="'secciones_estructura['+secIndex+'][segmentos]['+segIndex+']'" required x-bind:readonly="!isEditing" class="w-full bg-white dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-xs text-gray-900 dark:text-white" :class="!isEditing ? 'border-transparent bg-transparent pl-0 focus:ring-0' : ''"
                                                           placeholder="Título del campo">
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                                                {{-- Modal Confirmación --}}
                        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm">
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl max-w-md w-full mx-4 border border-slate-100 dark:border-gray-700" @click.away="showModal = false">
                                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">¿Estás seguro de guardar estos cambios?</h3>
                                <p class="text-sm text-slate-600 dark:text-gray-400 mb-6">
                                    Al hacerlo, los cambios podrán aplicarse en todas las historias clínicas ya existentes. Sin embargo, si no lo deseas, los cambios solo se aplicarán para los siguientes expedientes clínicos futuros.
                                </p>
                                <input type="hidden" name="aplicar_a_todos" id="aplicar_a_todos_input" value="0">
                                
                                <div class="flex flex-col gap-3">
                                    <button type="button" @click="submitForm(true)" class="w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-colors text-sm">
                                        Sí, aplicar a todos
                                    </button>
                                    <button type="button" @click="submitForm(false)" class="w-full px-4 py-3 bg-slate-100 dark:bg-gray-700 hover:bg-slate-200 dark:hover:bg-gray-600 text-slate-700 dark:text-gray-200 font-bold rounded-xl transition-colors text-sm">
                                        Solo aplicar a expedientes futuros
                                    </button>
                                    <button type="button" @click="showModal = false" class="mt-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-gray-300 uppercase tracking-widest text-center w-full">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    {{-- Floating Buttons --}}
                    <div class="fixed bottom-8 right-8 z-30 flex items-center gap-3 bg-white dark:bg-gray-800 p-2 rounded-full shadow-2xl border border-slate-100 dark:border-gray-700">
                        <template x-if="isEditing">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="agregarSeccion()" title="Agregar Sección" class="flex items-center gap-2 px-4 h-12 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm text-sm font-bold tracking-wide">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    
                                </button>
                                <div class="w-px h-8 bg-slate-200 dark:bg-gray-700 mx-1"></div>
                                <button type="button" @click="isEditing = false; if('{{ $plantilla->status }}' == '2') isEditing = true;" title="Cancelar" class="w-12 h-12 flex items-center justify-center rounded-full bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm" x-show="'{{ $plantilla->status }}' == '1'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <button type="button" @click="guardar()" title="{{ $plantilla->status == 1 ? 'Guardar' : 'Guardar y Activar' }}" class="w-12 h-12 flex items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="!isEditing">
                            <button type="button" @click="isEditing = true" title="Editar" class="w-12 h-12 flex items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                        </template>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
