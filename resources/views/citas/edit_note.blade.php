<x-app-layout>
    <div class="min-h-screen bg-[#f8fafc] pb-20" x-data="clinicalNoteEditor()">
        {{-- Cabecera Contextual --}}
        <div class="bg-white border-b border-slate-100 mb-8 shadow-sm">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        @php
                            $paciente = $cita->paciente;
                            $nombreCompleto = $paciente->name ?? '';
                            $partes = explode(' ', trim($nombreCompleto));
                            $primerNombre = $partes[0] ?? '';
                            $primerApellido = $partes[1] ?? '';
                            $iniciales = strtoupper(substr($primerNombre, 0, 1) . substr($primerApellido, 0, 1));
                            
                            // Datos para el modal
                            $fechaCita = ($paciente->primera_cita ?? null) ? \Carbon\Carbon::parse($paciente->primera_cita)->format('d/m/Y') : 'No disponible';
                            $edad = ($paciente->fecha_nacimiento ?? null) ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age : 'No disponible';
                            $nacimiento = ($paciente->fecha_nacimiento ?? null) ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') : 'No disponible';
                        @endphp
                        
                        <button type="button" 
                                class="open-patient-modal w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-100 hover:scale-105 active:scale-95 transition-all cursor-pointer"
                                data-patient-type="user"
                                data-patient-name="{{ $paciente->name }}" 
                                data-patient-email="{{ $paciente->email ?? 'No disponible' }}" 
                                data-patient-phone="{{ $paciente->telefono ?? 'No disponible' }}" 
                                data-patient-created="{{ $fechaCita }}"
                                data-patient-cedula="{{ $paciente->cedula ?? 'No disponible' }}"
                                data-patient-genero="{{ $paciente->genero ?? 'No disponible' }}"
                                data-patient-nacimiento="{{ $nacimiento }}"
                                data-patient-ubicacion="{{ $paciente->ubicacion ?? 'No disponible' }}"
                                data-patient-discapacidad="{{ ($paciente->discapacidad ?? 'No') == 'Si' ? ($paciente->tipo_discapacidad ?? '') : 'Ninguna' }}"
                                data-patient-hijos="{{ ($paciente->tiene_hijos ?? 'No') == 'Si' ? ($paciente->numero_hijos ?? 0) : 'Ninguno' }}"
                                data-patient-civil="{{ $paciente->estado_civil ?? 'No disponible' }}"
                                data-patient-perfil-academico="{{ $paciente->perfil_academico ?? 'Sin definir' }}"
                                data-patient-pnf="{{ $paciente->pnf ?? 'No aplica' }}"
                                data-patient-semestre="{{ ($paciente->semestre ?? null) ? $paciente->semestre . '° Semestre' : 'No aplica' }}"
                                data-patient-edad="{{ $edad }}"
                                title="Ver perfil del paciente">
                            <span class="text-xl font-black">{{ $iniciales }}</span>
                        </button>

                        <div>
                            <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                                Nota de Sesión: {{ $cita->paciente->name }}
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase tracking-widest rounded-md border border-indigo-100">CIE-10 READY</span>
                            </h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">
                                {{ $cita->fecha?->translatedFormat('d M, Y') ?? 'S/F' }} ({{ $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('g:i A') : 'S/H' }})
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <a href="{{ route('historias.show', $cita->user_id) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold border border-slate-200 transition-all active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Volver al Historial
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-100 rounded-3xl p-5 mb-8 flex gap-4 items-start shadow-sm animate-in fade-in slide-in-from-top-4 duration-200">
                    <div class="w-10 h-10 bg-rose-500 rounded-2xl flex items-center justify-center text-white flex-shrink-0 shadow-md shadow-rose-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-rose-800 uppercase tracking-widest mb-1">Nota Clínica Obligatoria</h4>
                        <p class="text-xs font-bold text-rose-600/90 leading-relaxed">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif
            <form action="{{ route('citas.update.notas', $cita->id) }}" method="POST" id="noteForm">
                @csrf
                @method('PATCH')
                <input type="hidden" name="structured" value="1">
                
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                    
                    {{-- COLUMNA IZQUIERDA: RESUMEN --}}
                    <div class="xl:col-span-2 space-y-6">
                        <div class="bg-white rounded-[24px] p-5 shadow-sm border border-slate-100">
                            <div class="flex items-center gap-2 mb-4 text-slate-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <h4 class="text-[11px] font-black uppercase tracking-widest">Resumen Cita</h4>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Cita</p>
                                    <p class="text-sm font-bold text-slate-700">{{ $cita->fecha?->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Duración Estimada</p>
                                    <p class="text-sm font-bold text-slate-700">45 minutos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- COLUMNA CENTRAL: ANOTACIONES --}}
                    <div class="xl:col-span-7 space-y-6">
                        
                        {{-- 1. Motivo de Consulta --}}
                        <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100">
                            <div class="flex items-center gap-2 mb-4 text-slate-800">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <h4 class="text-[11px] font-black uppercase tracking-widest">1. Motivo de Consulta:</h4>
                            </div>
                            <textarea name="motivo_consulta" rows="3" 
                                      class="w-full border-slate-100 bg-slate-50/30 rounded-xl p-4 text-sm text-slate-700 focus:ring-4 focus:ring-indigo-500/5 transition-all resize-none font-medium"
                                      x-model="data.motivo_consulta"
                                      placeholder="Escribe el motivo principal reportado por el paciente..."></textarea>
                        </div>

                        {{-- 2. Observaciones Clínicas --}}
                        <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100">
                            <div class="flex items-center gap-2 mb-4 text-slate-800">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <h4 class="text-[11px] font-black uppercase tracking-widest">2. Observaciones Clínicas:</h4>
                            </div>
                            
                            {{-- Toolbar Fake --}}
                            <div class="flex items-center gap-1 mb-2 p-1.5 bg-slate-50 rounded-lg border border-slate-100 w-max">
                                <button type="button" class="p-1.5 hover:bg-white rounded hover:shadow-sm transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 12h12M6 12l6-6m-6 6l6 6"></path></svg></button>
                                <div class="w-[1px] h-4 bg-slate-200 mx-1"></div>
                                <button type="button" class="px-2 py-1 text-[10px] font-black hover:bg-white rounded hover:shadow-sm transition-all">B</button>
                                <button type="button" class="px-2 py-1 text-[10px] font-serif italic hover:bg-white rounded hover:shadow-sm transition-all">I</button>
                                <button type="button" class="px-2 py-1 text-[10px] underline hover:bg-white rounded hover:shadow-sm transition-all">U</button>
                                <div class="w-[1px] h-4 bg-slate-200 mx-1"></div>
                                <button type="button" class="p-1.5 hover:bg-white rounded hover:shadow-sm transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg></button>
                                <button type="button" class="p-1.5 hover:bg-white rounded hover:shadow-sm transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg></button>
                            </div>

                             <textarea name="observaciones" rows="8" 
                                       class="w-full @error('observaciones') border-rose-300 focus:ring-rose-500/5 @else border-slate-100 bg-white @enderror rounded-xl p-4 text-sm text-slate-700 focus:ring-4 focus:ring-indigo-500/5 transition-all resize-none font-medium leading-relaxed"
                                       x-model="data.observaciones"
                                       placeholder="El paciente llega puntual, se nota fatigado..."></textarea>
                             @error('observaciones')
                                 <p class="mt-2 text-[10px] font-black text-rose-500 uppercase tracking-wider flex items-center gap-1.5 ml-1 animate-in fade-in duration-200">
                                     <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                     {{ $message }}
                                 </p>
                             @enderror
                         </div>

                        {{-- 3. Intervenciones --}}
                        <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100">
                            <div class="flex items-center gap-2 mb-4 text-slate-800">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                <h4 class="text-[11px] font-black uppercase tracking-widest">3. Intervenciones / Resumen de Sesión:</h4>
                            </div>
                            
                            {{-- Toolbar Fake --}}
                            <div class="flex items-center gap-1 mb-2 p-1.5 bg-slate-50 rounded-lg border border-slate-100 w-max">
                                <button type="button" class="px-2 py-1 text-[10px] font-black hover:bg-white rounded hover:shadow-sm transition-all">B</button>
                                <button type="button" class="px-2 py-1 text-[10px] font-serif italic hover:bg-white rounded hover:shadow-sm transition-all">I</button>
                                <button type="button" class="px-2 py-1 text-[10px] underline hover:bg-white rounded hover:shadow-sm transition-all">U</button>
                                <div class="w-[1px] h-4 bg-slate-200 mx-1"></div>
                                <button type="button" class="p-1.5 hover:bg-white rounded hover:shadow-sm transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 12h16"></path></svg></button>
                            </div>

                            <textarea name="intervenciones" rows="8" 
                                      class="w-full border-slate-100 bg-white rounded-xl p-4 text-sm text-slate-700 focus:ring-4 focus:ring-indigo-500/5 transition-all resize-none font-medium leading-relaxed"
                                      x-model="data.intervenciones"
                                      placeholder="Se trabajaron técnicas de respiración diafragmática..."></textarea>
                        </div>

                    </div>

                    {{-- COLUMNA DERECHA: DIAGNOSTICOS Y PLAN --}}
                    <div class="xl:col-span-3 space-y-6">
                        
                        {{-- Diagnósticos CIE-10 (Diseño homologado con Expediente General) --}}
                        <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2 text-slate-800">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <h4 class="text-[11px] font-black uppercase tracking-widest">Diagnósticos Oficiales</h4>
                                </div>
                            </div>
                            
                            {{-- Etiquetas de Diagnóstico (Style: Expediente General) --}}
                            <div class="flex flex-wrap gap-2 mb-4">
                                <template x-for="(diag, index) in data.diagnosticos" :key="index">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-wider rounded-full border border-indigo-100 group/tag">
                                        <span x-text="diag.nombre"></span>
                                        <button type="button" @click="removeDiagnostico(index)" class="hover:text-rose-500 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </span>
                                </template>
                                <template x-if="data.diagnosticos.length === 0">
                                    <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic">Sin diagnósticos asociados</span>
                                </template>
                            </div>

                            {{-- Buscador (Style: Expediente General) --}}
                            <div class="relative" x-data="{ search: '', results: [], loading: false, open: false }">
                                <div class="flex items-center px-4 bg-white border border-slate-200 rounded-full focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-400 transition-all shadow-sm">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <input type="text" x-model="search" 
                                           @input.debounce.300ms="
                                                if(search.length < 2) { results = []; open = false; return; }
                                                loading = true;
                                                fetch(`{{ route('enfermedades.api.search') }}?q=${encodeURIComponent(search)}`)
                                                    .then(r => r.json()).then(d => { results = d; loading = false; open = true; });
                                           "
                                           class="w-full border-none bg-transparent text-xs font-bold text-slate-700 focus:ring-0 placeholder-slate-400 py-2.5" 
                                           placeholder="Buscar diagnóstico o condición...">
                                </div>
                                
                                <div x-show="open" @click.away="open = false" x-cloak
                                     class="absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-100 p-2">
                                    <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                        <template x-if="loading">
                                            <div class="p-3 text-[10px] text-slate-400 text-center font-bold uppercase tracking-widest animate-pulse">Buscando...</div>
                                        </template>
                                        <template x-for="res in results" :key="res.id">
                                            <button type="button" @click="addDiagnostico(res); open = false; search = ''"
                                                    class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 rounded-xl border-b border-slate-50 last:border-none transition-colors group">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-2 h-2 rounded-full bg-indigo-400 group-hover:scale-110 transition-transform"></div>
                                                    <span class="text-[10px] font-bold text-slate-700 group-hover:text-indigo-600" x-text="res.nombre"></span>
                                                    <span class="text-[9px] font-black text-slate-300 ml-auto" x-text="res.codigo"></span>
                                                </div>
                                            </button>
                                        </template>
                                        <template x-if="results.length === 0 && !loading">
                                            <div class="p-3 text-[10px] text-slate-400 text-center italic font-bold">No se encontraron resultados</div>
                                        </template>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-slate-50">
                                        <a href="{{ route('enfermedades.create', ['tipo' => 'mental', 'return_to' => $cita->user_id]) }}" 
                                           class="block text-center text-[9px] font-black text-indigo-500 hover:text-indigo-700 uppercase tracking-widest py-1">
                                            ¿No aparece? Crear nueva condición
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- [NUEVO] Avances de Sesión --}}
                        <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100">
                            <div class="flex items-center gap-2 mb-4 text-slate-800">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                <h4 class="text-[11px] font-black uppercase tracking-widest">Avances de Sesión</h4>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Estado de Evolución</label>
                                    <select name="avance_estado" x-model="data.avance_estado" 
                                            class="w-full bg-slate-50 border-slate-100 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all cursor-pointer">
                                        <option value="">Seleccionar estado...</option>
                                        <option value="estancado">Estancado / Sin cambios</option>
                                        <option value="en_progreso">En progreso / Mejora leve</option>
                                        <option value="logrado">Logrado / Mejora significativa</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Detalle del Avance</label>
                                    <textarea name="avance_detalle" rows="3" 
                                              class="w-full border-slate-100 bg-slate-50/30 rounded-xl p-3 text-[11px] text-slate-600 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all resize-none font-medium"
                                              x-model="data.avance_detalle"
                                              placeholder="Describe específicamente los avances o retrocesos observados..."></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Plan de Tratamiento --}}
                        <div class="bg-white rounded-[24px] p-5 shadow-sm border border-slate-100">
                            <div class="flex items-center gap-2 mb-4 text-slate-800">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <h4 class="text-[11px] font-black uppercase tracking-widest">Plan de Tratamiento</h4>
                            </div>
                            <textarea name="plan_tratamiento" rows="4" 
                                      class="w-full border-slate-100 bg-slate-50/20 rounded-xl p-3 text-[12px] text-slate-700 focus:ring-2 focus:ring-indigo-500/10 transition-all resize-none font-medium"
                                      x-model="data.plan_tratamiento"
                                      placeholder="Asignar tareas para la casa..."></textarea>
                        </div>

                        {{-- Próxima Cita --}}
                        <div class="bg-white rounded-[24px] p-5 shadow-sm border border-slate-100">
                            <div class="flex items-center gap-2 mb-4 text-slate-800">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <h4 class="text-[11px] font-black uppercase tracking-widest">Próxima Cita Recomendada</h4>
                            </div>
                            <input type="date" name="proxima_cita_fecha" 
                                   class="w-full border-slate-100 bg-slate-50/20 rounded-xl p-3 text-xs font-bold text-slate-700 mb-3"
                                   x-model="data.proxima_cita_fecha">
                            <textarea name="proxima_cita_razon" rows="2" 
                                      class="w-full border-slate-100 bg-slate-50/20 rounded-xl p-3 text-[11px] text-slate-600 focus:ring-2 focus:ring-indigo-500/10 transition-all resize-none font-medium"
                                      x-model="data.proxima_cita_razon"
                                      placeholder="Razón de la próxima cita..."></textarea>
                        </div>

                        {{-- BOTONES --}}
                        <div class="flex flex-col gap-3">
                            <button type="submit" @click="syncStructured()"
                                    class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[20px] text-sm font-black shadow-lg shadow-indigo-100 transition-all active:scale-95 group">
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                Guardar Nota
                            </button>
                            <a href="{{ route('historias.show', $cita->user_id) }}" 
                               class="w-full text-center px-6 py-3 bg-white hover:bg-slate-50 text-slate-500 rounded-[20px] text-xs font-bold border border-slate-100 transition-all">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Hidden input for diagnosticos array --}}
                <template x-for="(diag, index) in data.diagnosticos" :key="'hidden-'+index">
                    <input type="hidden" :name="'diagnosticos['+index+'][id]'" :value="diag.id">
                </template>
                <template x-for="(diag, index) in data.diagnosticos" :key="'hidden-cod-'+index">
                    <input type="hidden" :name="'diagnosticos['+index+'][codigo]'" :value="diag.codigo">
                </template>
                <template x-for="(diag, index) in data.diagnosticos" :key="'hidden-nom-'+index">
                    <input type="hidden" :name="'diagnosticos['+index+'][nombre]'" :value="diag.nombre">
                </template>
            </form>
        </div>

        {{-- Modal de advertencia de cambios no guardados --}}
        <div x-show="showUnsavedModal" 
             class="fixed inset-0 z-[200] overflow-y-auto" 
             x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Fondo oscuro difuminado --}}
                <div x-show="showUnsavedModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" 
                     @click="showUnsavedModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Contenedor del Modal --}}
                <div x-show="showUnsavedModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                     class="relative inline-block w-full max-w-sm p-8 overflow-hidden text-center transition-all transform bg-white shadow-2xl rounded-[32px] border border-slate-100 z-10 sm:align-middle">
                    
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-amber-50 mb-6 text-amber-500 shadow-md shadow-amber-100">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-black text-slate-900 mb-2 tracking-tight">¿Seguro que deseas salir?</h3>
                    <p class="text-xs font-bold text-slate-500 mb-8 leading-relaxed">
                        Aún no has guardado la nota de evolución de esta cita, por lo tanto, la cita no se guardará como realizada y perderás la información ingresada.
                    </p>
                    
                    <div class="flex justify-center gap-4">
                        <button type="button" @click="showUnsavedModal = false" class="flex-1 py-4 px-6 bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                            Cancelar
                        </button>
                        <button type="button" @click="confirmLeave()" class="flex-1 py-4 px-6 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-amber-100 transition-all">
                            Salir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function clinicalNoteEditor() {
            let initialData = {
                motivo_consulta: '',
                observaciones: '',
                intervenciones: '',
                diagnosticos: [],
                avance_estado: '',
                avance_detalle: '',
                plan_tratamiento: '',
                proxima_cita_fecha: '',
                proxima_cita_razon: ''
            };

            const rawNotas = @json($cita->notas);
            try {
                const parsed = JSON.parse(rawNotas);
                if (typeof parsed === 'object' && parsed !== null) {
                    initialData = { ...initialData, ...parsed };
                } else {
                    initialData.observaciones = rawNotas; // Fallback para notas viejas
                }
            } catch(e) {
                initialData.observaciones = rawNotas;
            }

            const initialSnapshot = JSON.stringify(initialData);
            const isConfirmed = @json($cita->estado === 'confirmada');

            return {
                data: initialData,
                hasUnsavedChanges: isConfirmed,
                showUnsavedModal: false,
                pendingUrl: '',
                isSubmitting: false,

                init() {
                    // Detectar cambios profundos en los datos del formulario
                    this.$watch('data', (value) => {
                        this.hasUnsavedChanges = isConfirmed || JSON.stringify(value) !== initialSnapshot;
                    });

                    // Advertencia de navegador nativa al recargar o cerrar pestaña
                    window.addEventListener('beforeunload', (e) => {
                        if (this.hasUnsavedChanges && !this.isSubmitting) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });

                    // Capturar clics en enlaces dentro de la aplicación para advertencia personalizada
                    document.addEventListener('click', (e) => {
                        let link = e.target.closest('a');
                        if (link && link.href && !link.href.includes('#') && link.target !== '_blank' && !link.hasAttribute('download')) {
                            if (this.hasUnsavedChanges && !this.isSubmitting) {
                                e.preventDefault();
                                this.pendingUrl = link.href;
                                this.showUnsavedModal = true;
                            }
                        }
                    });
                },

                confirmLeave() {
                    this.hasUnsavedChanges = false;
                    if (this.pendingUrl) {
                        window.location.href = this.pendingUrl;
                    }
                },

                addDiagnostico(item) {
                    if (!this.data.diagnosticos.some(d => d.id === item.id)) {
                        this.data.diagnosticos.push(item);
                        // Asegurar el disparo de cambios en el snapshot
                        this.hasUnsavedChanges = JSON.stringify(this.data) !== initialSnapshot;
                    }
                },

                removeDiagnostico(index) {
                    this.data.diagnosticos.splice(index, 1);
                    // Asegurar el disparo de cambios en el snapshot
                    this.hasUnsavedChanges = JSON.stringify(this.data) !== initialSnapshot;
                },

                syncStructured() {
                    this.isSubmitting = true;
                    this.hasUnsavedChanges = false;
                }
            };
        }
    </script>
    @include('pacientes.partials.modal')
</x-app-layout>
