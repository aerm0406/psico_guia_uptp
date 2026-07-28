<x-app-layout>
    <style>
        /* Invisible scrollbar but keep scroll functionality */
        .invisible-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .invisible-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>

    <div class="py-2">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-3xl border-l-8 border-sky-700">
                <div class="p-4 sm:p-8 text-gray-900 dark:text-gray-100">
                    {{-- Selector de Psicólogo para Admin --}}
                    <x-psicologo-selector :psicologos="$psicologos" :psicologoId="$psicologoId" />
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 mt-4 w-full">
                        <div class="flex-shrink-0">
                            <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight whitespace-nowrap">
                                @if(auth()->user()->role === 'admin')
                                    Agenda Centralizada
                                @else
                                    Mi Agenda
                                @endif
                            </h3>
                        </div>

                        <div class="flex flex-col md:flex-row md:flex-nowrap items-stretch md:items-center justify-start md:justify-end gap-2 sm:gap-3 w-full md:w-auto overflow-x-auto invisible-scrollbar pb-1">
                            {{-- Bloquesito de Fecha de Hoy --}}
                            <div class="flex items-center justify-center gap-2 bg-sky-600 text-white px-3 sm:px-4 h-12 rounded-2xl shadow-sm flex-shrink-0 w-full md:w-auto">
                                <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <div class="flex flex-col leading-none text-left">
                                    <span class="text-[8px] font-black uppercase tracking-[0.15em] opacity-70">Fecha de hoy</span>
                                    <span class="text-[11px] sm:text-[12px] font-black uppercase tracking-wide whitespace-nowrap">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('ddd DD MMM, YYYY') }}</span>
                                </div>
                            </div>

                            @if($view === 'list')
                                <button type="button" onclick="document.getElementById('filterModal').classList.remove('hidden'); document.getElementById('filterModal').classList.add('flex');" class="flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 h-12 rounded-2xl shadow-sm transition-all flex-shrink-0 w-full md:w-auto" title="Filtrar Fechas">
                                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                    <div class="flex flex-col leading-none">
                                        <span class="text-[12px] font-black uppercase tracking-wide whitespace-nowrap">Filtrar</span>
                                    </div>
                                </button>
                            @endif

                            {{-- Navegador de Fechas (Solo para Month/Week) --}}
                            @if($view !== 'list')
                                <div class="flex items-center justify-between md:justify-center gap-1 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 p-1 h-12 rounded-2xl shadow-sm flex-shrink-0 w-full md:w-auto">
                                    <a href="{{ route('agenda.index', ['view' => $view, 'date' => ($view === 'month' ? $currentDate->copy()->subMonth() : $currentDate->copy()->subWeek())->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                       class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-50 dark:hover:bg-gray-600 text-slate-400 dark:text-gray-500 transition-colors flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                    </a>

                                    <span class="px-2 sm:px-4 text-[10px] sm:text-[11px] font-black text-slate-700 dark:text-gray-300 min-w-[110px] sm:min-w-[130px] text-center uppercase tracking-widest leading-none whitespace-nowrap">
                                        @if($view === 'month')
                                            {{ $currentDate->translatedFormat('F Y') }}
                                        @else
                                            Semana {{ ceil($currentDate->day / 7) }}
                                        @endif
                                    </span>

                                    <a href="{{ route('agenda.index', ['view' => $view, 'date' => ($view === 'month' ? $currentDate->copy()->addMonth() : $currentDate->copy()->addWeek())->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                       class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-50 dark:hover:bg-gray-600 text-slate-400 dark:text-gray-500 transition-colors flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </div>
                            @endif

                            {{-- Toggles de Vista --}}
                            <div class="bg-slate-100/80 dark:bg-gray-700/80 p-1 h-12 rounded-2xl flex items-center justify-center md:justify-start gap-1 flex-shrink-0 w-full md:w-auto">
                                <a href="{{ route('agenda.index', ['view' => 'month', 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                   class="flex-1 md:flex-none px-3 sm:px-4 h-9 flex items-center justify-center rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap {{ $view === 'month' ? 'bg-white dark:bg-gray-600 text-sky-700 dark:text-sky-400 shadow-sm' : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-300' }}"
                                   title="Vista Mensual">
                                    Mes
                                </a>
                                <a href="{{ route('agenda.index', ['view' => 'week', 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                   class="flex-1 md:flex-none px-3 sm:px-4 h-9 flex items-center justify-center rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap {{ $view === 'week' ? 'bg-white dark:bg-gray-600 text-sky-700 dark:text-sky-400 shadow-sm' : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-300' }}"
                                   title="Vista Semanal">
                                    Semana
                                </a>
                                <a href="{{ route('agenda.index', ['view' => 'list', 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                   class="px-3 h-9 flex items-center justify-center rounded-xl transition-all flex-shrink-0 {{ $view === 'list' ? 'bg-white dark:bg-gray-600 text-sky-700 dark:text-sky-400 shadow-sm' : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-300' }}"
                                   title="Historial de Sesiones">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </a>
                                {{-- Dashboard Button --}}
                                <a href="{{ route('agenda.estadisticas', ['format' => 'html', 'psicologo_id' => $psicologoId]) }}" class="px-3 h-9 flex items-center justify-center rounded-xl bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/50 transition-all font-bold text-[10px] uppercase tracking-widest gap-1.5 flex-shrink-0" title="Panel Estadístico">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </a>

                                {{-- Botón directo PDF de Agenda Semanal --}}
                                <a href="{{ route('agenda.exportarPdf', ['view' => $view, 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}" target="_blank"
                                   class="px-3 h-9 flex items-center justify-center rounded-xl text-slate-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all font-bold tracking-widest gap-1.5 flex-shrink-0" title="Imprimir Agenda en PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 {{ $view === 'week' ? 'xl:grid-cols-4' : '' }} gap-8 flex-col-reverse xl:flex-row">
                        @if($view === 'week')
                            {{-- Sidebar de Solicitudes --}}
                            <aside id="pendingRequestsPanel" class="xl:col-span-1 bg-white dark:bg-gray-800 rounded-[32px] border border-slate-100 dark:border-gray-700 p-6 shadow-sm order-2 xl:order-1 flex flex-col max-h-[calc(100vh-250px)] overflow-hidden">
                                <div class="flex items-center gap-3 mb-6">
                                    <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight">Pendientes</h3>
                                </div>

                                <div class="space-y-4 mb-6">
                                    <div class="relative">
                                        <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest" for="pendingFilter">Paciente</label>
                                        <input id="pendingFilter" type="text" class="w-full rounded-2xl border-slate-100 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all placeholder-slate-300 dark:placeholder-gray-500 font-medium text-gray-900 dark:text-white pr-10" placeholder="Buscar..." />
                                        <div id="searchSpinner" class="absolute right-3 top-[34px] hidden">
                                            <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest" for="priorityFilter">Prioridad</label>
                                        <select id="priorityFilter" class="w-full rounded-2xl border-slate-100 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all font-medium text-gray-900 dark:text-white">
                                            <option value="">Todas</option>
                                            @foreach($prioridadesDisponibles as $prioridad)
                                                <option value="{{ $prioridad->nombre }}">{{ ucfirst($prioridad->nombre) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @include('agenda.components.pending-list')
                            </aside>
                        @endif

                        {{-- Área de Contenido Principal (Calendario/Lista) --}}
                        <section class="{{ $view === 'week' ? 'xl:col-span-3' : 'w-full' }} relative order-1 xl:order-2">
                            <div id="agendaMainView" class="transition-all duration-300 w-full rounded-3xl">
                            @if($view === 'month')
                                {{-- VISTA MENSUAL --}}
                                <div class="bg-white dark:bg-gray-800 rounded-[32px] border border-slate-100 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-auto max-h-[calc(100vh-250px)] invisible-scrollbar relative">
                                    <div class="min-w-[700px]">
                                        <div class="grid grid-cols-7 border-b border-slate-100 dark:border-gray-700 bg-slate-50/90 dark:bg-gray-700/90 backdrop-blur-md sticky top-0 z-10">
                                            @foreach(['DOM','LUN','MAR','MIÉ','JUE','VIE','SÁ'] as $diaLabel)
                                                <div class="py-4 text-center text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">{{ $diaLabel }}</div>
                                            @endforeach
                                        </div>
                                    <div class="grid grid-cols-7">
                                        @foreach($calendarioData as $data)
                                            <div onclick="openDailyAgenda(this, '{{ $data['date'] }}')"
                                                 class="min-h-[120px] p-2 border-b border-r border-slate-50 dark:border-gray-700 relative group cursor-pointer hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-all {{ !$data['isCurrentMonth'] ? 'bg-slate-50/30 dark:bg-gray-700/20' : '' }} {{ $data['isToday'] ? 'bg-sky-50/50 dark:bg-sky-900/20' : '' }}"
                                                 data-date="{{ $data['date'] }}">
                                                <div class="flex justify-between items-start mb-2">
                                                    <span class="text-xs font-black {{ $data['isToday'] ? 'w-7 h-7 bg-sky-700 text-white rounded-lg flex items-center justify-center shadow-lg shadow-sky-100 dark:shadow-sky-900/30' : ($data['isCurrentMonth'] ? 'text-slate-800 dark:text-gray-300' : 'text-slate-300 dark:text-gray-600') }}">
                                                        {{ $data['day'] }}
                                                    </span>
                                                </div>

                                                <div class="space-y-1 overflow-y-auto max-h-[80px] custom-scrollbar pointer-events-none">
                                                    @foreach($data['citas']->where('estado', '!=', 'cancelada') as $cita)
                                                        <div class="px-2 py-1 rounded-md text-[9px] font-bold truncate
                                                            {{ $cita->estado === 'confirmada' ? 'bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800' :
                                                               ($cita->estado === 'realizada' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' :
                                                               ($cita->estado === 'no_asistio' ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-gray-400')) }}">
                                                            {{ $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('g:i A') : 'S/H' }} - {{ $cita->paciente_short_name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                        </div>
                                    </div>
                                </div>
                            @elseif($view === 'list')
                                {{-- VISTA HISTORIAL --}}
                                <div class="bg-white dark:bg-gray-800 rounded-[32px] border-slate-100 dark:border-gray-700 shadow-sm overflow-hidden p-4 sm:p-8">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-center sm:justify-between gap-2 mb-8">
                                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight w-full text-center sm:text-left">Historial de Citas</h3>
                                        <div class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest w-full text-center sm:text-left">Total: {{ $citasCalendario->total() }} registros</div>
                                    </div>

                                    <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-250px)] rounded-[10px] invisible-scrollbar relative">
                                        <table class="min-w-[600px] w-full text-left">
                                            <thead class="bg-slate-50/90 dark:bg-gray-800/90 backdrop-blur-md sticky top-0 z-10 shadow-sm">
                                                <tr class="border-b border-slate-100 dark:border-gray-700">
                                                    <th class="pb-4 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Paciente</th>
                                                    <th class="pb-4 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Solicitada</th>
                                                    <th class="pb-4 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Fecha y Hora</th>
                                                    <th class="pb-4 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Estado</th>
                                                    <th class="pb-4"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50 dark:divide-gray-700">
                                                @forelse($citasCalendario as $cita)
                                                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-gray-700/50 transition-colors">
                                                        <td class="py-4">
                                                            <div class="flex items-center gap-3">
                                                                <div class="w-8 h-8 bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 rounded-xl flex items-center justify-center text-[10px] font-black uppercase">
                                                                    {{ substr($cita->paciente_nombre, 0, 1) }}
                                                                </div>
                                                                <span class="text-sm font-bold text-slate-700 dark:text-gray-300">{{ $cita->paciente_nombre }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="py-4">
                                                            <div class="flex flex-col">
                                                                <span class="text-sm font-bold text-slate-600 dark:text-gray-400">{{ $cita->created_at ? \Carbon\Carbon::parse($cita->created_at)->translatedFormat('d M, Y') : 'N/A' }}</span>
                                                                <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase">{{ $cita->created_at ? \Carbon\Carbon::parse($cita->created_at)->format('g:i A') : '' }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="py-4">
                                                            <div class="flex flex-col">
                                                                @if(!$cita->hora)
                                                                    <span class="text-sm font-bold text-slate-400 dark:text-gray-500 uppercase tracking-tighter italic">Sin horario asignado</span>
                                                                @else
                                                                    <span class="text-sm font-bold text-slate-700 dark:text-gray-300">{{ $cita->fecha ? $cita->fecha->translatedFormat('d M, Y') : 'Sin fecha' }}</span>
                                                                    <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase">{{ \Carbon\Carbon::parse($cita->hora)->format('g:i A') }}</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="py-4">
                                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border
                                                                {{ $cita->estado === 'realizada' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800' :
                                                                   ($cita->estado === 'cancelada' ? 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-800' :
                                                                   ($cita->estado === 'rechazada' ? 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-800' :
                                                                   ($cita->estado === 'confirmada' ? 'bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 border-sky-100 dark:border-sky-800' :
                                                                   ($cita->estado === 'no_asistio' ? 'bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 border-orange-100 dark:border-orange-800' : 'bg-slate-50 dark:bg-gray-700 text-slate-600 dark:text-gray-400 border-slate-100 dark:border-gray-600')))) }}">
                                                                {{ str_replace('_', ' ', $cita->estado) }}
                                                            </span>
                                                        </td>
                                                        <td class="py-4 text-right">
                                                            <button type="button" onclick="abrirDetalleCita({{ json_encode([
                                                                'paciente' => $cita->paciente_nombre,
                                                                'estado' => $cita->estado,
                                                                'motivo' => $cita->motivo ?? 'No especificado',
                                                                'prioridad' => $cita->prioridad ?? 'Normal',
                                                                'fecha_solicitud' => $cita->created_at ? \Carbon\Carbon::parse($cita->created_at)->translatedFormat('d M, Y - g:i A') : 'N/A',
                                                                'fecha_programada' => ($cita->fecha && $cita->hora) ? $cita->fecha->translatedFormat('d M, Y') . ' - ' . \Carbon\Carbon::parse($cita->hora)->format('g:i A') : 'Sin horario asignado',
                                                                'fecha_programada_iso' => $cita->fecha ? $cita->fecha->format('Y-m-d') : null,
                                                                'hora_programada_iso' => $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('H:i') : null,
                                                                'cancelado_por' => $cita->cancelado_por ?? null,
                                                                'motivo_rechazo' => $cita->motivo_rechazo_propuesta ?? null,
                                                            ]) }})" class="p-2 text-slate-300 dark:text-gray-600 hover:text-sky-700 dark:hover:text-sky-400 transition-colors">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="py-12 text-center text-slate-400 dark:text-gray-500 font-bold text-xs uppercase tracking-widest">
                                                            Historial de citas vacío
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($citasCalendario->lastPage() > 1)
                                        <div class="mt-8 flex justify-center">
                                            {{ $citasCalendario->appends(request()->query())->links('agenda.partials.pagination') }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- VISTA SEMANAL --}}
                                @if(isset($grupoActivo) && $horarios->isNotEmpty())
                                    @php
                                        $currentDate = ($currentDate->dayOfWeek === \Carbon\Carbon::SUNDAY) ? $currentDate->copy()->next(\Carbon\Carbon::MONDAY) : $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);

                                        $normalizeBlock = function ($text) {
                                            $value = trim($text ?? '');

                                            $value = preg_replace_callback('/(\d{1,2}):(\d{2})\s*(am|pm)\b/i', function($matches) {
                                                $hours = (int)$matches[1];
                                                $ampm = strtolower($matches[3]);
                                                if ($ampm === 'pm' && $hours < 12) $hours += 12;
                                                if ($ampm === 'am' && $hours === 12) $hours = 0;
                                                return sprintf('%02d:%s', $hours, $matches[2]);
                                            }, $value);

                                            $value = preg_replace([
                                                '/\s*[-–—]\s*/u',
                                                '/(\d{1,2}:\d{2}):\d{2}/',
                                                '/\s+/'
                                            ], [
                                                '-',
                                                '$1',
                                                ' '
                                            ], $value);

                                            $value = preg_replace('/(^|\s|-)(\d):/', '${1}0$2:', $value);

                                            return strtolower($value);
                                        };

                                        $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes'];

                                        $defaultIntervalos = collect([
                                            ['inicio' => '07:00', 'fin' => '08:15'],
                                            ['inicio' => '08:15', 'fin' => '09:20'],
                                            ['inicio' => '09:20', 'fin' => '10:00'],
                                            ['inicio' => '10:00', 'fin' => '10:45'],
                                            ['inicio' => '10:45', 'fin' => '11:30'],
                                            ['inicio' => '11:30', 'fin' => '12:20'],
                                            ['inicio' => '12:20', 'fin' => '13:00'],
                                            ['inicio' => '13:00', 'fin' => '13:45'],
                                            ['inicio' => '13:45', 'fin' => '14:25'],
                                            ['inicio' => '14:25', 'fin' => '15:05'],
                                            ['inicio' => '15:05', 'fin' => '15:45'],
                                            ['inicio' => '16:00', 'fin' => '16:40'],
                                            ['inicio' => '16:40', 'fin' => '17:20'],
                                            ['inicio' => '17:20', 'fin' => '18:00'],
                                            ['inicio' => '18:00', 'fin' => '18:35'],
                                            ['inicio' => '18:35', 'fin' => '19:10'],
                                            ['inicio' => '19:10', 'fin' => '19:45'],
                                            ['inicio' => '19:45', 'fin' => '20:20'],
                                            ['inicio' => '20:20', 'fin' => '20:55'],
                                            ['inicio' => '20:55', 'fin' => '21:30'],
                                        ]);

                                        $intervalos = $defaultIntervalos->sortBy(function ($item) {
                                            return \Carbon\Carbon::parse($item['inicio'])->timestamp;
                                        })->values()->all();
                                    @endphp
                                    <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-250px)] rounded-[32px] border border-slate-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm invisible-scrollbar relative">
                                        <table class="min-w-[800px] w-full divide-y divide-slate-100 dark:divide-gray-700 text-sm">
                                            <thead class="bg-slate-50/90 dark:bg-gray-800/90 backdrop-blur-md sticky top-0 z-10 shadow-sm">
                                                <tr>
                                                    <th class="px-4 py-4 text-center text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest border-r border-slate-100 dark:border-gray-700">Hora</th>
                                                    @foreach($dias as $diaHeaderIndex => $dia)
                                                        @php
                                                            $fechaColumna = $currentDate->copy()->addDays($diaHeaderIndex);
                                                            $esHoy = $fechaColumna->isToday();
                                                        @endphp
                                                        <th class="px-4 py-3 text-center uppercase tracking-widest {{ $esHoy ? 'bg-sky-50/80 dark:bg-sky-900/20' : '' }}">
                                                            <div class="flex flex-col items-center gap-1">
                                                                <span class="text-[9px] font-black {{ $esHoy ? 'text-sky-600 dark:text-sky-400' : 'text-slate-400 dark:text-gray-500' }} tracking-[0.15em]">{{ $dia }}</span>
                                                                <span class="@if($esHoy) w-7 h-7 bg-sky-700 text-white rounded-lg flex items-center justify-center text-[11px] font-black shadow-sm @else text-[13px] font-black text-slate-700 dark:text-gray-300 @endif">
                                                                    {{ $fechaColumna->day }}
                                                                </span>
                                                            </div>
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50 dark:divide-gray-700">
                                                @php $sectionActual = null; @endphp
                                                @foreach($intervalos as $intervalo)
                                                    @php
                                                        $t = \Carbon\Carbon::parse($intervalo['inicio']);
                                                        $seccion = $t->lt(\Carbon\Carbon::parse('12:30')) ? 'Mañana' : ($t->lt(\Carbon\Carbon::parse('18:00')) ? 'Vespertino' : 'Nocturno');
                                                    @endphp

                                                    @if($sectionActual !== $seccion)
                                                        <tr class="bg-slate-50/30 dark:bg-gray-700/20">
                                                            <td colspan="6" class="px-4 py-2 text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] text-center">{{ $seccion }}</td>
                                                        </tr>
                                                        @php $sectionActual = $seccion; @endphp
                                                    @endif

                                                    <tr>
                                                        <td class="px-4 py-4 text-center text-[10px] font-black text-slate-400 dark:text-gray-500 border-r border-slate-50 dark:border-gray-700 bg-slate-50/10 dark:bg-gray-700/20">
                                                            {{ \Carbon\Carbon::parse($intervalo['inicio'])->format('g:i') }} - {{ \Carbon\Carbon::parse($intervalo['fin'])->format('g:i') }}
                                                        </td>
                                                        @foreach($dias as $diaIndex => $dia)
                                                            @php
                                                                $horaInicio = $intervalo['inicio'];
                                                                $horaFin = $intervalo['fin'];
                                                                $horarioBloque = $horarios->where('dia', $dia)->first(fn($h) => $h->hora_inicio < $horaFin && $h->hora_fin > $horaInicio);

                                                                $bloqueLabel = $horarioBloque ? ($dia . ' ' . \Carbon\Carbon::parse($horarioBloque->hora_inicio)->format('H:i') . ' - ' . \Carbon\Carbon::parse($horarioBloque->hora_fin)->format('H:i')) : "$dia $horaInicio - $horaFin";
                                                                $normalizedSlotText = $normalizeBlock($bloqueLabel);

                                                                $fechaDelDia = $currentDate->copy()->addDays($diaIndex)->toDateString();

                                                                $citasConfirmadasEnSlot = $citasCalendario->filter(fn($cita) => in_array($cita->estado, ['confirmada', 'realizada', 'no_asistio']) && $cita->fecha->isSameDay($fechaDelDia) && $cita->bloque_propuesto && str_contains($normalizeBlock($cita->bloque_propuesto), $normalizedSlotText));
                                                                $assignedCita = $citasConfirmadasEnSlot->first();

                                                                $citasCanceladasEnSlot = $citasCalendario->filter(fn($cita) => $cita->estado === 'cancelada' && $cita->fecha && $cita->fecha->isSameDay($fechaDelDia) && $cita->bloque_propuesto && str_contains($normalizeBlock($cita->bloque_propuesto), $normalizedSlotText));
                                                                $canceladaCita = $citasCanceladasEnSlot->sortByDesc('updated_at')->first();

                                                                $citasEnSlot = $citasPendientes->filter(function ($cita) use ($normalizedSlotText, $normalizeBlock, $dia, $horaInicio, $horaFin, $fechaDelDia) {
                                                                    if (!$cita->bloques_sugeridos) return false;

                                                                    $raw = $cita->bloques_sugeridos;
                                                                    $excepcionesStr = '';
                                                                    $horariosStr = $raw;
                                                                    if (str_contains($raw, '|')) {
                                                                        $parts = explode('|', $raw);
                                                                        $leftPart = trim($parts[0]);
                                                                        $rightPart = trim($parts[1]);

                                                                        // Solo tratar como excepciones si explícitamente dice "Días exceptuados:"
                                                                        // "Días propuestos:" NO son excepciones, son los días que el paciente SÍ puede
                                                                        if (str_contains($leftPart, 'exceptuados')) {
                                                                            $excepcionesStr = trim(str_replace('Días exceptuados:', '', $leftPart));
                                                                        }

                                                                        // Remover el prefijo de horarios (ambas variantes)
                                                                        $horariosStr = preg_replace('/^\s*Horarios\s*(propuestos)?\s*:\s*/i', '', $rightPart);
                                                                    }

                                                                    if ($excepcionesStr !== '') {
                                                                        $excepcionesArray = array_map('trim', explode(',', $excepcionesStr));
                                                                        if (in_array($fechaDelDia, $excepcionesArray)) {
                                                                            return false;
                                                                        }
                                                                    }

                                                                    if (\Carbon\Carbon::parse($fechaDelDia)->isBefore(\Carbon\Carbon::today())) return false;
                                                                    if (\Carbon\Carbon::parse($fechaDelDia)->isAfter(\Carbon\Carbon::today()->addMonth())) return false;

                                                                    $bloques = array_filter(array_map('trim', explode(';', str_replace(',', ';', $horariosStr))));
                                                                    foreach ($bloques as $bloque) {
                                                                        if (str_contains($bloque, $fechaDelDia) || str_contains($normalizeBlock($bloque), strtolower($dia))) {
                                                                            if (preg_match('/(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)\s*[-\x96\x97]\s*(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)/i', $bloque, $m)) {
                                                                                $sI = \Carbon\Carbon::parse($m[1]); $sF = \Carbon\Carbon::parse($m[2]);
                                                                                if (\Carbon\Carbon::parse($horaInicio)->lt($sF) && \Carbon\Carbon::parse($horaFin)->gt($sI)) return true;
                                                                            }
                                                                        }
                                                                    }
                                                                    return false;
                                                                });
                                                            @endphp
                                                            <td class="px-2 py-3">
                                                                @if($horarioBloque)
                                                                    <button type="button" class="block-slot-button w-full rounded-2xl border p-3 text-center transition-all drop-zone group
                                                                        {{ $assignedCita ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-100 dark:border-indigo-800 text-indigo-700 dark:text-indigo-400 shadow-sm' :
                                                                           ($horarioBloque->activo === \App\Models\Horario::STATUS_ACTIVE ? 'bg-white dark:bg-gray-700 border-slate-100 dark:border-gray-600 text-slate-600 dark:text-gray-300 hover:border-indigo-200 dark:hover:border-indigo-700 hover:shadow-md' : 'bg-orange-50 dark:bg-orange-900/30 border-orange-100 dark:border-orange-800 text-orange-700 dark:text-orange-400 hover:border-orange-200 dark:hover:border-orange-700') }}"
                                                                        data-block-label="{{ $bloqueLabel }}"
                                                                        data-block-date="{{ $fechaDelDia }}"
                                                                        data-block-time="{{ $horarioBloque->hora_inicio }}"
                                                                        data-block-active="{{ $horarioBloque->activo === \App\Models\Horario::STATUS_ACTIVE ? 'true' : 'false' }}"
                                                                        @if($assignedCita) data-assigned-cita-id="{{ $assignedCita->id }}" data-assigned-paciente="{{ $assignedCita->paciente_short_name }}" data-assigned-estado="{{ $assignedCita->estado }}" data-assigned-block="true" @endif>

                                                                        <div class="flex items-center justify-center mb-1">
                                                                            <span class="text-[9px] font-black uppercase tracking-tighter opacity-50 group-hover:opacity-100 transition-opacity text-slate-500 dark:text-gray-400">
                                                                                {{ \Carbon\Carbon::parse($horarioBloque->hora_inicio)->format('g:i') }} - {{ \Carbon\Carbon::parse($horarioBloque->hora_fin)->format('g:i') }}
                                                                            </span>
                                                                        </div>

                                                                        <div class="block-slot-status flex flex-col items-center gap-0.5">
                                                                            @if($assignedCita)
                                                                                <div class="flex items-center gap-1">
                                                                                    @if($assignedCita->estado === 'realizada')
                                                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shadow-[0_0_2px_rgba(59,130,246,0.8)]"></span>
                                                                                    @elseif($assignedCita->estado === 'no_asistio')
                                                                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 shadow-[0_0_2px_rgba(239,68,68,0.8)]"></span>
                                                                                    @else
                                                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_2px_rgba(16,185,129,0.8)]"></span>
                                                                                    @endif
                                                                                    <p class="text-[10px] font-black leading-tight truncate text-indigo-700 dark:text-indigo-400">{{ $assignedCita->paciente_short_name }}</p>
                                                                                </div>
                                                                            @else
                                                                                @if($citasEnSlot->isNotEmpty())
                                                                                    <div class="flex items-center gap-1">
                                                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                                                        <p class="text-[10px] font-black text-amber-600 dark:text-amber-400">{{ $citasEnSlot->count() }} Solic.</p>
                                                                                    </div>
                                                                                @else
                                                                                    <p class="text-[9px] font-bold text-slate-300 dark:text-gray-600 group-hover:text-slate-400 dark:group-hover:text-gray-400 uppercase">Libre</p>
                                                                                @endif

                                                                                @if($canceladaCita)
                                                                                    <div class="flex items-center justify-center gap-1 w-full mt-0.5" onclick="event.stopPropagation()">
                                                                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 flex-shrink-0" title="{{ $canceladaCita->cancelado_por === 'paciente' ? 'Cancelado por el paciente' : 'Cancelada por el psicólogo' }}"></span>
                                                                                        <p class="text-[10px] font-bold leading-tight truncate text-slate-500 line-through" title="{{ $canceladaCita->cancelado_por === 'paciente' ? 'Cancelado por el paciente' : 'Cancelada por el psicólogo' }}">
                                                                                            {{ $canceladaCita->paciente_short_name }}
                                                                                        </p>
                                                                                        <div onclick="dismissCancelMessage(event, {{ $canceladaCita->id }})" class="ml-1 text-[10px] font-black text-slate-400 hover:text-slate-600 transition-colors flex-shrink-0 cursor-pointer" title="Ocultar">✕</div>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </button>
                                                                @else
                                                                    <div class="h-10 flex items-center justify-center">
                                                                        <div class="w-1 h-1 bg-slate-100 dark:bg-gray-700 rounded-full"></div>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="mt-6 min-h-[400px] bg-white dark:bg-gray-800 rounded-[32px] border-2 border-dashed border-slate-100 dark:border-gray-700 p-12 flex flex-col items-center justify-center text-center">
                                        <div class="w-20 h-20 bg-slate-50 dark:bg-gray-700 text-slate-300 dark:text-gray-500 rounded-3xl flex items-center justify-center mb-6">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">Sin Horarios Activos</h3>
                                        <p class="text-slate-400 dark:text-gray-500 text-sm max-w-xs mx-auto">Gestiona tus grupos de horarios para comenzar a agendar citas en esta semana.</p>
                                    </div>
                                @endif
                            @endif
                            </div> <!-- End agendaMainView -->

                            <!-- Block Manager View (Hidden by default) -->
                            <div id="agendaBlockManagerView" class="hidden opacity-0 transition-all duration-300 w-full bg-white dark:bg-gray-800 rounded-[32px] border border-slate-100 dark:border-gray-700 shadow-sm overflow-hidden p-6 md:p-8">
                                <div class="flex flex-col h-full min-h-[400px]">
                                    <!-- Header -->
                                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-gray-700 pb-4 mb-6">
                                        <div>
                                            <button type="button" onclick="closeBlockManager()" class="flex items-center gap-2 text-sky-600 hover:text-sky-700 dark:text-sky-400 font-black text-sm uppercase tracking-wider mb-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                                Volver a la Agenda
                                            </button>
                                            <h3 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight" id="blockManagerTitle"></h3>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" id="blockManagerPrevBtn" onclick="navigateBlock(-1)" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white dark:bg-gray-700 border border-slate-100 dark:border-gray-600 text-slate-400 dark:text-gray-400 hover:text-sky-600 dark:hover:text-sky-400 hover:border-sky-200 dark:hover:border-sky-800 transition-all shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                            </button>
                                            <button type="button" id="blockManagerNextBtn" onclick="navigateBlock(1)" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white dark:bg-gray-700 border border-slate-100 dark:border-gray-600 text-slate-400 dark:text-gray-400 hover:text-sky-600 dark:hover:text-sky-400 hover:border-sky-200 dark:hover:border-sky-800 transition-all shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 flex-1" id="blockManagerGrid">
                                        <!-- Candidatos -->
                                        <div class="flex flex-col h-full transition-all duration-300" id="colCandidatos">
                                            <div class="flex justify-between items-center px-2 mb-4">
                                                <h4 class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">Candidatos Disponibles</h4>
                                                <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-slate-100 dark:bg-gray-700 text-slate-500 dark:text-gray-400 uppercase tracking-widest">Lista de Espera</span>
                                            </div>
                                            <div class="w-full h-[320px] rounded-[24px] border border-slate-100 dark:border-gray-700 bg-slate-50/30 dark:bg-gray-800/30 p-4 transition-all flex flex-col">
                                                <ul id="blockRequestsList" class="space-y-3 custom-scrollbar overflow-y-auto flex-1 pr-1">
                                                    <!-- Llenado por JS -->
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- Estado de Confirmación -->
                                        <div class="flex flex-col h-full transition-all duration-300" id="colEstado">
                                            <div class="flex justify-between items-center px-2 mb-4">
                                                <h4 class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">Estado de Cita</h4>
                                                <span id="blockConfirmationBadge" class="text-[9px] font-black px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800 uppercase tracking-widest hidden">Confirmado</span>
                                            </div>
                                            <div id="blockConfirmedContainer" class="w-full h-[320px] rounded-[24px] border-2 border-dashed border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-gray-800/50 flex flex-col items-center justify-center p-8 text-center transition-all">
                                                <!-- Llenado por JS -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    // El script permanece igual (no necesita cambios para dark mode)
    document.addEventListener('DOMContentLoaded', function () {
        const CONFIG = {
            endpoints: {
                json: (id) => `{{ url('citas') }}/${id}/json`,
                prioridad: (id) => `{{ url('citas') }}/${id}/prioridad`,
                rechazar: (id) => `{{ url('citas') }}/${id}/rechazar`,
                aceptar: (id) => `{{ url('citas') }}/${id}/aceptar`,
                proponer: (id) => `{{ url('citas') }}/${id}/proponer`,
                quitarPropuesta: (id) => `{{ url('citas') }}/${id}/quitar-propuesta`,
                enviarPropuesta: (id) => `{{ url('citas') }}/${id}/enviar-propuesta`,
                realizar: (id) => `{{ url('citas') }}/${id}/realizar`,
                noAsistio: (id) => `{{ url('citas') }}/${id}/no-asistio`,
                posponer: (id) => `{{ url('citas') }}/${id}/posponer`,
                cancelar: (id) => `{{ url('citas') }}/${id}/cancelar-psicologo`,
                pendingList: '{{ route('agenda.pending.list') }}',
                dailyCitas: '{{ route('agenda.daily_citas') }}'
            },
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        };

        let state = {
            currentCitaId: null,
            currentCitaIndex: -1,
            pendingCitaIds: [],
            currentBlockLabel: null,
            currentBlockDate: null
        };

        const Utils = {
            escapeHtml: (str) => {
                const div = document.createElement('div');
                div.textContent = str || '';
                return div.innerHTML;
            },
            formatAmPm: (label) => {
                if (!label) return 'En espera';
                return label.replace(/(\d{1,2}):(\d{2})/g, (m, h, min) => {
                    let hh = parseInt(h);
                    return `${hh % 12 || 12}:${min} ${hh >= 12 ? 'PM' : 'AM'}`;
                });
            },
            normalize: (l) => {
                let s = (l || '').trim().toLowerCase()
                    .replace(/(\d{1,2}):(\d{2})\s*(am|pm)\b/g, (m, h, min, ampm) => {
                        let hh = parseInt(h);
                        if (ampm === 'pm' && hh < 12) hh += 12;
                        if (ampm === 'am' && hh === 12) hh = 0;
                        return `${hh < 10 ? '0' : ''}${hh}:${min}`;
                    });
                return s.replace(/(\d{1,2}:\d{2}):\d{2}/g, '$1').replace(/\s*[-–—]\s*/g, '-').replace(/\s+/g, ' ').replace(/(^|\s|-)(\d):/g, '$10$2:');
            },
            api: (url, method = 'GET', body = null) => {
                const options = {
                    method,
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfToken }
                };
                if (body) options.body = JSON.stringify(body);
                return fetch(url, options).then(res => res.ok ? res.json() : Promise.reject(res));
            },
            confirm: (title, text, options = {}) => {
                return new Promise((resolve) => {
                    const m = document.getElementById('confirmModal');
                    const t = document.getElementById('confirmTitle');
                    const p = document.getElementById('confirmText');
                    const y = document.getElementById('confirmYesBtn');
                    const n = document.getElementById('confirmNoBtn');
                    const iconBox = document.getElementById('confirmIconBox');
                    const iconSvg = document.getElementById('confirmIconSvg');
                    const inputArea = document.getElementById('confirmInputArea');
                    const inputField = document.getElementById('confirmInputField');

                    if (title) t.innerText = title;
                    if (text) p.innerText = text;

                    const btnColor = options.btnColor || 'bg-sky-700 hover:bg-sky-800 shadow-sky-200';
                    y.className = `flex-1 py-4 px-6 ${btnColor} text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg transition-all`;

                    const iconColor = options.iconColor || 'bg-sky-50 text-sky-700';
                    iconBox.className = `w-16 h-16 ${iconColor} rounded-2xl flex items-center justify-center mb-6 mx-auto`;
                    if (iconSvg && options.icon) iconSvg.innerHTML = options.icon;

                    if (options.inputLabel) {
                        document.getElementById('confirmInputLabel').textContent = options.inputLabel;
                        inputField.value = options.inputDefault || '';
                        inputArea.classList.remove('hidden');
                    } else {
                        inputArea.classList.add('hidden');
                        inputField.value = '';
                    }

                    m.classList.remove('hidden');
                    m.classList.add('flex');

                    const cleanup = (val) => {
                        m.classList.add('hidden');
                        m.classList.remove('flex');
                        y.onclick = null;
                        n.onclick = null;
                        inputField.classList.remove('border-rose-500', 'ring-rose-200');
                        resolve(val);
                    };

                    y.onclick = () => {
                        if (options.inputLabel && options.requireInput && !inputField.value.trim()) {
                            inputField.classList.add('border-rose-500', 'ring-rose-200');
                            inputField.focus();
                            return;
                        }
                        cleanup(options.inputLabel ? inputField.value.trim() : true);
                    };
                    n.onclick = () => cleanup(false);
                });
            }
        };

        function openCitaModal(id) {
            state.pendingCitaIds = Array.from(document.querySelectorAll('.pending-item')).map(i => i.dataset.citaId);
            state.currentCitaId = id;
            state.currentCitaIndex = state.pendingCitaIds.indexOf(String(id));

            updateCitaNavButtons();
            Utils.api(CONFIG.endpoints.json(id))
                .then(renderCitaDetails)
                .catch(err => { console.error(err); AppModal.alert('Error', 'Error al cargar la cita.'); });
        }

        function renderCitaDetails(cita) {
            const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '-'; };

            set('citaPacienteName', cita.paciente);
            set('citaPsicologoName', 'Psicólogo: ' + (cita.psicologo || '-'));
            set('citaFechaSolicitud', cita.fecha_solicitud_iso ? new Date(cita.fecha_solicitud_iso).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true }) : cita.fecha_solicitud);
            set('citaFechaConfirmada', cita.fecha_confirmada || 'Pendiente');
            set('citaBloqueConfirmado', Utils.formatAmPm(cita.bloque_confirmado));
            set('citaEstado', cita.estado);
            set('citaMotivo', cita.motivo);
            set('citaBloqueTag', (cita.estado || '').toUpperCase());

            const pMap = { baja: 'bg-emerald-500', media: 'bg-sky-500', alta: 'bg-amber-500', 'crítica': 'bg-rose-500' };
            const dot = document.getElementById('citaPrioridadDot');
            if (dot) dot.className = `h-2 w-2 rounded-full ${pMap[cita.prioridad] || 'bg-indigo-500'}`;
            set('citaPrioridadTexto', (cita.prioridad || 'Media').charAt(0).toUpperCase() + (cita.prioridad || 'media').slice(1));

            document.querySelectorAll('.prioridad-radio').forEach(r => r.checked = r.value === cita.prioridad);
            document.getElementById('prioridadMensaje')?.classList.add('hidden');

            if (cita.paciente_horario) {
                document.getElementById('citaHorarioContainer').classList.remove('hidden');
                document.getElementById('citaHorarioLink').href = cita.paciente_horario;
            } else {
                document.getElementById('citaHorarioContainer').classList.add('hidden');
            }

            // Manejo de la foto de perfil en el avatar
            const avatarContainer = document.getElementById('citaAvatarContainer');
            const avatarImg = document.getElementById('citaAvatarImg');
            const avatarText = document.getElementById('citaAvatarText');

            if (avatarContainer && avatarImg && avatarText) {
                avatarContainer.classList.add('open-patient-modal', 'cursor-pointer');
                avatarContainer.dataset.patientType = 'manual';
                avatarContainer.dataset.patientJsonUrl = CONFIG.endpoints.json(cita.id);

                if (cita.paciente_foto && cita.paciente_foto !== '') {
                    avatarImg.src = cita.paciente_foto;
                    avatarImg.classList.remove('hidden');
                    avatarText.classList.add('hidden');
                    avatarContainer.classList.remove('bg-gradient-to-br', 'from-sky-500', 'to-blue-600');
                    avatarContainer.style.background = 'transparent';
                } else {
                    avatarImg.classList.add('hidden');
                    avatarText.classList.remove('hidden');
                    avatarContainer.classList.add('bg-gradient-to-br', 'from-sky-500', 'to-blue-600');
                    avatarContainer.style.background = '';
                    const initials = (cita.paciente || 'P').split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
                    avatarText.textContent = initials;
                }
            }

            const cont = document.getElementById('citaBloquesSugeridos');
            if (cont) {
                cont.innerHTML = '';
                const raw = cita.bloques_sugeridos || '';

                let excepcionesStr = '';
                let horariosStr = raw;
                if (raw.includes('|')) {
                    const parts = raw.split('|');
                    excepcionesStr = parts[0].replace('Días exceptuados:', '').trim();
                    horariosStr = parts[1].replace('Horarios:', '').trim();
                }

                const list = horariosStr.split(';').map(s => s.trim()).filter(Boolean);

                if (!list.length) {
                    const empty = document.createElement('span');
                    empty.className = 'text-[10px] text-slate-400 italic';
                    empty.textContent = 'No hay horarios sugeridos';
                    cont.appendChild(empty);
                } else {
                    list.forEach(txt => {
                        const chip = document.createElement('span');
                        chip.className = 'px-3 py-1 bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300 rounded-lg text-xs font-semibold';
                        chip.textContent = txt;
                        cont.appendChild(chip);
                    });
                }
            }

            const propInfo = document.getElementById('citaPropuestaInfo');
            const propAcciones = document.getElementById('citaPropuestaAcciones');
            const enviarBtn = document.getElementById('enviarPropuestaBtn');

            if (propInfo && propAcciones) {
                propInfo.className = 'p-4 rounded-2xl border text-xs font-semibold ';
                propInfo.classList.add('hidden');
                propAcciones.classList.add('hidden');

                const propList = (cita.bloques_propuestos || '').split(/[;,]/).map(s => s.trim()).filter(Boolean);

                if (cita.propuesta_estado === 'pendiente') {
                    propInfo.classList.remove('hidden');
                    propInfo.classList.add('bg-yellow-50', 'dark:bg-yellow-950/20', 'border-yellow-100', 'dark:border-yellow-900/40', 'text-yellow-850', 'dark:text-yellow-350');
                    propInfo.innerHTML = `Propuesta enviada al paciente. Esperando su respuesta.<br><strong>Bloques propuestos:</strong> ${propList.map(Utils.formatAmPm).join(', ')}`;
                } else if (cita.propuesta_estado === 'cualquier_dia') {
                    propInfo.classList.remove('hidden');
                    propInfo.classList.add('bg-emerald-50', 'dark:bg-emerald-950/20', 'border-emerald-100', 'dark:border-emerald-900/40', 'text-emerald-850', 'dark:text-emerald-350');
                    propInfo.innerHTML = `El paciente respondió: <strong>"Cualquier día está bien"</strong>. Puedes agendar esta cita en cualquier bloque de tu agenda.`;
                } else if (cita.propuesta_estado === 'sugerencia_aceptada') {
                    propInfo.classList.remove('hidden');
                    propInfo.classList.add('bg-sky-50', 'dark:bg-sky-950/20', 'border-sky-100', 'dark:border-sky-900/40', 'text-sky-850', 'dark:text-sky-350');
                    let bloqueSel = cita.propuesta_bloque_seleccionado || 'Sugerencia';
                    propInfo.innerHTML = `El paciente aceptó la propuesta para el bloque: <strong>${Utils.formatAmPm(bloqueSel)}</strong>.<br>Para confirmarla definitivamente, arrastra al paciente o confírmalo en ese bloque.`;
                } else if (cita.propuesta_estado === 'rechazada') {
                    propInfo.classList.remove('hidden');
                    propInfo.classList.add('bg-rose-50', 'dark:bg-rose-950/20', 'border-rose-100', 'dark:border-rose-900/40', 'text-rose-850', 'dark:text-rose-350');
                    let rejectedReason = cita.motivo_rechazo_propuesta ? `<strong>Motivo:</strong> ${cita.motivo_rechazo_propuesta}<br>` : '';
                    let rejectedBlocks = propList.length > 0 ? `<strong>Bloques descartados:</strong> ${propList.map(Utils.formatAmPm).join(', ')}<br>` : '';
                    propInfo.innerHTML = `El paciente rechazó la propuesta de horario.<br>${rejectedReason}${rejectedBlocks}La cita permanece pendiente en cola.`;
                } else {
                    if (propList.length > 0) {
                        propInfo.classList.remove('hidden');
                        propInfo.classList.add('bg-slate-50', 'dark:bg-gray-700/50', 'border-slate-100', 'dark:border-gray-700', 'text-slate-600', 'dark:text-gray-300');
                        propInfo.innerHTML = `Tienes bloques propuestos para esta cita: <strong>${propList.map(Utils.formatAmPm).join(', ')}</strong>. Puedes presionar el botón de abajo para enviar la propuesta formalmente al paciente.`;

                        propAcciones.classList.remove('hidden');
                        if (enviarBtn) {
                            enviarBtn.onclick = () => window.enviarPropuesta(cita.id);
                        }
                    }
                }
            }

            const m = document.getElementById('citaDetailsModal');
            if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
        }

        function updateCitaNavButtons() {
            const p = document.getElementById('prevCitaBtn'), n = document.getElementById('nextCitaBtn');
            if (p && n) { p.disabled = state.currentCitaIndex <= 0; n.disabled = state.currentCitaIndex < 0 || state.currentCitaIndex >= state.pendingCitaIds.length - 1; }
        }

        function openBlockManager(cell) {
            if (!cell) return;
            state.currentBlockLabel = cell.dataset.blockLabel;
            state.currentBlockDate = cell.dataset.blockDate;

            const title = document.getElementById('blockManagerTitle');
            if (title) {
                const parsedDate = new Date(state.currentBlockDate + 'T12:00:00');
                const dateStr = isNaN(parsedDate.getTime()) ? '' : parsedDate.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' });
                // Strip the day name from the block label to avoid duplication (e.g. "Miércoles 7:00 - 8:30" -> "7:00 - 8:30")
                const timeOnly = (state.currentBlockLabel || '').replace(/^[a-záéíóúñü]+\s+/i, '');
                title.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1) + ' · ' + Utils.formatAmPm(timeOnly);
            }

            renderBlockRequests(cell);

            // Mostrar el Block Manager con animación
            const mainView = document.getElementById('agendaMainView');
            const blockView = document.getElementById('agendaBlockManagerView');

            if (mainView && blockView && blockView.classList.contains('hidden')) {
                mainView.classList.add('opacity-0');
                setTimeout(() => {
                    mainView.classList.add('hidden');
                    blockView.classList.remove('hidden');
                    void blockView.offsetWidth; // force reflow
                    blockView.classList.remove('opacity-0');
                }, 300);
            }
        }

        window.closeBlockManager = function() {
            const mainView = document.getElementById('agendaMainView');
            const blockView = document.getElementById('agendaBlockManagerView');

            if (mainView && blockView) {
                blockView.classList.add('opacity-0');
                setTimeout(() => {
                    blockView.classList.add('hidden');
                    mainView.classList.remove('hidden');
                    void mainView.offsetWidth; // force reflow
                    mainView.classList.remove('opacity-0');
                }, 300);
            }
        };

        window.navigateBlock = function(dir) {
            let allButtons = Array.from(document.querySelectorAll('.block-slot-button'));
            if (!allButtons.length) return;

            // Deduplicate blocks by date and label
            let uniqueBlocksMap = new Map();
            allButtons.forEach(b => {
                const key = b.dataset.blockDate + '|' + b.dataset.blockLabel;
                if (!uniqueBlocksMap.has(key)) {
                    uniqueBlocksMap.set(key, b);
                }
            });
            let buttons = Array.from(uniqueBlocksMap.values());

            buttons.sort((a, b) => {
                const dateA = new Date(a.dataset.blockDate + 'T' + (a.dataset.blockTime || '00:00:00'));
                const dateB = new Date(b.dataset.blockDate + 'T' + (b.dataset.blockTime || '00:00:00'));
                return dateA.getTime() - dateB.getTime();
            });

            let currentIndex = buttons.findIndex(b =>
                b.dataset.blockLabel === state.currentBlockLabel &&
                b.dataset.blockDate === state.currentBlockDate
            );

            if (currentIndex === -1) currentIndex = 0;

            let nextIndex = currentIndex + dir;
            if (nextIndex < 0) nextIndex = buttons.length - 1;
            if (nextIndex >= buttons.length) nextIndex = 0;

            const nextCell = buttons[nextIndex];
            if (nextCell) {
                openBlockManager(nextCell);
            }
        };

        window.openDailyAgenda = function(cell, date) {
            if (!cell) return;

            const subtitle = document.getElementById('dailyAgendaSubtitle');
            const content = document.getElementById('dailyAgendaContent');
            if (!content) return;

            const parsedDate = new Date(date + 'T12:00:00');
            const dateFormatted = isNaN(parsedDate.getTime()) ? date : parsedDate.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

            if (subtitle) subtitle.textContent = dateFormatted;
            content.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 border-4 border-sky-100 border-t-sky-600 rounded-full animate-spin mb-4"></div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Cargando agenda...</p>
                </div>
            `;

            const m = document.getElementById('dailyAgendaModal');
            if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }

            const psicologoId = new URLSearchParams(window.location.search).get('psicologo_id') || '{{ $psicologoId }}';

            Utils.api(`${CONFIG.endpoints.dailyCitas}?fecha=${date}&psicologo_id=${psicologoId}`)
                .then(citas => {
                    content.innerHTML = '';
                    if (citas.length === 0) {
                        content.innerHTML = `
                            <div class="flex flex-col items-center justify-center py-12 text-center animate-in fade-in zoom-in duration-300">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Sin citas para este día</p>
                            </div>
                        `;
                    } else {
                        citas.forEach(cita => {
                            const div = document.createElement('div');
                            div.className = 'flex items-center justify-between p-4 rounded-[24px] border border-slate-100 bg-white hover:border-sky-200 hover:shadow-lg hover:shadow-sky-50/50 transition-all group animate-in slide-in-from-bottom-2 duration-300';

                            let badgeClass = 'bg-slate-50 text-slate-500 border-slate-100';
                            if (cita.estado === 'confirmada') badgeClass = 'bg-sky-50 text-sky-700 border-sky-100';
                            else if (cita.estado === 'realizada') badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                            else if (cita.estado === 'no_asistio') badgeClass = 'bg-rose-50 text-rose-700 border-rose-100';

                            div.innerHTML = `
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-700 font-black shadow-sm group-hover:scale-110 transition-transform">
                                        ${cita.hora !== 'S/H' ? cita.hora.split(':')[0] : '--'}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800 leading-none mb-1">${cita.paciente}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${cita.hora}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1.5 rounded-xl border text-[9px] font-black uppercase tracking-widest ${badgeClass}">
                                        ${cita.estado === 'no_asistio' ? 'Ausente' : cita.estado}
                                    </span>
                                </div>
                            `;
                            content.appendChild(div);
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    content.innerHTML = `<p class="text-center text-rose-500 text-xs font-bold py-8">Error al cargar citas.</p>`;
                });
        }

        function renderBlockRequests(cell) {
            const list = document.getElementById('blockRequestsList');
            const assignedList = document.getElementById('blockConfirmedContainer');
            const badge = document.getElementById('blockConfirmationBadge');
            
            const colCandidatos = document.getElementById('colCandidatos');
            const colEstado = document.getElementById('colEstado');

            list.innerHTML = ''; assignedList.innerHTML = '';
            const assignedPac = cell.dataset.assignedPaciente;
            const assignedId = cell.dataset.assignedCitaId;
            const assignedEstado = cell.dataset.assignedEstado;

            if (assignedPac) {
                if (colCandidatos) { colCandidatos.classList.add('hidden'); colCandidatos.classList.remove('lg:col-span-2'); }
                if (colEstado) { colEstado.classList.remove('hidden'); colEstado.classList.add('lg:col-span-2'); }

                if (badge) {
                    badge.classList.remove('hidden');
                    if (assignedEstado === 'realizada') {
                        badge.textContent = 'Realizada';
                        badge.className = 'px-3 py-1 rounded-xl bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-widest shadow-sm';
                    } else if (assignedEstado === 'no_asistio') {
                        badge.textContent = 'Ausente';
                        badge.className = 'px-3 py-1 rounded-xl bg-rose-100 text-rose-700 text-[10px] font-black uppercase tracking-widest shadow-sm';
                    } else {
                        badge.textContent = 'Confirmado';
                        badge.className = 'px-3 py-1 rounded-xl bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest shadow-sm';
                    }
                }

                let actionButtons = '';
                if (assignedEstado === 'confirmada') {
                    actionButtons = `
                        <div class="flex flex-wrap justify-center gap-3 mt-6">
                            <button type="button" id="btn-realizar-${assignedId}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-200 dark:shadow-emerald-900/30 transition-all uppercase tracking-wider active:scale-95">Realizada</button>
                            <button type="button" id="btn-no-asistio-${assignedId}" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow-md shadow-rose-200 dark:shadow-rose-900/30 transition-all uppercase tracking-wider active:scale-95">Ausente</button>
                            <button type="button" id="btn-posponer-${assignedId}" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-md shadow-amber-200 dark:shadow-amber-900/30 transition-all uppercase tracking-wider active:scale-95">Reagendar</button>
                            <button type="button" id="btn-cancelar-${assignedId}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-600 dark:text-gray-300 rounded-xl font-bold text-xs transition-all uppercase tracking-wider active:scale-95">Cancelar</button>
                        </div>
                    `;
                }

                const assignedHtml = `
                    <div class="w-16 h-16 rounded-full bg-sky-600 text-white flex items-center justify-center shadow-lg shadow-sky-900/20 mb-4 ring-4 ring-white dark:ring-gray-800">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <span class="text-[10px] font-black text-sky-600 dark:text-sky-400 uppercase tracking-widest mb-1">Paciente Asignado</span>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">${Utils.escapeHtml(assignedPac)}</h3>
                    ${actionButtons}
                `;
                assignedList.innerHTML = assignedHtml;

                if (assignedEstado === 'confirmada') {
                    document.getElementById(`btn-realizar-${assignedId}`).addEventListener('click', () => handleAction('realizar', assignedId));
                    document.getElementById(`btn-no-asistio-${assignedId}`).addEventListener('click', () => handleAction('no_asistio', assignedId));
                    document.getElementById(`btn-posponer-${assignedId}`).addEventListener('click', () => handleAction('posponer', assignedId));
                    document.getElementById(`btn-cancelar-${assignedId}`).addEventListener('click', () => handleAction('cancelar', assignedId));
                }
            } else {
                if (colEstado) { colEstado.classList.add('hidden'); colEstado.classList.remove('lg:col-span-2'); }
                if (colCandidatos) { colCandidatos.classList.remove('hidden'); colCandidatos.classList.add('lg:col-span-2'); }

                if (badge) { badge.classList.add('hidden'); }

                assignedList.innerHTML = `
                    <div class="w-16 h-16 bg-slate-100 dark:bg-gray-700 text-slate-300 dark:text-gray-500 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="text-sm font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest">No hay paciente confirmado aún.</p>
                `;

                const candidates = getCandidatesForBlock(state.currentBlockLabel, state.currentBlockDate);
                if (!candidates.length) {
                    list.innerHTML = `
                    <div class="flex-1 flex flex-col items-center justify-center h-full text-center py-12">
                        <div class="w-16 h-16 bg-slate-100 dark:bg-gray-700 text-slate-300 dark:text-gray-500 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest">Sin pacientes interesados</p>
                    </div>`;
                } else {
                    candidates.forEach(can => {
                        const li = document.createElement('li');
                        li.className = `group rounded-2xl border p-4 transition-all ${can.status === 'proposed' ? 'bg-sky-50/50 border-sky-100' : 'bg-white border-slate-100'}`;
                        li.innerHTML = `<div class="flex justify-between items-center gap-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    ${can.status === 'proposed' ? (can.propuestaEstado === 'pendiente' ? '<span class="text-[9px] font-black text-amber-600 uppercase">Contrapropuesta enviada, en espera de respuesta</span>' : '<span class="text-[9px] font-black text-sky-600 uppercase">Agregado al bloque (Pendiente de acción)</span>') : '<span class="text-[9px] font-black text-emerald-600 uppercase">Solicitado por el paciente</span>'}
                                    <span class="font-black text-slate-700">${Utils.escapeHtml(can.paciente)}</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button title="Aceptar" class="block-request-action-btn h-9 w-9 flex items-center justify-center rounded-xl border border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors" data-action="accept" data-cita-id="${can.citaId}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <button title="Rechazar" class="block-request-action-btn h-9 w-9 flex items-center justify-center rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors" data-action="reject" data-cita-id="${can.citaId}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <button title="Quitar sugerencia" class="block-request-action-btn h-9 w-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-500 hover:text-white transition-colors" data-action="remove_proposal" data-cita-id="${can.citaId}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>`;
                        list.appendChild(li);
                    });
                }
            }
        }

        function isBlockSuggested(sug, label, blockDate) {
            if (!sug) return false;
            const normLabel = Utils.normalize(label);
            const labelDayMatch = normLabel.match(/^([a-záéíóúñ]+)/);
            const labelDay = labelDayMatch ? labelDayMatch[1] : '';
            
            let labelTimes = [];
            const timesMatch = normLabel.match(/(\d{2}:\d{2})-(\d{2}:\d{2})/);
            if (timesMatch) {
                labelTimes = [timesMatch[1], timesMatch[2]];
            }

            let parts = sug.split('|');
            let excepcionesStr = '';
            let horariosStr = sug;
            if (parts.length > 1) {
                const leftPart = parts[0].trim();
                const rightPart = parts[1].trim();

                if (leftPart.toLowerCase().includes('exceptuados')) {
                    excepcionesStr = leftPart.replace(/D[íi]as exceptuados:/i, '').trim();
                }

                horariosStr = rightPart.replace(/^\s*Horarios\s*(propuestos)?\s*:\s*/i, '').trim();
            } else {
                horariosStr = sug.replace(/^\s*Horarios\s*(propuestos)?\s*:\s*/i, '').trim();
            }

            if (blockDate) {
                if (excepcionesStr) {
                    const excArray = excepcionesStr.split(',').map(s => s.trim());
                    if (excArray.includes(blockDate)) return false; 
                }

                const bd = new Date(blockDate + 'T00:00:00');
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const nextMonth = new Date(today);
                nextMonth.setMonth(today.getMonth() + 1);

                if (bd < today || bd > nextMonth) {
                    return false;
                }
            }

            const bloques = horariosStr.split(';').map(s => s.trim()).filter(Boolean);
            return bloques.some(b => {
                if (blockDate && b.includes(blockDate)) {
                    const mTimes = b.match(/(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)\s*[-–—]\s*(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)/i);
                    if (mTimes && labelTimes.length === 2) {
                        const toMinutes = (t) => {
                            const m = t.trim().match(/(\d{1,2}):(\d{2})\s*([aApP][mM])?/);
                            if (!m) return 0;
                            let h = parseInt(m[1]), min = parseInt(m[2]);
                            const ampm = (m[3] || '').toLowerCase();
                            if (ampm === 'pm' && h !== 12) h += 12;
                            if (ampm === 'am' && h === 12) h = 0;
                            return h * 60 + min;
                        };
                        const bStart = toMinutes(mTimes[1]);
                        const bEnd   = toMinutes(mTimes[2]);
                        const [lh1, lm1] = labelTimes[0].split(':').map(Number);
                        const [lh2, lm2] = labelTimes[1].split(':').map(Number);
                        const lStart = lh1 * 60 + lm1;
                        const lEnd   = lh2 * 60 + lm2;
                        return lStart < bEnd && lEnd > bStart;
                    }
                    return true;
                }

                const normB = Utils.normalize(b);
                if (!normB.includes(labelDay)) return false;

                const m = normB.match(/(\d{2}:\d{2})-(\d{2}:\d{2})/);
                if (m && labelTimes.length > 0) {
                    return (labelTimes[0] < m[2] && labelTimes[1] > m[1]);
                }
                return false;
            });
        }

        function getCandidatesForBlock(label, blockDate) {
            const normLabel = Utils.normalize(label);
            // Extract the day name from the normalized label (e.g., "lunes 07:00-08:00" -> "lunes")
            const labelDayMatch = normLabel.match(/^([a-záéíóúñ]+)/);
            const labelDay = labelDayMatch ? labelDayMatch[1] : '';
            // Extract times
            let labelTimes = [];
            const timesMatch = normLabel.match(/(\d{2}:\d{2})-(\d{2}:\d{2})/);
            if (timesMatch) {
                labelTimes = [timesMatch[1], timesMatch[2]];
            }

            return Array.from(document.querySelectorAll('.pending-item')).filter(i => {
                const sug = i.dataset.bloquesSugeridos || '';
                const pro = i.dataset.bloquesPropuestos || '';

                let matchesSug = isBlockSuggested(sug, label, blockDate);

                let matchesPro = false;
                let isProposed = false;
                const propEstado = i.dataset.propuestaEstado || '';
                
                if (pro) {
                    const parsedPro = pro.split(';').map(p => p.trim());
                    matchesPro = parsedPro.some(b => {
                        const parts = b.split('|');
                        const isMatch = (parts.length === 2 && parts[0] === blockDate && Utils.normalize(parts[1]) === normLabel) || (Utils.normalize(b) === normLabel);
                        
                        if (isMatch) {
                            if (propEstado !== 'rechazada') {
                                isProposed = true;
                            }
                            return true;
                        }
                        return false;
                    });
                }

                return matchesSug || matchesPro;
            }).map(i => {
                const pro = i.dataset.bloquesPropuestos || '';
                const propEstado = i.dataset.propuestaEstado || '';
                let status = 'interested';
                
                if (pro && propEstado !== 'rechazada') {
                    pro.split(';').forEach(b => {
                        const parts = b.split('|');
                        if (parts.length === 2 && parts[0] === blockDate && Utils.normalize(parts[1]) === normLabel) status = 'proposed';
                        else if (Utils.normalize(b) === normLabel) status = 'proposed';
                    });
                }
                return {
                    citaId: i.dataset.citaId,
                    paciente: i.dataset.patientName || 'Paciente',
                    status: status,
                    propuestaEstado: propEstado
                };
            });
        }

        function handleAction(action, id, targetBtn = null) {
            let endpoint = '';
            let body = null;
            let successMsg = '';

            const normalizedAction = action === 'complete' ? 'realizar' :
                                    (action === 'cancel_confirmada' ? 'cancelar' : action);

            if (targetBtn) {
                targetBtn.disabled = true;
                targetBtn.classList.add('opacity-50', 'cursor-wait');
                if (!targetBtn.innerHTML.includes('svg')) {
                    targetBtn.innerText = '...';
                }
            }

            switch(normalizedAction) {
                case 'reject':
                    Utils.confirm('Rechazar solicitud', 'Por favor, indica el motivo del rechazo.', {
                        iconColor: 'bg-rose-50 text-rose-600',
                        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />',
                        btnColor: 'bg-rose-600 hover:bg-rose-700 shadow-rose-100',
                        btnText: 'Rechazar',
                        inputLabel: 'Motivo',
                        inputDefault: 'Lo siento, no puedo atenderte en este momento.'
                    }).then(reason => {
                        if (!reason) {
                            if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                            return;
                        }
                        const body = { motivo_rechazo: reason === true ? 'Lo siento, no puedo atenderte en este momento.' : reason };

                        Utils.api(CONFIG.endpoints.rechazar(id), 'PATCH', body)
                            .then(res => {
                                if (targetBtn) {
                                    const item = targetBtn.closest('.block-request-item');
                                    if (item) item.style.display = 'none';
                                }
                                if(typeof showToast === 'function') showToast(res.message || 'Solicitud rechazada.', 'success');
                                refreshAll();
                            })
                            .catch(err => {
                                console.error(err);
                                if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                AppModal.alert('Error', 'Error al rechazar la solicitud.');
                            });
                    });
                    return; // Retornar porque la petición se maneja de forma asíncrona dentro de la promesa
                case 'accept':
                    const timeMatch = state.currentBlockLabel ? state.currentBlockLabel.match(/(\d{1,2}:\d{2})/) : null;
                    const timeStr = timeMatch ? timeMatch[1] : '00:00';
                    const selectedDateTime = state.currentBlockDate ? new Date(state.currentBlockDate + 'T' + timeStr + ':00') : new Date();
                    const now = new Date();

                    const citaEl_accept = document.querySelector(`li[data-cita-id="${id}"]`);
                    const isManualAccept = citaEl_accept && citaEl_accept.dataset.isManual === '1';

                    if (selectedDateTime < now && !isManualAccept) {
                        AppModal.alert('Error', 'No puedes agendar citas en fechas u horas pasadas.');
                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                        return;
                    }

                    const existingCandidatesAccept = getCandidatesForBlock(state.currentBlockLabel, state.currentBlockDate);
                    if (existingCandidatesAccept.some(c => c.propuestaEstado === 'pendiente' && c.citaId !== id.toString())) {
                        AppModal.alert('Acción no permitida', 'Hay una contrapropuesta enviada en este bloque en espera de respuesta. No puedes confirmar a otro paciente hasta que se resuelva.');
                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                        return;
                    }

                    // Validar si el bloque está permitido según la propuesta y sugerencias
                    Utils.api(CONFIG.endpoints.json(id))
                        .then(cita => {
                            const sugResult = isBlockSuggested(cita.bloques_sugeridos, state.currentBlockLabel, state.currentBlockDate);

                            let caminoA = false; // Bloque sugerido por el paciente

                            if (sugResult) {
                                caminoA = true;
                            } else if (cita.propuesta_estado === 'cualquier_dia') {
                                caminoA = true;
                            } else if (cita.propuesta_estado === 'aceptada') {
                                // El paciente ya aceptó la contrapropuesta previa
                                caminoA = true;
                            } else if (!cita.bloques_sugeridos) {
                                // Cita agregada manualmente (sin bloques sugeridos), no requiere contrapropuesta
                                caminoA = true;
                            }

                            if (caminoA) {
                                // CAMINO A: Confirmar directamente
                                const acceptBody = {
                                    fecha: state.currentBlockDate || new Date().toISOString().split('T')[0],
                                    hora: state.currentBlockLabel.match(/(\d{1,2}:\d{2})/)?.[1],
                                    bloque: state.currentBlockLabel
                                };

                                Utils.api(CONFIG.endpoints.aceptar(id), 'PATCH', acceptBody)
                                    .then(res => {
                                        if (res.status === 'error') {
                                            if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                            AppModal.alert('Error', res.message || 'Error al confirmar la cita');
                                            return;
                                        }
                                        const cells = document.querySelectorAll(`.block-slot-button[data-block-label="${state.currentBlockLabel}"][data-block-date="${state.currentBlockDate}"]`);
                                        cells.forEach(cell => {
                                            cell.dataset.assignedBlock = 'true';
                                            cell.dataset.assignedPaciente = res.paciente || 'Paciente';
                                            cell.dataset.assignedCitaId = res.cita_id || id;
                                            cell.dataset.assignedEstado = 'confirmada';
                                            const blockView = document.getElementById('agendaBlockManagerView');
                                            if (blockView && !blockView.classList.contains('hidden') && state.currentBlockLabel === cell.dataset.blockLabel) {
                                                renderBlockRequests(cell);
                                            }
                                        });
                                        AppModal.alert('Éxito', res.message || 'Cita confirmada exitosamente');
                                        refreshAll();
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                        if (err instanceof Response) {
                                            err.json().then(data => {
                                                AppModal.alert('Error', data.message || 'Error al procesar la acción.');
                                            }).catch(() => {
                                                AppModal.alert('Error', 'Error al procesar la acción.');
                                            });
                                        } else {
                                            AppModal.alert('Error', 'Error al procesar la acción.');
                                        }
                                    });
                            } else {
                                // CAMINO B: El bloque NO fue sugerido por el paciente
                                // Verificar si ya hay una propuesta pendiente
                                if (cita.propuesta_estado === 'pendiente') {
                                    AppModal.alert('Propuesta pendiente', 'Ya se envió una contrapropuesta al paciente y aún no ha respondido. Debes esperar su respuesta antes de enviar otra.');
                                    if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                    return;
                                }

                                Utils.confirm(
                                    'Contrapropuesta de horario',
                                    'El paciente no solicitó este bloque de horario. ¿Deseas enviarle una contrapropuesta para este bloque? El paciente deberá aceptarla antes de que la cita se confirme.',
                                    {
                                        iconColor: 'bg-sky-50 text-sky-600',
                                        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                                        btnColor: 'bg-sky-600 hover:bg-sky-700 shadow-sky-100',
                                        btnText: 'Enviar contrapropuesta'
                                    }
                                ).then(confirmed => {
                                    if (!confirmed) {
                                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                        return;
                                    }

                                    // Enviar directamente la propuesta (el paciente ya fue propuesto al agregarlo al bloque)
                                    Utils.api(CONFIG.endpoints.enviarPropuesta(id), 'PATCH', {
                                        fecha: state.currentBlockDate,
                                        bloque: state.currentBlockLabel
                                    }).then(res => {
                                        if (res.status === 'error') throw new Error(res.message);
                                        if(typeof showToast === 'function') showToast(res.message || 'La contrapropuesta fue enviada al paciente. Esperando su respuesta.', 'success');
                                        refreshAll();
                                    }).catch(err => {
                                        console.error(err);
                                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                        if (err instanceof Error) {
                                            AppModal.alert('Error', err.message);
                                        } else if (err instanceof Response) {
                                            err.json().then(data => {
                                                AppModal.alert('Error', data.message || 'Error al enviar la contrapropuesta.');
                                            }).catch(() => {
                                                AppModal.alert('Error', 'Error al enviar la contrapropuesta.');
                                            });
                                        } else {
                                            AppModal.alert('Error', 'Error al enviar la contrapropuesta.');
                                        }
                                    });
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                            AppModal.alert('Error', 'Error al validar el estado de la cita.');
                        });
                    return;
                case 'propose':
                    const proposeTimeMatch = state.currentBlockLabel ? state.currentBlockLabel.match(/(\d{1,2}:\d{2})/) : null;
                    const proposeTimeStr = proposeTimeMatch ? proposeTimeMatch[1] : '00:00';
                    const proposeDateTime = state.currentBlockDate ? new Date(state.currentBlockDate + 'T' + proposeTimeStr + ':00') : new Date();
                    const proposeNow = new Date();

                    if (proposeDateTime < proposeNow) {
                        AppModal.alert('Error', 'No puedes sugerir bloques de horario en fechas u horas pasadas.');
                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                        return;
                    }

                    // Validar que el bloque no tenga ya una cita confirmada
                    const currentCell = document.querySelector(`.block-slot-button[data-block-label="${state.currentBlockLabel}"][data-block-date="${state.currentBlockDate}"]`);
                    if (currentCell && currentCell.dataset.assignedBlock === 'true') {
                        AppModal.alert('Bloque ocupado', 'Este bloque ya tiene una cita confirmada. No puedes sugerir más pacientes en un horario ocupado.');
                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                        return;
                    }

                    const existingCandidates = getCandidatesForBlock(state.currentBlockLabel, state.currentBlockDate);
                    if (existingCandidates.some(c => c.citaId == id)) {
                        AppModal.alert('Acción no permitida', 'Este paciente ya se encuentra propuesto en este bloque.');
                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                        return;
                    }

                    // Validar máximo 10 candidatos por bloque
                    if (existingCandidates.length >= 10) {
                        AppModal.alert('Límite alcanzado', 'Este bloque ya tiene 10 solicitudes. Debes liberar espacio quitando pacientes de la lista antes de sugerir otro.');
                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                        return;
                    }

                    if (existingCandidates.some(c => c.status === 'proposed' && c.propuestaEstado === 'pendiente')) {
                        AppModal.alert('Acción no permitida', 'Ya has enviado una propuesta para este horario a un paciente. Por favor, espera su respuesta o cancela la propuesta actual antes de enviar otra.');
                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                        return;
                    }
                    endpoint = CONFIG.endpoints.proponer(id);
                    body = { fecha: state.currentBlockDate, bloque: state.currentBlockLabel };
                    break;
                case 'remove_proposal':
                    endpoint = CONFIG.endpoints.quitarPropuesta(id); body = { fecha: state.currentBlockDate, bloque: state.currentBlockLabel };
                    if (targetBtn) {
                        const item = targetBtn.closest('.block-request-item');
                        if (item) item.style.display = 'none';
                    }
                    break;
                case 'realizar':
                    Utils.confirm(
                        '¿Registrar evolución de la cita?',
                        'Al confirmar, serás redirigido a la creación de la nota de evolución clínica para completar esta cita.',
                        {
                            iconColor: 'bg-sky-50 text-sky-700',
                            icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                            btnColor: 'bg-sky-700 hover:bg-sky-800 shadow-sky-200'
                        }
                    ).then(confirmed => {
                        if (!confirmed) {
                            if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                            return;
                        }
                        Utils.api(CONFIG.endpoints.realizar(id), 'PATCH', {})
                            .then(res => {
                                clearCellAssignment(id);
                                window.location.href = res.redirect_url || `{{ url('citas') }}/${id}/editar-nota`;
                            })
                            .catch(err => {
                                console.error(err);
                                if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                if (err && typeof err.json === 'function') {
                                    err.json().then(data => {
                                        if (data.redirect_template) {
                                            AppModal.show('Atención', data.message || 'Debe activar su Esquema General para el historial clínico.', {
                                                type: 'alert',
                                                btnText: 'IR ALLÁ'
                                            }).then(() => {
                                                window.location.href = '{{ route("plantillas-globales.index") }}';
                                            });
                                        } else if (data.is_warning) {
                                            AppModal.show('Atención', data.message, { type: 'alert', intent: 'warning' });
                                        } else {
                                            AppModal.alert('Error', data.message || 'Error al procesar la cita.');
                                        }
                                    }).catch(() => {
                                        AppModal.alert('Error', 'Error al procesar la cita.');
                                    });
                                } else {
                                    AppModal.alert('Error', 'Error al procesar la cita.');
                                }
                            });
                    });
                    return;

                case 'no_asistio':
                    Utils.confirm(
                        '¿Marcar al paciente como ausente?',
                        'Se registrará que el paciente no asistió a esta sesión y se procesarán las penalizaciones correspondientes.',
                        {
                            iconColor: 'bg-amber-50 text-amber-600',
                            icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
                            btnColor: 'bg-amber-500 hover:bg-amber-600 shadow-amber-100'
                        }
                    ).then(confirmed => {
                        if (!confirmed) {
                            if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                            return;
                        }
                        closeModals();
                        Utils.api(CONFIG.endpoints.noAsistio(id), 'PATCH', {})
                            .then(res => {
                                if (res.status === 'error') throw new Error(res.message);
                                clearCellAssignment(id);
                                if(typeof showToast === 'function') showToast(res.message || 'El paciente ha sido marcado como ausente.', 'success');
                                refreshAll();
                                setTimeout(() => { window.location.reload(); }, 500);
                            })
                            .catch(err => {
                                console.error(err);
                                if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                if (err && typeof err.json === 'function') {
                                    err.json().then(data => {
                                        if (data.is_warning) {
                                            AppModal.show('Atención', data.message, { type: 'alert', intent: 'warning' });
                                        } else {
                                            AppModal.alert('Error', data.message || 'Error al procesar la cita.');
                                        }
                                    }).catch(() => {
                                        AppModal.alert('Error', 'Error al procesar la cita.');
                                    });
                                } else if (err instanceof Error) {
                                    AppModal.alert('Error', err.message);
                                } else {
                                    AppModal.alert('Error', 'Error al registrar la inasistencia.');
                                }
                             });
                     });
                     return;
                 case 'posponer':
                    const actionTimeMatchPosp = state.currentBlockLabel ? Utils.normalize(state.currentBlockLabel).match(/(\d{1,2}:\d{2})/) : null;
                    const actionTimeStrPosp = actionTimeMatchPosp ? actionTimeMatchPosp[1] : '00:00';
                    const actionDateTimePosp = state.currentBlockDate ? new Date(state.currentBlockDate + 'T' + actionTimeStrPosp + ':00') : new Date();
                    const currentNowPosp = new Date();

                    if (actionDateTimePosp < currentNowPosp) {
                        AppModal.show('Acción no permitida', 'No puedes posponer una cita que ya ocurrió en el pasado. Debes registrar su estado actual (realizada o ausente).', { type: 'alert', intent: 'warning' });
                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                        return;
                    }

                    Utils.confirm(
                        '¿Desea reagendar esta cita?',
                        'La cita confirmada pasará a estado pendiente, descartando el día actual, para que puedas proponerle otra fecha o esperar a que el paciente sugiera una nueva.',
                        {
                            btnColor: 'bg-amber-600 hover:bg-amber-700 shadow-amber-100'
                        }
                    ).then(result => {
                        if (result === false) {
                            if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                            return;
                        }
                        closeModals();
                        Utils.api(CONFIG.endpoints.posponer(id), 'PATCH', {})
                            .then(res => {
                                if (res.status === 'error') throw new Error(res.message);
                                clearCellAssignment(id);
                                if(typeof showToast === 'function') showToast(res.message || 'La cita fue devuelta a pendientes.', 'success');
                                refreshAll();
                                setTimeout(() => { window.location.reload(); }, 500);
                            })
                            .catch(err => {
                                console.error(err);
                                if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                AppModal.alert('Error', err.message || 'Error al reagendar la cita.');
                            });
                    });
                    return;

                 case 'cancelar':
                     Utils.confirm(
                         '¿Cancelar esta cita?',
                         'Indica el motivo de la cancelación para notificar al paciente.',
                         {
                             iconColor: 'bg-rose-50 text-rose-600',
                             icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                             btnColor: 'bg-rose-600 hover:bg-rose-700 shadow-rose-100',
                             inputLabel: 'Motivo de cancelación (Obligatorio)',
                             inputDefault: 'Lo siento, surgió un inconveniente a última hora.',
                             requireInput: true
                         }
                     ).then(result => {
                         if (result === false) {
                             if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                             return;
                         }
                         const motivo = typeof result === 'string' ? result : 'Cancelado por el psicólogo.';
                         closeModals();
                         Utils.api(CONFIG.endpoints.cancelar(id), 'PATCH', { motivo_cancelacion: motivo })
                             .then(res => {
                                 clearCellAssignment(id);
                                 if(typeof showToast === 'function') showToast(res.message || 'La cita ha sido cancelada.', 'success');
                                 refreshAll();
                             })
                            .catch(err => {
                                console.error(err);
                                if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                AppModal.alert('Error', 'Error al cancelar la cita.');
                            });
                    });
                    return;
                default: console.error('Acción no reconocida:', action); return;
            }

            // enviarPropuesta se ejecuta desde la contrapropuesta del Camino B
            // ya no se necesita aquí como función separada dentro del switch

            Utils.api(endpoint, 'PATCH', body)
                .then(res => {
                    const cells = document.querySelectorAll(`.block-slot-button[data-block-label="${state.currentBlockLabel}"][data-block-date="${state.currentBlockDate}"]`);
                    cells.forEach(cell => {
                        if (normalizedAction === 'accept') {
                            cell.dataset.assignedBlock = 'true';
                            cell.dataset.assignedPaciente = res.paciente || 'Paciente';
                            cell.dataset.assignedCitaId = res.cita_id || id;
                            cell.dataset.assignedEstado = 'confirmada';
                        }
                    });

                    if (successMsg === 'redirect') {
                        window.location.href = `{{ url('historias') }}/${res.paciente_id}?tab=evolucion`;
                        return;
                    }
                    if (res.status === 'warning') {
                        AppModal.alert('Advertencia', res.message);
                    } else if (successMsg) {
                        if(typeof showToast === 'function') showToast(res.message || successMsg, 'success');
                    } else if (res.message) {
                        if(typeof showToast === 'function') showToast(res.message, 'success');
                    }
                    refreshAll();
                })
                .catch(err => {
                    console.error(err);
                    if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }

                    if (err instanceof Response) {
                        err.json().then(data => {
                            AppModal.alert('Error', data.message || 'Error al procesar la acción.');
                        }).catch(() => {
                            AppModal.alert('Error', 'Error al procesar la acción.');
                        });
                    } else {
                        AppModal.alert('Error', 'Error al procesar la acción.');
                    }
                });
        }

        function clearCellAssignment(citaId) {
            const cells = document.querySelectorAll(`.block-slot-button[data-block-label="${state.currentBlockLabel}"][data-block-date="${state.currentBlockDate}"]`);
            cells.forEach(cell => {
                if (cell.dataset.assignedCitaId == citaId) {
                    delete cell.dataset.assignedBlock;
                    delete cell.dataset.assignedPaciente;
                    delete cell.dataset.assignedCitaId;
                    delete cell.dataset.assignedEstado;
                    const blockView = document.getElementById('agendaBlockManagerView');
                    if (blockView && !blockView.classList.contains('hidden') && state.currentBlockLabel === cell.dataset.blockLabel) {
                        renderBlockRequests(cell);
                    }
                }
            });
        }

        let pendingListAbortController = null;

        function refreshAll(targetUrl = null) {
            let url = targetUrl;
            if (!url) {
                const params = new URLSearchParams(window.location.search);
                const q = document.getElementById('pendingFilter')?.value;
                const p = document.getElementById('priorityFilter')?.value;
                if (q) params.set('q', q); else params.delete('q');
                if (p) params.set('prioridad', p); else params.delete('prioridad');
                url = `${CONFIG.endpoints.pendingList}?${params.toString()}`;
            }

            if (pendingListAbortController) {
                pendingListAbortController.abort();
            }
            pendingListAbortController = new AbortController();

            const spinner = document.getElementById('searchSpinner');
            if(spinner) spinner.classList.remove('hidden');

            fetch(url, { 
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: pendingListAbortController.signal
            })
                .then(res => {
                    if(spinner) spinner.classList.add('hidden');
                    if (!res.ok) throw new Error('Error al recargar lista');
                    return res.text();
                })
                .then(html => {
                    const wrapper = document.getElementById('pendingListWrapper');
                    if (wrapper) wrapper.outerHTML = html;
                    applyFilters();
                    updateCalendarStatuses();
                    const blockView = document.getElementById('agendaBlockManagerView');
                    if (blockView && !blockView.classList.contains('hidden')) {
                        const cell = document.querySelector(`.block-slot-button[data-block-label="${state.currentBlockLabel}"][data-block-date="${state.currentBlockDate}"]`);
                        if (cell) renderBlockRequests(cell); else closeBlockManager();
                    }
                })
                .catch(err => {
                    const spinner = document.getElementById('searchSpinner');
                    if(spinner) spinner.classList.add('hidden');
                    if (err.name === 'AbortError') {
                        // Request was aborted, ignore
                        return;
                    }
                    console.error('Refresh error:', err);
                });
        }

        function updateCalendarStatuses() {
            document.querySelectorAll('.block-slot-button').forEach(btn => {
                const status = btn.querySelector('.block-slot-status');
                if (!status) return;

                if (btn.dataset.assignedBlock === 'true') {
                    const assignedEstado = btn.dataset.assignedEstado || 'confirmada';
                    let dotColor = 'bg-emerald-500 shadow-[0_0_2px_rgba(16,185,129,0.8)]';
                    if (assignedEstado === 'realizada') dotColor = 'bg-blue-500 shadow-[0_0_2px_rgba(59,130,246,0.8)]';
                    else if (assignedEstado === 'no_asistio') dotColor = 'bg-red-500 shadow-[0_0_2px_rgba(239,68,68,0.8)]';

                    btn.className = `block-slot-button w-full rounded-2xl border p-3 text-center transition-all drop-zone group bg-indigo-50 dark:bg-indigo-900/30 border-indigo-100 dark:border-indigo-800 text-indigo-700 dark:text-indigo-400 shadow-sm`;
                    
                    status.innerHTML = `<div class="flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full ${dotColor}"></span>
                                            <p class="text-[10px] font-black leading-tight truncate text-indigo-700 dark:text-indigo-400">${Utils.escapeHtml(btn.dataset.assignedPaciente)}</p>
                                        </div>`;
                } else {
                    const isActive = btn.dataset.blockActive === 'true';
                    
                    if (isActive) {
                        btn.className = `block-slot-button w-full rounded-2xl border p-3 text-center transition-all drop-zone group bg-white dark:bg-gray-700 border-slate-100 dark:border-gray-600 text-slate-600 dark:text-gray-300 hover:border-indigo-200 dark:hover:border-indigo-700 hover:shadow-md`;
                    } else {
                        btn.className = `block-slot-button w-full rounded-2xl border p-3 text-center transition-all drop-zone group bg-orange-50 dark:bg-orange-900/30 border-orange-100 dark:border-orange-800 text-orange-700 dark:text-orange-400 hover:border-orange-200 dark:hover:border-orange-700`;
                    }

                    const cands = getCandidatesForBlock(btn.dataset.blockLabel, btn.dataset.blockDate);
                    
                    // Preservar el mensaje de cita cancelada si existe en el DOM
                    let canceledHtml = '';
                    const canceledElement = status.querySelector('.mt-0\\.5');
                    if (canceledElement) {
                        canceledHtml = canceledElement.outerHTML;
                    }

                    const q = document.getElementById('pendingFilter')?.value?.trim();
                    const p = document.getElementById('priorityFilter')?.value;
                    const isSearchActive = (q || p);

                    let statusHtml = '';
                    const hasAssignedUI = status.innerHTML.includes('bg-emerald-500') || status.innerHTML.includes('bg-indigo-700') || status.innerHTML.includes('bg-blue-500') || status.innerHTML.includes('bg-red-500');

                    if (isSearchActive && !hasAssignedUI && status.innerHTML.trim() !== '') {
                        // Preservar el estado visual actual si hay una búsqueda activa
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = status.innerHTML;
                        const canc = tempDiv.querySelector('.mt-0\\.5');
                        if (canc) canc.remove();
                        statusHtml = tempDiv.innerHTML;
                    } else {
                        // Recalcular normalmente
                        statusHtml = cands.length ? `<div class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span><p class="text-[10px] font-black text-amber-600 dark:text-amber-400">${cands.length} Solic.</p></div>` : '<p class="text-[9px] font-bold text-slate-300 dark:text-gray-600 group-hover:text-slate-400 dark:group-hover:text-gray-400 uppercase">Libre</p>';
                    }
                    
                    status.innerHTML = statusHtml + canceledHtml;
                }
            });
        }

        function applyFilters() {
            const q = document.getElementById('pendingFilter')?.value?.toLowerCase();
            const p = document.getElementById('priorityFilter')?.value;
            let count = 0;
            document.querySelectorAll('.pending-item').forEach(i => {
                const pName = i.dataset.patientName || '';
                const pCedula = i.dataset.patientCedula || '';
                const pPrioridad = i.dataset.prioridad || '';
                const nameMatches = pName.toLowerCase().includes(q);
                const cedulaMatches = pCedula.toLowerCase().startsWith(q);
                const match = (!q || nameMatches || cedulaMatches) && (!p || pPrioridad === p);
                i.style.display = match ? '' : 'none';
                if (match) count++;
            });
            document.getElementById('pendingNoResultsMessage')?.classList.toggle('hidden', count > 0);
        }

        function closeModals() {
            ['citaDetailsModal', 'dailyAgendaModal'].forEach(id => {
                const m = document.getElementById(id);
                if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
            });
        }

        window.enviarPropuesta = function(citaId) {
            const btn = document.getElementById('enviarPropuestaBtn');
            if (btn) { btn.disabled = true; btn.classList.add('opacity-50', 'cursor-wait'); btn.innerText = 'Enviando...'; }

            Utils.api(CONFIG.endpoints.enviarPropuesta(citaId), 'PATCH', {})
                .then(res => {
                    if (res.status === 'error') throw new Error(res.message);
                    if(typeof showToast === 'function') showToast(res.message || 'La contrapropuesta fue enviada al paciente. Esperando su respuesta.', 'success');
                    closeModals();
                    refreshAll();
                })
                .catch(err => {
                    console.error(err);
                    if (btn) { btn.disabled = false; btn.classList.remove('opacity-50', 'cursor-wait'); btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg> ENVIAR PROPUESTA AL PACIENTE'; }
                    if (err instanceof Error) {
                        AppModal.alert('Error', err.message);
                    } else if (err instanceof Response) {
                        err.json().then(data => AppModal.alert('Error', data.message || 'Error al enviar.')).catch(() => AppModal.alert('Error', 'Error al enviar.'));
                    } else {
                        AppModal.alert('Error', 'Error al enviar la contrapropuesta.');
                    }
                });
        };

        document.addEventListener('click', (e) => {
            const pendingPaginationLink = e.target.closest('#pendingListWrapper nav a');
            if (pendingPaginationLink) {
                e.preventDefault();
                refreshAll(pendingPaginationLink.href);
                return;
            }

            const btn = e.target.closest('button, a');
            if (!btn) return;

            if (btn.classList.contains('detail-btn')) openCitaModal(btn.dataset.citaId);
            else if (btn.classList.contains('block-slot-button')) openBlockManager(btn);
            else if (btn.classList.contains('block-request-action-btn')) handleAction(btn.dataset.action, btn.dataset.citaId, btn);
            else if (['closeCitaModal', 'closeDailyAgendaModal'].includes(btn.id)) closeModals();
            else if (btn.id === 'prevCitaBtn') openCitaModal(state.pendingCitaIds[state.currentCitaIndex - 1]);
            else if (btn.id === 'nextCitaBtn') openCitaModal(state.pendingCitaIds[state.currentCitaIndex + 1]);
            else if (btn.classList.contains('agregar-manual-btn')) {
                const pacienteId = btn.dataset.pacienteId;
                btn.disabled = true;
                btn.textContent = '...';
                Utils.api('{{ route("agenda.crear_cita_manual") }}', 'POST', { paciente_id: pacienteId })
                    .then(res => {
                        if (res.success) refreshAll();
                        else { AppModal.alert('Error', res.message || 'Error'); btn.disabled = false; btn.textContent = 'Agregar'; }
                    })
                    .catch(() => { AppModal.alert('Error', 'Error'); btn.disabled = false; btn.textContent = 'Agregar'; });
            }
            else if (btn.id === 'guardarPrioridadBtn') {
                const sel = document.querySelector('.prioridad-radio:checked')?.value;
                if (!sel) return;
                Utils.api(CONFIG.endpoints.prioridad(state.currentCitaId), 'PATCH', { prioridad: sel }).then(() => {
                    document.getElementById('prioridadMensaje').textContent = 'Actualizado.';
                    document.getElementById('prioridadMensaje').classList.remove('hidden');
                    refreshAll();
                });
            }
        });

        let pendingSearchTimeout = null;
        document.getElementById('pendingFilter')?.addEventListener('input', (e) => {
            applyFilters();
            
            clearTimeout(pendingSearchTimeout);
            pendingSearchTimeout = setTimeout(() => {
                refreshAll();
            }, 250);
        });
        document.getElementById('priorityFilter')?.addEventListener('change', (e) => {
            applyFilters();
        });

        [document.getElementById('citaDetailsModal'), document.getElementById('dailyAgendaModal')].forEach(m => {
            m?.addEventListener('click', (e) => { if (e.target === m) closeModals(); });
        });

        let draggedId = null;
        document.addEventListener('dragstart', (e) => {
            const draggableEl = e.target.closest ? e.target.closest('.draggable-patient') : null;
            if (draggableEl) {
                draggedId = draggableEl.dataset.citaId;
                draggableEl.classList.add('opacity-50');
            }
        });
        document.addEventListener('dragend', (e) => {
            const draggableEl = e.target.closest ? e.target.closest('.draggable-patient') : null;
            if (draggableEl) {
                draggableEl.classList.remove('opacity-50');
            }
            draggedId = null;
        });
        document.addEventListener('dragover', (e) => {
            // Permitir drop en celdas del calendario (drop-zone) Y en el bloque manager abierto (blockRequestsList)
            if (e.target.closest('.drop-zone') || e.target.closest('#blockRequestsList') || e.target.closest('#blockConfirmedContainer')) {
                e.preventDefault();
                const zone = e.target.closest('.drop-zone');
                if (zone) zone.classList.add('ring-2', 'ring-sky-400', 'ring-offset-2');
            }
        });
        document.addEventListener('dragleave', (e) => {
            const zone = e.target.closest('.drop-zone');
            if (zone) zone.classList.remove('ring-2', 'ring-sky-400', 'ring-offset-2');
        });
        document.addEventListener('drop', (e) => {
            e.preventDefault();
            // Quitar highlight de todas las zonas
            document.querySelectorAll('.drop-zone').forEach(z => z.classList.remove('ring-2', 'ring-sky-400', 'ring-offset-2'));

            if (!draggedId) return;

            // Caso 1: Drop en celda del calendario (vista agenda cerrada)
            const zone = e.target.closest('.drop-zone');
            if (zone) {
                state.currentBlockLabel = zone.dataset.blockLabel;
                if (zone.dataset.blockDate) state.currentBlockDate = zone.dataset.blockDate;
                
                const draggedEl = document.querySelector(`li[data-cita-id="${draggedId}"]`);
                const currentDraggedId = draggedId;

                const timeMatch = state.currentBlockLabel ? state.currentBlockLabel.match(/(\d{1,2}:\d{2})/) : null;
                const timeStr = timeMatch ? timeMatch[1] : '00:00';
                const selectedDateTime = state.currentBlockDate ? new Date(state.currentBlockDate + 'T' + timeStr + ':00') : new Date();
                if (selectedDateTime < new Date() && !(draggedEl && draggedEl.dataset.isManual === '1')) {
                    AppModal.alert('Error', 'No puedes agendar citas en fechas u horas pasadas.');
                    draggedId = null;
                    return;
                }

                if (draggedEl && draggedEl.dataset.isManual === '1') {
                    showConfirmManualCita(() => {
                        handleAction('accept', currentDraggedId);
                    });
                } else if (draggedEl && !draggedEl.dataset.bloquesSugeridos) {
                    handleAction('accept', currentDraggedId);
                } else {
                    handleAction('propose', currentDraggedId);
                }
                
                draggedId = null;
                return;
            }

            // Caso 2: Drop en la lista del bloque manager abierto (blockRequestsList)
            const blockList = e.target.closest('#blockRequestsList') || e.target.closest('#blockConfirmedContainer');
            if (blockList && state.currentBlockLabel && state.currentBlockDate) {
                const draggedEl = document.querySelector(`li[data-cita-id="${draggedId}"]`);
                const currentDraggedId = draggedId;

                const timeMatch = state.currentBlockLabel ? state.currentBlockLabel.match(/(\d{1,2}:\d{2})/) : null;
                const timeStr = timeMatch ? timeMatch[1] : '00:00';
                const selectedDateTime = state.currentBlockDate ? new Date(state.currentBlockDate + 'T' + timeStr + ':00') : new Date();
                if (selectedDateTime < new Date() && !(draggedEl && draggedEl.dataset.isManual === '1')) {
                    AppModal.alert('Error', 'No puedes agendar citas en fechas u horas pasadas.');
                    draggedId = null;
                    return;
                }

                if (draggedEl && draggedEl.dataset.isManual === '1') {
                    showConfirmManualCita(() => {
                        handleAction('accept', currentDraggedId);
                    });
                } else if (draggedEl && !draggedEl.dataset.bloquesSugeridos) {
                    handleAction('accept', currentDraggedId);
                } else {
                    handleAction('propose', currentDraggedId);
                }
                
                draggedId = null;
                return;
            }

            draggedId = null;
        });

        function addSwipe(modal, onPrev, onNext) {
            if (!modal) return;
            let startX = 0, startY = 0;
            modal.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; startY = e.touches[0].clientY; }, { passive: true });
            modal.addEventListener('touchend', (e) => {
                let dx = e.changedTouches[0].clientX - startX, dy = e.changedTouches[0].clientY - startY;
                if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) { if (dx > 0) onPrev(); else onNext(); }
            }, { passive: true });
        }

        addSwipe(document.getElementById('citaDetailsModal'), () => openCitaModal(state.pendingCitaIds[state.currentCitaIndex - 1]), () => openCitaModal(state.pendingCitaIds[state.currentCitaIndex + 1]));
        addSwipe(document.getElementById('agendaBlockManagerView'), () => navigateBlock(-1), () => navigateBlock(1));

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft' || e.key === 'Left') {
                if (!document.getElementById('citaDetailsModal').classList.contains('hidden')) {
                    e.preventDefault();
                    openCitaModal(state.pendingCitaIds[state.currentCitaIndex - 1]);
                }
                else if (!document.getElementById('agendaBlockManagerView').classList.contains('hidden')) {
                    e.preventDefault();
                    navigateBlock(-1);
                }
            } else if (e.key === 'ArrowRight' || e.key === 'Right') {
                if (!document.getElementById('citaDetailsModal').classList.contains('hidden')) {
                    e.preventDefault();
                    openCitaModal(state.pendingCitaIds[state.currentCitaIndex + 1]);
                }
                else if (!document.getElementById('agendaBlockManagerView').classList.contains('hidden')) {
                    e.preventDefault();
                    navigateBlock(1);
                }
            }
        });

        const initialParams = new URLSearchParams(window.location.search);
        if (initialParams.has('q')) document.getElementById('pendingFilter').value = initialParams.get('q');
        if (initialParams.has('prioridad')) document.getElementById('priorityFilter').value = initialParams.get('prioridad');
        applyFilters();
        updateCalendarStatuses();

        // Exponer handleAction globalmente para que los botones dinámicos puedan llamarlo
        window.handleAction = handleAction;
        window.refreshAll = refreshAll;
        window.clearCellAssignment = clearCellAssignment;
        window.updateCalendarStatuses = updateCalendarStatuses;
        window.applyFilters = applyFilters;
        window.closeModals = closeModals;
        window.navigateBlock = navigateBlock;
        window.openBlockManager = openBlockManager;
        
        setInterval(() => {
            if (!document.hidden) {
                console.log('Auto-refreshing agenda...');
                refreshAll();
            }
        }, 10 * 60 * 1000); // 10 minutes
        
        window.showConfirmManualCita = function(onConfirm) {
            window.AppModal.show(
                'Confirmar Cita Manual',
                'Esta es una cita creada manualmente por ti como psicólogo, por lo tanto no requiere de contrapropuestas. Tomando esto en cuenta, ¿Desea usted confirmar un encuentro para esta fecha y horario?',
                { type: 'confirm', btnText: 'SÍ, AGENDAR', intent: 'info' }
            ).then(result => {
                if (result) onConfirm();
            });
        };
    });
</script>

<!-- Modal: Agenda Diaria (Mes) -->
<div id="dailyAgendaModal" class="fixed inset-0 z-[140] hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 transition-all animate-in fade-in duration-150">
    <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-[32px] shadow-2xl shadow-slate-200/50 dark:shadow-gray-900/50 flex flex-col max-h-[85vh] overflow-hidden border border-slate-100 dark:border-gray-700">
        <div class="p-6 border-b border-slate-50 dark:border-gray-700 flex justify-between items-center bg-slate-50/30 dark:bg-gray-700/30">
            <div>
                <h3 id="dailyAgendaTitle" class="text-lg font-black text-slate-800 dark:text-white tracking-tight uppercase">Agenda del Día</h3>
                <p id="dailyAgendaSubtitle" class="text-xs font-bold text-slate-400 dark:text-gray-500 mt-0.5 tracking-wide"></p>
            </div>
            <button id="closeDailyAgendaModal" type="button" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white dark:bg-gray-700 border border-slate-100 dark:border-gray-600 text-slate-400 dark:text-gray-400 hover:text-rose-500 dark:hover:text-rose-400 hover:border-rose-100 dark:hover:border-rose-800 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="dailyAgendaContent" class="p-6 overflow-y-auto space-y-4 custom-scrollbar bg-white dark:bg-gray-800 flex-1">
            <!-- Listado de citas -->
        </div>
    </div>
</div>

@include('components.cita-details-modal')
@include('components.aviso-atencion-modal')
@include('agenda.components.patient-modal')

<!-- Modal: Filtros Avanzados (Historial) -->
<div id="filterModal" class="fixed inset-0 z-[150] hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all animate-in fade-in duration-200">
    <div class="bg-white dark:bg-gray-800 w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 dark:border-gray-700 flex flex-col max-h-[85vh] overflow-hidden">
        <div class="p-6 flex justify-between items-center border-b border-slate-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Filtros Avanzados</h3>
            <button type="button" onclick="document.getElementById('filterModal').classList.add('hidden'); document.getElementById('filterModal').classList.remove('flex');" class="text-slate-400 hover:text-slate-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form method="GET" action="{{ route('agenda.index') }}" class="p-6 overflow-y-auto space-y-4 custom-scrollbar bg-white dark:bg-gray-800 flex-1">
            <input type="hidden" name="view" value="list">
            <input type="hidden" name="psicologo_id" value="{{ $psicologoId }}">

            <div x-data="{ tipoFiltro: '{{ request('tipo_filtro_fecha', 'rango') }}' }">
                <label class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Filtrar fechas por</label>
                <select name="tipo_filtro_fecha" x-model="tipoFiltro" class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white mb-3">
                    <option value="rango">Rango de fechas (cualquier cita)</option>
                    <option value="primera_cita">Fecha de primera cita realizada</option>
                    <option value="ultima_cita">Fecha de última cita realizada</option>
                </select>
                <div class="grid grid-cols-2 gap-4" x-show="tipoFiltro !== ''" x-transition>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Fecha desde</label>
                        <input type="date" name="start_date" value="{{ request('start_date', now()->subMonth()->toDateString()) }}" class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Fecha hasta</label>
                        <input type="date" name="end_date" value="{{ request('end_date', now()->toDateString()) }}" class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white">
                    </div>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-gray-500 mt-1.5 italic" x-show="tipoFiltro === 'primera_cita'">Mostrará citas de pacientes cuya primera sesión realizada esté en este rango.</p>
                <p class="text-[11px] text-slate-400 dark:text-gray-500 mt-1.5 italic" x-show="tipoFiltro === 'ultima_cita'">Mostrará citas de pacientes cuya última sesión realizada esté en este rango.</p>
                <p class="text-[11px] text-slate-400 dark:text-gray-500 mt-1.5 italic" x-show="tipoFiltro === 'rango'">Mostrará todas las citas dentro de este rango de fechas.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Estado de Cita</label>
                <select name="estado" class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white">
                    <option value="">Todos</option>
                    <option value="confirmada" {{ request('estado') === 'confirmada' ? 'selected' : '' }}>Confirmadas</option>
                    <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                    <option value="realizada" {{ request('estado') === 'realizada' ? 'selected' : '' }}>Realizadas</option>
                    <option value="cancelada_paciente" {{ request('estado') === 'cancelada_paciente' ? 'selected' : '' }}>Canceladas (Paciente)</option>
                    <option value="cancelada_psicologo" {{ request('estado') === 'cancelada_psicologo' ? 'selected' : '' }}>Canceladas (Psicólogo)</option>
                    <option value="rechazada" {{ request('estado') === 'rechazada' ? 'selected' : '' }}>Rechazadas</option>
                    <option value="no_asistio" {{ request('estado') === 'no_asistio' ? 'selected' : '' }}>Paciente Ausente</option>
                    <option value="sin_cita" {{ request('estado') === 'sin_cita' ? 'selected' : '' }}>Sin Cita</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Avance de Sesión (Mejoría)</label>
                <select name="avance_id" class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white">
                    <option value="">Todos</option>
                    @foreach($avances as $av)
                        <option value="{{ $av->id }}" {{ request('avance_id') == $av->id ? 'selected' : '' }}>{{ $av->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Estado de Ánimo</label>
                <select name="estado_animo_id" class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white">
                    <option value="">Todos</option>
                    @foreach($estados_animo as $ea)
                        <option value="{{ $ea->id }}" {{ request('estado_animo_id') == $ea->id ? 'selected' : '' }}>{{ $ea->valor }} - {{ $ea->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Prioridad de Atención</label>
                <select name="prioridad" class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white">
                    <option value="">Todas</option>
                    @foreach($prioridades as $prioridad)
                        <option value="{{ $prioridad->nombre }}" {{ request('prioridad') === $prioridad->nombre ? 'selected' : '' }}>{{ $prioridad->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('agenda.index', ['view' => 'list', 'psicologo_id' => $psicologoId]) }}" class="px-4 py-2 bg-slate-100 text-slate-600 dark:bg-gray-700 dark:text-gray-300 font-bold text-sm rounded-xl hover:bg-slate-200 dark:hover:bg-gray-600 transition-colors">Limpiar</a>
                <button type="submit" class="px-5 py-2 bg-sky-600 text-white font-bold text-sm rounded-xl hover:bg-sky-700 shadow-md shadow-sky-200 dark:shadow-sky-900/20 transition-all">Aplicar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Confirmación Personalizado (Multi-acción) -->
<div id="confirmModal" class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-gray-800 rounded-[32px] p-8 max-w-sm w-full shadow-2xl border border-slate-100 dark:border-gray-700">
        <div id="confirmIconBox" class="w-16 h-16 bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 rounded-2xl flex items-center justify-center mb-6 mx-auto">
            <svg id="confirmIconSvg" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 id="confirmTitle" class="text-xl font-black text-slate-900 dark:text-white text-center mb-3 tracking-tight"></h3>
        <p id="confirmText" class="text-sm font-medium text-slate-500 dark:text-gray-400 text-center mb-6 leading-relaxed"></p>
        <div id="confirmInputArea" class="hidden mb-6">
            <label id="confirmInputLabel" class="block text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Motivo</label>
            <textarea id="confirmInputField" rows="3" class="w-full rounded-2xl border border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-3 text-sm font-medium text-slate-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-400 transition-all resize-none"></textarea>
        </div>
        <div class="flex gap-3">
            <button id="confirmNoBtn" class="flex-1 py-4 px-6 bg-slate-50 dark:bg-gray-700 text-slate-400 dark:text-gray-500 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-100 dark:hover:bg-gray-600 hover:text-slate-600 dark:hover:text-gray-300 transition-all">Cancelar</button>
            <button id="confirmYesBtn" class="flex-1 py-4 px-6 bg-sky-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-sky-200 dark:shadow-sky-900/30 hover:bg-sky-800 transition-all">Aceptar</button>
        </div>
    </div>
</div>

{{-- Modal de Exportación con Fecha Personalizada --}}
<div id="exportCustomModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm items-center justify-center z-[100]">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-lg font-black text-slate-800 dark:text-white">Exportar — Rango Personalizado</h4>
                <button onclick="document.getElementById('exportCustomModal').classList.add('hidden'); document.getElementById('exportCustomModal').classList.remove('flex');" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest" for="exportCustomStart">Fecha Inicio</label>
                    <input type="date" id="exportCustomStart" class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-3 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block mb-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest" for="exportCustomEnd">Fecha Fin</label>
                    <input type="date" id="exportCustomEnd" class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-3 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition-all font-medium text-gray-900 dark:text-white">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="exportarCustom('pdf');" class="flex-1 px-4 py-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    PDF
                </button>
                <button onclick="exportarCustom('excel');" class="flex-1 px-4 py-3 bg-green-50 hover:bg-green-100 text-green-600 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Excel
                </button>
            </div>
        </div>
    </div>
</div>
<script>
function exportarCustom(formato) {
    const s = document.getElementById('exportCustomStart').value;
    const e = document.getElementById('exportCustomEnd').value;
    if (!s || !e) { alert('Selecciona ambas fechas.'); return; }
    const url = `{{ route('agenda.estadisticas') }}?format=${formato}&psicologo_id={{ $psicologoId }}&start_date=${s}&end_date=${e}&periodo=personalizado`;
    document.getElementById('exportCustomModal').classList.add('hidden');
    document.getElementById('exportCustomModal').classList.remove('flex');
    if (formato === 'pdf') { window.open(url, '_blank'); } else { window.location.href = url; }
}
</script>

{{-- Modal Detalle de Cita --}}
<div id="detalleCitaModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm items-center justify-center z-[100]" onclick="if(event.target===this){cerrarDetalleCita()}">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-lg font-black text-slate-800 dark:text-white">Detalle de la Cita</h4>
                <button onclick="cerrarDetalleCita()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="space-y-4">
                <div class="flex items-center gap-3 bg-sky-50 dark:bg-sky-900/20 rounded-2xl p-4">
                    <div class="w-10 h-10 bg-sky-100 dark:bg-sky-800/50 text-sky-700 dark:text-sky-400 rounded-xl flex items-center justify-center text-sm font-black uppercase" id="modalCitaInitial">—</div>
                    <div>
                        <p class="text-sm font-black text-slate-800 dark:text-white" id="modalCitaPaciente">—</p>
                        <p class="text-[10px] font-bold text-sky-600 dark:text-sky-400 uppercase tracking-widest">Paciente</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 dark:bg-gray-700/50 rounded-2xl p-4">
                        <p class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Fecha Solicitada</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-gray-300" id="modalCitaSolicitud">—</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-gray-700/50 rounded-2xl p-4">
                        <p class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Fecha Programada</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-gray-300" id="modalCitaProgramada">—</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-gray-700/50 rounded-2xl p-4">
                        <p class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Estado</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-gray-300 capitalize" id="modalCitaEstado">—</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-gray-700/50 rounded-2xl p-4">
                        <p class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Prioridad</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-gray-300 capitalize" id="modalCitaPrioridad">—</p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-gray-700/50 rounded-2xl p-4">
                    <p class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Motivo de Consulta</p>
                    <p class="text-sm font-medium text-slate-700 dark:text-gray-300" id="modalCitaMotivo">—</p>
                </div>

                <div id="modalCitaCancelInfo" class="hidden bg-rose-50 dark:bg-rose-900/20 rounded-2xl p-4">
                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-1" id="modalCitaCancelLabel">Cancelado por</p>
                    <p class="text-sm font-medium text-rose-700 dark:text-rose-400" id="modalCitaCancelValue">—</p>
                </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-end items-center" id="detalleCitaActions">
                <button onclick="cerrarDetalleCita()" class="px-6 py-2.5 bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-gray-300 hover:bg-slate-200 dark:hover:bg-gray-600 font-bold rounded-xl transition-colors text-sm w-full sm:w-auto">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function abrirDetalleCita(data) {
    document.getElementById('modalCitaPaciente').textContent = data.paciente;
    document.getElementById('modalCitaInitial').textContent = data.paciente ? data.paciente.charAt(0).toUpperCase() : '—';
    document.getElementById('modalCitaSolicitud').textContent = data.fecha_solicitud;
    document.getElementById('modalCitaProgramada').textContent = data.fecha_programada;
    let estadoLabel = data.estado ? data.estado.replace(/_/g, ' ') : '—';
    if (data.estado === 'cancelada' && data.cancelado_por) {
        estadoLabel = data.cancelado_por === 'paciente' ? 'Cancelada por el paciente' : 'Cancelada por el psicólogo';
    }
    document.getElementById('modalCitaEstado').textContent = estadoLabel;
    document.getElementById('modalCitaPrioridad').textContent = data.prioridad || 'Normal';
    document.getElementById('modalCitaMotivo').textContent = data.motivo || 'No especificado';

    const cancelInfo = document.getElementById('modalCitaCancelInfo');
    if (data.cancelado_por || data.motivo_rechazo) {
        cancelInfo.classList.remove('hidden');
        if (data.motivo_rechazo) {
            document.getElementById('modalCitaCancelLabel').textContent = 'Motivo de Rechazo';
            document.getElementById('modalCitaCancelValue').textContent = data.motivo_rechazo;
        } else {
            document.getElementById('modalCitaCancelLabel').textContent = 'Cancelado por';
            document.getElementById('modalCitaCancelValue').textContent = data.cancelado_por === 'paciente' ? 'El paciente' : 'El psicólogo';
        }
    } else {
        cancelInfo.classList.add('hidden');
    }

    const actionsContainer = document.getElementById('detalleCitaActions');
    if (actionsContainer) {
        actionsContainer.querySelectorAll('.dynamic-action-btn').forEach(btn => btn.remove());
        if (data.estado === 'confirmada' || data.estado === 'confirmada_reprogramada') {
            const id = data.id;
            
            const btnRealizar = document.createElement('button');
            btnRealizar.className = 'dynamic-action-btn px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition-all uppercase tracking-wider active:scale-95 w-full sm:w-auto shadow-md shadow-emerald-200 dark:shadow-emerald-900/30';
            btnRealizar.textContent = 'Realizada';
            btnRealizar.onclick = () => { cerrarDetalleCita(); if(data.fecha_programada_iso) state.currentBlockDate = data.fecha_programada_iso; if(data.hora_programada_iso) state.currentBlockLabel = data.hora_programada_iso; handleAction('realizar', id); };
            
            const btnAusente = document.createElement('button');
            btnAusente.className = 'dynamic-action-btn px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs transition-all uppercase tracking-wider active:scale-95 w-full sm:w-auto shadow-md shadow-rose-200 dark:shadow-rose-900/30';
            btnAusente.textContent = 'Ausente';
            btnAusente.onclick = () => { cerrarDetalleCita(); if(data.fecha_programada_iso) state.currentBlockDate = data.fecha_programada_iso; if(data.hora_programada_iso) state.currentBlockLabel = data.hora_programada_iso; handleAction('no_asistio', id); };
            
            const btnCancelar = document.createElement('button');
            btnCancelar.className = 'dynamic-action-btn px-5 py-2.5 bg-slate-200 hover:bg-slate-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-slate-700 dark:text-gray-200 rounded-xl font-bold text-xs transition-all uppercase tracking-wider active:scale-95 w-full sm:w-auto';
            btnCancelar.textContent = 'Cancelar';
            btnCancelar.onclick = () => { cerrarDetalleCita(); if(data.fecha_programada_iso) state.currentBlockDate = data.fecha_programada_iso; if(data.hora_programada_iso) state.currentBlockLabel = data.hora_programada_iso; handleAction('cancelar', id); };
            
            const btnPosponer = document.createElement('button');
            btnPosponer.className = 'dynamic-action-btn px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs transition-all uppercase tracking-wider active:scale-95 w-full sm:w-auto shadow-md shadow-amber-200 dark:shadow-amber-900/30';
            btnPosponer.textContent = 'Reagendar';
            btnPosponer.onclick = () => { cerrarDetalleCita(); if(data.fecha_programada_iso) state.currentBlockDate = data.fecha_programada_iso; if(data.hora_programada_iso) state.currentBlockLabel = data.hora_programada_iso; handleAction('posponer', id); };
            
            actionsContainer.insertBefore(btnCancelar, actionsContainer.firstChild);
            actionsContainer.insertBefore(btnAusente, actionsContainer.firstChild);
            actionsContainer.insertBefore(btnPosponer, actionsContainer.firstChild);
            actionsContainer.insertBefore(btnRealizar, actionsContainer.firstChild);
        }
    }

    const modal = document.getElementById('detalleCitaModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarDetalleCita() {
    const modal = document.getElementById('detalleCitaModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function dismissCancelMessage(event, citaId) {
    event.stopPropagation();
    
    fetch(`/citas/${citaId}/dismiss-cancel`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Ocurrió un error al ocultar el mensaje.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al procesar la solicitud.');
    });
}
</script>
</x-app-layout>
