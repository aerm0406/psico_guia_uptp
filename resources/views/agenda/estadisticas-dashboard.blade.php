<x-app-layout>
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-3xl border-l-8 border-sky-700">
                <div class="p-8 text-gray-900 dark:text-gray-100">

                    {{-- Header --}}
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('agenda.index', ['psicologo_id' => $psicologoId]) }}" class="w-10 h-10 bg-slate-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center text-slate-400 hover:text-sky-700 hover:bg-sky-50 dark:hover:bg-sky-900/30 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </a>
                            <div>
                                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">Panel Estadístico</h3>
                                <p class="text-sm text-slate-400 dark:text-gray-500 font-medium mt-1">Análisis interactivo de citas y pacientes</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                            {{-- Filtro de Período --}}
                            <div x-data="{ open: false, selected: 'mensual', labels: { semanal: 'Últimos 7 días', mensual: 'Últimos 30 días', semestral: 'Últimos 6 meses', anual: 'Último año', personalizado: 'Personalizado' } }" class="relative z-30 w-full sm:w-auto">
                                <button @click="open = !open" @click.away="open = false" class="flex items-center justify-between sm:justify-start gap-2 w-full sm:w-auto bg-sky-600 hover:bg-sky-700 text-white px-5 h-12 rounded-2xl shadow-sm transition-all font-bold text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span x-text="labels[selected]">Últimos 30 días</span>
                                    </div>
                                    <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="open" x-transition style="display: none;" class="absolute left-0 right-0 sm:right-auto mt-2 w-full sm:w-56 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-slate-100 dark:border-gray-700 overflow-hidden z-50">
                                    <div class="p-2 space-y-1">
                                        <template x-for="key in ['semanal','mensual','semestral','anual']" :key="key">
                                            <button @click="selected = key; open = false; window.dashboardApp.cambiarFiltro(key);" class="flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-left w-full" :class="selected === key ? 'bg-sky-50 dark:bg-sky-900/20' : ''">
                                                <div class="w-2 h-2 rounded-full" :class="selected === key ? 'bg-sky-600' : 'bg-slate-200 dark:bg-gray-600'"></div>
                                                <span class="text-sm font-bold text-slate-700 dark:text-white" x-text="labels[key]"></span>
                                            </button>
                                        </template>
                                        <div class="border-t border-slate-100 dark:border-gray-700 my-1"></div>
                                        <button @click="selected = 'personalizado'; open = false; document.getElementById('customDateModal').classList.remove('hidden'); document.getElementById('customDateModal').classList.add('flex');" class="flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-left w-full">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            <span class="text-sm font-bold text-slate-700 dark:text-white">Rango Personalizado</span>
                                        </button>
                                    </div>
                                </div>         
                            </div> 

                            {{-- Exportar Dropdown --}}
                            <div x-data="{ openExport: false }" class="relative z-20 w-full sm:w-auto">
                                <button @click="openExport = !openExport" @click.away="openExport = false" class="flex items-center justify-between sm:justify-start gap-2 w-full sm:w-auto bg-slate-50 dark:bg-gray-700 border border-slate-200 dark:border-gray-600 hover:bg-slate-100 dark:hover:bg-gray-600 text-slate-700 dark:text-white px-5 h-12 rounded-2xl shadow-sm transition-all font-bold text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span>Exportar</span>
                                    </div>
                                    <svg class="w-3 h-3 opacity-60 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="openExport" x-transition style="display: none;" class="absolute left-0 right-0 sm:left-auto sm:right-0 mt-2 w-full sm:w-72 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-slate-100 dark:border-gray-700 overflow-hidden z-50">
                                    <div class="p-2 space-y-1">
                                        <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'completo');" class="flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-left w-full">
                                            <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-500 flex items-center justify-center flex-shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg></div>
                                            <div class="flex flex-col"><span class="text-sm font-bold text-slate-700 dark:text-white leading-tight">Descargar PDF (Completo)</span><span class="text-[10px] font-medium text-slate-400 dark:text-gray-400">Todos los datos del período</span></div>
                                        </button>
                                        <button @click="openExport = false; window.dashboardApp.exportar('word', 'completo');" class="flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-left w-full">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center flex-shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                                            <div class="flex flex-col"><span class="text-sm font-bold text-slate-700 dark:text-white leading-tight">Descargar Word (Completo)</span><span class="text-[10px] font-medium text-slate-400 dark:text-gray-400">Todos los datos del período</span></div>
                                        </button>
                                        <button @click="openExport = false; window.dashboardApp.exportar('excel', 'completo');" class="flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-left w-full">
                                            <div class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center flex-shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                                            <div class="flex flex-col"><span class="text-sm font-bold text-slate-700 dark:text-white leading-tight">Descargar Excel</span><span class="text-[10px] font-medium text-slate-400 dark:text-gray-400">Tabla de datos en Excel</span></div>
                                        </button>
                                        <div class="border-t border-slate-100 dark:border-gray-700 my-1"></div>
                                        <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'citas_estados');" class="flex items-center gap-3 p-2 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-left w-full">
                                            <div class="w-6 h-6 rounded-lg bg-sky-50 dark:bg-sky-900/30 text-sky-500 flex items-center justify-center flex-shrink-0"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                                            <span class="text-sm font-bold text-slate-600 dark:text-gray-300">Citas y Estados</span>
                                        </button>
                                        <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'demografico');" class="flex items-center gap-3 p-2 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-left w-full">
                                            <div class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center flex-shrink-0"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                                            <span class="text-sm font-bold text-slate-600 dark:text-gray-300">Demográfico</span>
                                        </button>
                                        <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'operativo');" class="flex items-center gap-3 p-2 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-left w-full">
                                            <div class="w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-500 flex items-center justify-center flex-shrink-0"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg></div>
                                            <span class="text-sm font-bold text-slate-600 dark:text-gray-300">Métricas Operativas</span>
                                        </button>
                                        <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'clinico');" class="flex items-center gap-3 p-2 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-left w-full">
                                            <div class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center flex-shrink-0"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg></div>
                                            <span class="text-sm font-bold text-slate-600 dark:text-gray-300">Clínico y Seguimiento</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Indicador de Período --}}
                    <div id="periodoIndicador" class="mb-8 bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-sky-900/20 dark:to-indigo-900/20 border border-sky-100 dark:border-sky-800/50 rounded-2xl px-6 py-4 flex items-center gap-4">
                        <div class="w-10 h-10 bg-white dark:bg-gray-700 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0">
                            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-sky-600 dark:text-sky-400 uppercase tracking-[0.15em]" id="periodoLabel">Mostrando datos del período (Mensual)</p>
                            <p class="text-sm font-bold text-slate-700 dark:text-gray-300" id="periodoTexto">
                                {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="ml-auto" id="loadingSpinner" style="display: none;">
                            <div class="w-6 h-6 border-2 border-sky-200 border-t-sky-600 rounded-full animate-spin"></div>
                        </div>
                              </div>

                    {{-- Filtros Adicionales --}}
                    <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Estado de Cita</label>
                            <select id="filterEstado" onchange="window.dashboardApp.recargar()" class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-gray-900 dark:text-white">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="realizada">Realizada</option>
                                <option value="cancelada">Cancelada</option>
                                <option value="no_asistio">No Asistió</option>
                                <option value="rechazada">Rechazada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Avance de Sesión</label>
                            <select id="filterAvance" onchange="window.dashboardApp.recargar()" class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-gray-900 dark:text-white">
                                <option value="">Todos</option>
                                @foreach($avances as $avance)
                                    <option value="{{ $avance->id }}">{{ $avance->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Estado de Ánimo</label>
                            <select id="filterEstadoAnimo" onchange="window.dashboardApp.recargar()" class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-gray-900 dark:text-white">
                                <option value="">Todos</option>
                                @foreach($estados_animo as $animo)
                                    <option value="{{ $animo->id }}">{{ $animo->valor }} - {{ $animo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Prioridad</label>
                            <select id="filterPrioridad" onchange="window.dashboardApp.recargar()" class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-gray-900 dark:text-white">
                                <option value="">Todas</option>
                                @foreach($prioridades as $prioridad)
                                    <option value="{{ $prioridad->nombre }}">{{ $prioridad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Rol Institucional</label>
                            <select id="filterPerfilAcademico" onchange="window.dashboardApp.recargar()" class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-gray-900 dark:text-white">
                                <option value="">Todos</option>
                                <option value="Estudiante">Estudiante</option>
                                <option value="Profesor">Profesor</option>
                                <option value="Obrero">Obrero</option>
                                <option value="Administrativo">Administrativo</option>
                                <option value="Pre-escolar">Pre-escolar</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">PNF / Carrera</label>
                            <select id="filterPnf" onchange="window.dashboardApp.recargar()" class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-2.5 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-gray-900 dark:text-white">
                                <option value="">Todos</option>
                                <option value="Administracion">Administración</option>
                                <option value="Mecanica">Mecánica</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Electricidad">Electricidad</option>
                                <option value="Veterinaria">Veterinaria</option>
                                <option value="Informatica">Informática</option>
                                <option value="PDA">PDA</option>
                                <option value="Distribucion_Logistica">Distribución y Logística</option>
                                <option value="Agroalimentacion">Agroalimentación</option>
                                <option value="Seguridad_Alimentaria_Nutricional">Seguridad alimentaria y Cultura Nutricional</option>
                            </select>
                        </div>
                    </div>

                    {{-- KPI Cards --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8" id="kpiCards">
                        <div class="bg-white dark:bg-gray-700/50 border border-slate-100 dark:border-gray-700 rounded-2xl p-5 text-center transition-all hover:shadow-md hover:-translate-y-0.5">
                            <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.15em] mb-2">Total Citas</p>
                            <p class="text-2xl font-black text-slate-800 dark:text-white" id="kpiTotalCitas">—</p>
                        </div>
                        <div class="bg-white dark:bg-gray-700/50 border border-slate-100 dark:border-gray-700 rounded-2xl p-5 text-center transition-all hover:shadow-md hover:-translate-y-0.5">
                            <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.15em] mb-2">Pacientes</p>
                            <p class="text-2xl font-black text-slate-800 dark:text-white" id="kpiPacientes">—</p>
                        </div>
                        <div class="bg-white dark:bg-gray-700/50 border border-slate-100 dark:border-gray-700 rounded-2xl p-5 text-center transition-all hover:shadow-md hover:-translate-y-0.5">
                            <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.15em] mb-2">Asistencia</p>
                            <p class="text-2xl font-black text-emerald-600" id="kpiAsistencia">—</p>
                        </div>
                        <div class="bg-white dark:bg-gray-700/50 border border-slate-100 dark:border-gray-700 rounded-2xl p-5 text-center transition-all hover:shadow-md hover:-translate-y-0.5">
                            <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.15em] mb-2">Hora Pico</p>
                            <p class="text-lg font-black text-amber-600" id="kpiHoraPico">—</p>
                        </div>
                        <div class="bg-white dark:bg-gray-700/50 border border-slate-100 dark:border-gray-700 rounded-2xl p-5 text-center transition-all hover:shadow-md hover:-translate-y-0.5">
                            <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.15em] mb-2">Citas/Semana</p>
                            <p class="text-2xl font-black text-sky-600" id="kpiSemanal">—</p>
                        </div>
                        <div class="bg-white dark:bg-gray-700/50 border border-slate-100 dark:border-gray-700 rounded-2xl p-5 text-center transition-all hover:shadow-md hover:-translate-y-0.5">
                            <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.15em] mb-2">vs. Período Ant.</p>
                            <p class="text-2xl font-black" id="kpiComparativa">—</p>
                        </div>
                    </div>

                    {{-- Charts Grid --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        {{-- Flujo por Semana (Líneas) --}}
                        <div class="bg-white dark:bg-gray-700/30 border border-slate-100 dark:border-gray-700 rounded-2xl p-6">
                            <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4">Tendencia Semanal de Pacientes</h4>
                            <div class="relative" style="height: 280px;">
                                <canvas id="chartFlujoSemanal"></canvas>
                            </div>
                        </div>

                        {{-- Distribución por Horas (Barras Verticales) --}}
                        <div class="bg-white dark:bg-gray-700/30 border border-slate-100 dark:border-gray-700 rounded-2xl p-6">
                            <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4">Distribución por Horas de Atención</h4>
                            <div class="relative" style="height: 280px;">
                                <canvas id="chartHoras"></canvas>
                            </div>
                        </div>

                        {{-- Distribución por Edad (Barras Horizontales) --}}
                        <div class="bg-white dark:bg-gray-700/30 border border-slate-100 dark:border-gray-700 rounded-2xl p-6">
                            <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4">Distribución de Edades</h4>
                            <div class="relative" style="height: 280px;">
                                <canvas id="chartEdades"></canvas>
                            </div>
                        </div>

                        {{-- Género y Avances --}}
                        <div class="bg-white dark:bg-gray-700/30 border border-slate-100 dark:border-gray-700 rounded-2xl p-6">
                            <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4">Género y Avances Clínicos</h4>
                            <div class="grid grid-cols-2 gap-4 h-full">
                                <div class="flex items-center justify-center" style="height: 250px;">
                                    <canvas id="chartGenero"></canvas>
                                </div>
                                <div class="flex items-center justify-center" style="height: 250px;">
                                    <canvas id="chartAvances"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Prioridad y Estados de Animo --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white dark:bg-gray-700/30 border border-slate-100 dark:border-gray-700 rounded-2xl p-6">
                            <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4">Pacientes Atendidos por Prioridad</h4>
                            <div class="relative" style="height: 250px;">
                                <canvas id="chartPrioridades"></canvas>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-gray-700/30 border border-slate-100 dark:border-gray-700 rounded-2xl p-6">
                            <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4">Pacientes Atendidos por Estado de Ánimo</h4>
                            <div class="relative" style="height: 250px;">
                                <canvas id="chartEstadosAnimo"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- Resumen Tabla Detallada --}}
                    <div class="bg-white dark:bg-gray-700/30 border border-slate-100 dark:border-gray-700 rounded-2xl p-6">
                        <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4">Métricas Detalladas</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left" id="tablaMetricas">
                                <thead>
                                    <tr class="border-b border-slate-100 dark:border-gray-700">
                                        <th class="pb-3 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Métrica</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest text-right">Valor</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 dark:divide-gray-700" id="tablaMetricasBody">
                                    <tr><td colspan="2" class="py-8 text-center text-slate-300 dark:text-gray-600 font-bold text-xs">Cargando datos...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal Fecha Personalizada --}}
    <div id="customDateModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm items-center justify-center z-[100]">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-lg font-black text-slate-800 dark:text-white">Rango Personalizado</h4>
                    <button onclick="document.getElementById('customDateModal').classList.add('hidden'); document.getElementById('customDateModal').classList.remove('flex');" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest" for="customStartDate">Fecha Inicio</label>
                        <input type="date" id="customStartDate" class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-3 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest" for="customEndDate">Fecha Fin</label>
                        <input type="date" id="customEndDate" class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-3 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-gray-900 dark:text-white">
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button onclick="document.getElementById('customStartDate').value=''; document.getElementById('customEndDate').value=''; window.dashboardApp.cambiarFiltro('mensual'); document.getElementById('customDateModal').classList.add('hidden'); document.getElementById('customDateModal').classList.remove('flex');" class="flex-1 px-4 py-3 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-gray-300 rounded-2xl font-bold text-sm hover:bg-slate-200 dark:hover:bg-gray-600 transition-all">Limpiar</button>
                    <button onclick="window.dashboardApp.aplicarPersonalizado(); document.getElementById('customDateModal').classList.add('hidden'); document.getElementById('customDateModal').classList.remove('flex');" class="flex-1 px-4 py-3 bg-sky-600 hover:bg-sky-700 text-white rounded-2xl font-bold text-sm transition-all shadow-sm">Aplicar Filtro</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <script>
        window.dashboardApp = (function() {
            const psicologoId = {{ $psicologoId }};
            let currentStartDate = '{{ $fechaInicio }}';
            let currentEndDate = '{{ $fechaFin }}';
            let currentSelected = 'mensual';
            let charts = {};

            const COLORS = {
                sky:     { bg: 'rgba(14,165,233,0.15)', border: '#0ea5e9' },
                emerald: { bg: 'rgba(16,185,129,0.15)', border: '#10b981' },
                amber:   { bg: 'rgba(245,158,11,0.15)', border: '#f59e0b' },
                rose:    { bg: 'rgba(244,63,94,0.15)',   border: '#f43f5e' },
                violet:  { bg: 'rgba(139,92,246,0.15)',  border: '#8b5cf6' },
                indigo:  { bg: 'rgba(99,102,241,0.15)',  border: '#6366f1' },
                cyan:    { bg: 'rgba(6,182,212,0.15)',   border: '#06b6d4' },
            };

            const PALETTE = ['#0ea5e9','#10b981','#f59e0b','#f43f5e','#8b5cf6','#6366f1','#06b6d4','#ec4899'];

            function getFilterParams() {
                const estado = document.getElementById('filterEstado')?.value || '';
                const avance = document.getElementById('filterAvance')?.value || '';
                const animo = document.getElementById('filterEstadoAnimo')?.value || '';
                const prioridad = document.getElementById('filterPrioridad')?.value || '';
                const perfil = document.getElementById('filterPerfilAcademico')?.value || '';
                const pnf = document.getElementById('filterPnf')?.value || '';
                let params = '';
                if (estado) params += `&estado=${estado}`;
                if (avance) params += `&avance_id=${avance}`;
                if (animo) params += `&estado_animo_id=${animo}`;
                if (prioridad) params += `&prioridad=${prioridad}`;
                if (perfil) params += `&perfil_academico=${perfil}`;
                if (pnf) params += `&pnf=${pnf}`;
                return params;
            }

            function formatDateDisplay(dateStr) {
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }

            function calcularFechas(tipo) {
                const hoy = new Date();
                let inicio;
                switch(tipo) {
                    case 'semanal':   inicio = new Date(hoy); inicio.setDate(hoy.getDate() - 7); break;
                    case 'mensual':   inicio = new Date(hoy); inicio.setDate(hoy.getDate() - 30); break;
                    case 'semestral': inicio = new Date(hoy); inicio.setMonth(hoy.getMonth() - 6); break;
                    case 'anual':     inicio = new Date(hoy); inicio.setFullYear(hoy.getFullYear() - 1); break;
                    default:          inicio = new Date(hoy); inicio.setDate(hoy.getDate() - 30);
                }
                return {
                    start: inicio.toISOString().split('T')[0],
                    end: hoy.toISOString().split('T')[0]
                };
            }

            async function fetchData(startDate, endDate) {
                document.getElementById('loadingSpinner').style.display = 'block';
                try {
                    const url = `{{ route('agenda.estadisticas') }}?format=json&psicologo_id=${psicologoId}&start_date=${startDate}&end_date=${endDate}${getFilterParams()}`;
                    const resp = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
                    if (!resp.ok) throw new Error('Error al obtener datos');
                    return await resp.json();
                } finally {
                    document.getElementById('loadingSpinner').style.display = 'none';
                }
            }

            function updateKPIs(resumen) {
                document.getElementById('kpiTotalCitas').textContent = resumen.total_citas;
                document.getElementById('kpiPacientes').textContent = resumen.total_pacientes;
                document.getElementById('kpiAsistencia').textContent = resumen.tasa_asistencia + '%';
                document.getElementById('kpiHoraPico').textContent = resumen.hora_pico || 'N/A';
                document.getElementById('kpiSemanal').textContent = resumen.promedio_semanal;

                const comp = document.getElementById('kpiComparativa');
                const val = resumen.comparativa_pacientes;
                comp.textContent = (val > 0 ? '+' : '') + val + '%';
                comp.className = 'text-2xl font-black ' + (val > 0 ? 'text-emerald-600' : (val < 0 ? 'text-rose-600' : 'text-slate-400'));
            }

            function updatePeriodoTexto(startDate, endDate) {
                document.getElementById('periodoTexto').textContent = formatDateDisplay(startDate) + ' — ' + formatDateDisplay(endDate);
                const periodNames = { semanal: 'Semanal', mensual: 'Mensual', semestral: 'Semestral', anual: 'Anual', personalizado: 'Personalizado' };
                document.getElementById('periodoLabel').textContent = 'Mostrando datos del período (' + (periodNames[currentSelected] || 'Personalizado') + ')';
            }

            function updateMetricsTable(resumen) {
                const rows = [
                    ['Total de Citas', resumen.total_citas],
                    ['Total de Pacientes Únicos', resumen.total_pacientes],
                    ['Hombres', resumen.genero?.masculino || 0],
                    ['Mujeres', resumen.genero?.femenino || 0],
                    ['Promedio de Edad', (resumen.edades?.promedio || 0) + ' años'],
                    ['Mediana de Edad', (resumen.edades?.mediana || 0) + ' años'],
                    ['Moda de Edad', (resumen.edades?.moda || 0) + ' años'],
                    ['Hora Pico (Moda)', resumen.hora_pico || 'N/A'],
                    ['Volumen Promedio Semanal', (resumen.promedio_semanal || 0) + ' citas/semana'],
                    ['Tasa de Asistencia', (resumen.tasa_asistencia || 0) + '%'],
                    ['Tiempo de Espera Promedio', (resumen.tiempo_espera_promedio || 0) + ' días'],
                    ['Comparativa vs. Período Anterior', (resumen.comparativa_pacientes > 0 ? '+' : '') + (resumen.comparativa_pacientes || 0) + '%'],
                ];

                // Roles
                rows.push(['<strong>ROLES INSTITUCIONALES</strong>', '']);
                if (resumen.perfil_academico) {
                    Object.entries(resumen.perfil_academico).forEach(([rol, cant]) => {
                        rows.push([rol, cant]);
                    });
                }
                // PNF
                rows.push(['<strong>PACIENTES POR PNF / CARRERA</strong>', '']);
                if (resumen.pnf) {
                    const pnfLabels = {
                        Administracion: 'Administración',
                        Mecanica: 'Mecánica',
                        Mantenimiento: 'Mantenimiento',
                        Electricidad: 'Electricidad',
                        Veterinaria: 'Veterinaria',
                        Informatica: 'Informática',
                        PDA: 'PDA',
                        Distribucion_Logistica: 'Distribución y Logística',
                        Agroalimentacion: 'Agroalimentación',
                        Seguridad_Alimentaria_Nutricional: 'Seguridad alimentaria y Cultura Nutricional',
                        'No especificado': 'No especificado',
                        'No aplica': 'No aplica'
                    };
                    Object.entries(resumen.pnf).forEach(([pnfKey, cant]) => {
                        rows.push([pnfLabels[pnfKey] || pnfKey, cant]);
                    });
                }

                const tbody = document.getElementById('tablaMetricasBody');
                tbody.innerHTML = rows.map(([label, val]) => `
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="py-3 text-sm font-medium text-slate-600 dark:text-gray-300">${label}</td>
                        <td class="py-3 text-sm font-bold text-slate-800 dark:text-white text-right">${val}</td>
                    </tr>
                `).join('');
            }

            function destroyChart(name) {
                if (charts[name]) { charts[name].destroy(); charts[name] = null; }
            }

            function buildCharts(resumen) {
                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
                const tickColor = isDark ? '#9ca3af' : '#94a3b8';

                // --- Flujo Semanal (Line/Area) ---
                destroyChart('flujo');
                const flujoLabels = Object.keys(resumen.flujo_semanal || {}).map(k => 'Sem ' + k.split('-')[0]);
                const flujoData = Object.values(resumen.flujo_semanal || {});
                charts.flujo = new Chart(document.getElementById('chartFlujoSemanal'), {
                    type: 'line',
                    data: {
                        labels: flujoLabels,
                        datasets: [{
                            label: 'Pacientes',
                            data: flujoData,
                            borderColor: COLORS.sky.border,
                            backgroundColor: COLORS.sky.bg,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: COLORS.sky.border,
                            pointBorderWidth: 2.5,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 11 } } },
                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } }
                        }
                    }
                });

                // --- Distribución por Horas (Vertical Bar) ---
                destroyChart('horas');
                const horasLabels = Object.keys(resumen.distribucion_horas || {});
                const horasData = Object.values(resumen.distribucion_horas || {});
                charts.horas = new Chart(document.getElementById('chartHoras'), {
                    type: 'bar',
                    data: {
                        labels: horasLabels,
                        datasets: [{
                            label: 'Citas',
                            data: horasData,
                            backgroundColor: COLORS.emerald.bg,
                            borderColor: COLORS.emerald.border,
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            maxBarThickness: 40
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 10 }, maxRotation: 0, autoSkip: false } },
                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } }
                        }
                    }
                });

                // --- Distribución por Edad (Horizontal Bar) ---
                destroyChart('edades');
                const edadLabels = Object.keys(resumen.edades?.rangos || {});
                const edadData = Object.values(resumen.edades?.rangos || {});
                const barColors = [COLORS.indigo, COLORS.sky, COLORS.emerald, COLORS.amber, COLORS.rose];
                charts.edades = new Chart(document.getElementById('chartEdades'), {
                    type: 'bar',
                    data: {
                        labels: edadLabels.map(l => l + ' años'),
                        datasets: [{
                            label: 'Pacientes',
                            data: edadData,
                            backgroundColor: barColors.map(c => c.bg),
                            borderColor: barColors.map(c => c.border),
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } },
                            y: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 12 } } }
                        }
                    }
                });

                // --- Género (Doughnut) ---
                destroyChart('genero');
                charts.genero = new Chart(document.getElementById('chartGenero'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Hombres', 'Mujeres', 'Otro'],
                        datasets: [{
                            data: [resumen.genero?.masculino || 0, resumen.genero?.femenino || 0, resumen.genero?.otro || 0],
                            backgroundColor: [COLORS.sky.border, COLORS.rose.border, COLORS.amber.border],
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 11 }, color: tickColor } }
                        }
                    }
                });

                // --- Avances (Doughnut) ---
                destroyChart('avances');
                const avancesLabels = Object.keys(resumen.avances || {});
                const avancesData = Object.values(resumen.avances || {});
                charts.avances = new Chart(document.getElementById('chartAvances'), {
                    type: 'doughnut',
                    data: {
                        labels: avancesLabels,
                        datasets: [{
                            data: avancesData,
                            backgroundColor: PALETTE.slice(0, avancesLabels.length),
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 10 }, color: tickColor } }
                        }
                    }
                });

                // --- Prioridades (Doughnut) ---
                destroyChart('prioridades');
                const prioridadesLabels = Object.keys(resumen.prioridades || {});
                const prioridadesData = Object.values(resumen.prioridades || {});
                charts.prioridades = new Chart(document.getElementById('chartPrioridades'), {
                    type: 'doughnut',
                    data: {
                        labels: prioridadesLabels,
                        datasets: [{
                            data: prioridadesData,
                            backgroundColor: PALETTE.slice(0, prioridadesLabels.length).reverse(),
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 10 }, color: tickColor } }
                        }
                    }
                });

                // --- Estados de Animo (Doughnut) ---
                destroyChart('estadosAnimo');
                const estadosAnimoLabels = Object.keys(resumen.estados_animo || {});
                const estadosAnimoData = Object.values(resumen.estados_animo || {});
                charts.estadosAnimo = new Chart(document.getElementById('chartEstadosAnimo'), {
                    type: 'doughnut',
                    data: {
                        labels: estadosAnimoLabels,
                        datasets: [{
                            data: estadosAnimoData,
                            backgroundColor: PALETTE.slice(0, estadosAnimoLabels.length),
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 10 }, color: tickColor } }
                        }
                    }
                });
            }

            async function loadDashboard(startDate, endDate) {
                currentStartDate = startDate;
                currentEndDate = endDate;
                updatePeriodoTexto(startDate, endDate);
                try {
                    const data = await fetchData(startDate, endDate);
                    updateKPIs(data.resumen);
                    updateMetricsTable(data.resumen);
                    buildCharts(data.resumen);
                } catch(err) {
                    console.error('Error cargando dashboard:', err);
                }
            }

            function cambiarFiltro(tipo) {
                currentSelected = tipo;
                const { start, end } = calcularFechas(tipo);
                loadDashboard(start, end);
            }

            function aplicarPersonalizado() {
                currentSelected = 'personalizado';
                const s = document.getElementById('customStartDate').value;
                const e = document.getElementById('customEndDate').value;
                if (s && e) {
                    loadDashboard(s, e);
                }
            }

            function recargar() {
                loadDashboard(currentStartDate, currentEndDate);
            }

            function exportar(formato, reportType = 'completo') {
                const url = `{{ route('agenda.estadisticas') }}?format=${formato}&report_type=${reportType}&psicologo_id=${psicologoId}&start_date=${currentStartDate}&end_date=${currentEndDate}&periodo=${currentSelected}${getFilterParams()}`;
                if (formato === 'pdf') {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }
            }

            // Initial load
            document.addEventListener('DOMContentLoaded', function() {
                loadDashboard(currentStartDate, currentEndDate);
            });

            return { cambiarFiltro, aplicarPersonalizado, exportar, recargar };
        })();
    </script>
</x-app-layout>
