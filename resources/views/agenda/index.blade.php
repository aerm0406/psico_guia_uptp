<x-app-layout>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border-l-8 border-sky-700">
                <div class="p-8 text-gray-900">
                    {{-- Selector de Psicólogo para Admin --}}
                    <x-psicologo-selector :psicologos="$psicologos" :psicologoId="$psicologoId" />
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 mt-4">
                        <div>
                            <h3 class="text-3xl font-black text-slate-800 tracking-tight">
                                @if(auth()->user()->role === 'admin')
                                    Agenda Centralizada
                                @else
                                    Mi Agenda
                                @endif
                            </h3>
                          <!--  <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1">Gestión de citas y disponibilidad</p>-->
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            {{-- Bloquesito de Fecha de Hoy --}}
                            <div class="flex items-center gap-2 bg-sky-600 text-white px-4 h-12 rounded-2xl shadow-sm">
                                <svg class="w-4 h-4 opacity-80 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <div class="flex flex-col leading-none">
                                    <span class="text-[8px] font-black uppercase tracking-[0.15em] opacity-70">Fecha de hoy</span>
                                    <span class="text-[12px] font-black uppercase tracking-wide">{{ \Carbon\Carbon::now()->translatedFormat('D d M, Y') }}</span>
                                </div>
                            </div>

                            {{-- Navegador de Fechas (Solo para Month/Week) --}}
                            @if($view !== 'list')
                                <div class="flex items-center gap-1 bg-white border border-slate-200 p-1.0 h-12 rounded-2xl shadow-sm">
                                    <a href="{{ route('agenda.index', ['view' => $view, 'date' => ($view === 'month' ? $currentDate->copy()->subMonth() : $currentDate->copy()->subWeek())->toDateString(), 'psicologo_id' => $psicologoId]) }}" 
                                       class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-50 text-slate-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                    </a>
                                    
                                    <span class="px-4 text-[11px] font-black text-slate-700 min-w-[180px] text-center uppercase tracking-widest leading-none">
                                        @if($view === 'month')
                                            {{ $currentDate->translatedFormat('F Y') }}
                                        @else
                                            Semana {{ ceil($currentDate->day / 7) }} <!--, {{ $currentDate->translatedFormat('d M, Y') }}-->
                                        @endif
                                    </span>

                                    <a href="{{ route('agenda.index', ['view' => $view, 'date' => ($view === 'month' ? $currentDate->copy()->addMonth() : $currentDate->copy()->addWeek())->toDateString(), 'psicologo_id' => $psicologoId]) }}" 
                                       class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-50 text-slate-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </div>
                            @endif

                            {{-- Toggles de Vista --}}
                            <div class="bg-slate-100/80 p-1.5 h-12 rounded-2xl flex items-center gap-1">
                                <a href="{{ route('agenda.index', ['view' => 'month', 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}" 
                                   class="px-6 h-9 flex items-center justify-center rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $view === 'month' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
                                   title="Vista Mensual">
                                    Mes
                                </a>
                                <a href="{{ route('agenda.index', ['view' => 'week', 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}" 
                                   class="px-6 h-9 flex items-center justify-center rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $view === 'week' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
                                   title="Vista Semanal">
                                    Semana
                                </a>
                                <a href="{{ route('agenda.index', ['view' => 'list', 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}" 
                                   class="px-5 h-9 flex items-center justify-center rounded-xl transition-all {{ $view === 'list' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
                                   title="Historial de Sesiones">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                        {{-- Sidebar de Solicitudes (Siempre visible) --}}
                        <aside id="pendingRequestsPanel" class="xl:col-span-1 bg-white rounded-[32px] border border-slate-100 p-6 shadow-sm">
                            <div class="flex items-center gap-3 mb-6">
                             <!--   <div class="w-10 h-10 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>-->
                                <h3 class="text-lg font-black text-slate-800 tracking-tight">Pendientes</h3>
                            </div>

                            <div class="space-y-4 mb-6">
                                <div>
                                    <label class="block mb-1.5 text-[10px] font-black text-slate-400 uppercase tracking-widest" for="pendingFilter">Paciente</label>
                                    <input id="pendingFilter" type="text" class="w-full rounded-2xl border-slate-100 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all placeholder-slate-300 font-medium" placeholder="Buscar..." />
                                </div>
                                <div>
                                    <label class="block mb-1.5 text-[10px] font-black text-slate-400 uppercase tracking-widest" for="priorityFilter">Prioridad</label>
                                    <select id="priorityFilter" class="w-full rounded-2xl border-slate-100 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all font-medium">
                                        <option value="">Todas</option>
                                        <option value="baja">Baja</option>
                                        <option value="media">Media</option>
                                        <option value="alta">Alta</option>
                                        <option value="super-alta">Crítica</option>
                                    </select>
                                </div>
                            </div>

                            @include('agenda.components.pending-list')
                        </aside>

                        {{-- Área de Contenido Principal (Calendario/Lista) --}}
                        <section class="xl:col-span-3">
                            @if($view === 'month')
                                {{-- VISTA MENSUAL --}}
                                <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
                                    <div class="grid grid-cols-7 border-b border-slate-100 bg-slate-50/50">
                                        @foreach(['DOM','LUN','MAR','MIÉ','JUE','VIE','SÁ'] as $diaLabel)
                                            <div class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $diaLabel }}</div>
                                        @endforeach
                                    </div>
                                    <div class="grid grid-cols-7">
                                        @foreach($calendarioData as $data)
                                            <div onclick="openDailyAgenda(this, '{{ $data['date'] }}')" 
                                                 class="min-h-[120px] p-2 border-b border-r border-slate-50 relative group cursor-pointer hover:bg-slate-50/80 transition-all {{ !$data['isCurrentMonth'] ? 'bg-slate-50/30' : '' }} {{ $data['isToday'] ? 'bg-sky-50/50' : '' }}"
                                                 data-date="{{ $data['date'] }}">
                                                <div class="flex justify-between items-start mb-2">
                                                    <span class="text-xs font-black {{ $data['isToday'] ? 'w-7 h-7 bg-sky-700 text-white rounded-lg flex items-center justify-center shadow-lg shadow-sky-100' : ($data['isCurrentMonth'] ? 'text-slate-800' : 'text-slate-300') }}">
                                                        {{ $data['day'] }}
                                                    </span>
                                                </div>
                                                
                                                <div class="space-y-1 overflow-y-auto max-h-[80px] custom-scrollbar pointer-events-none">
                                                    @foreach($data['citas'] as $cita)
                                                        <div class="px-2 py-1 rounded-md text-[9px] font-bold truncate 
                                                            {{ $cita->estado === 'confirmada' ? 'bg-sky-100 text-sky-700 border border-sky-200' : 
                                                               ($cita->estado === 'realizada' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 
                                                               ($cita->estado === 'no_asistio' ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-600')) }}">
                                                            {{ $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('g:i A') : 'S/H' }} - {{ $cita->paciente_short_name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($view === 'list')
                                {{-- VISTA HISTORIAL --}}
                                <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden p-8">
                                    <div class="flex items-center justify-between mb-8">
                                        <h3 class="text-xl font-black text-slate-800 tracking-tight">Historial de Sesiones</h3>
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total: {{ $citasCalendario->total() }} registros</div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead>
                                                <tr class="border-b border-slate-100">
                                                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Paciente</th>
                                                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha y Hora</th>
                                                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Estado</th>
                                                    <th class="pb-4"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50">
                                                @foreach($citasCalendario as $cita)
                                                    <tr class="group hover:bg-slate-50/50 transition-colors">
                                                        <td class="py-4">
                                                            <div class="flex items-center gap-3">
                                                                <div class="w-8 h-8 bg-sky-50 text-sky-700 rounded-xl flex items-center justify-center text-[10px] font-black uppercase">
                                                                    {{ substr($cita->paciente_nombre, 0, 1) }}
                                                                </div>
                                                                <span class="text-sm font-bold text-slate-700">{{ $cita->paciente_nombre }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="py-4">
                                                            <div class="flex flex-col">
                                                                @if($cita->estado === 'cancelada')
                                                                    <span class="text-sm font-bold text-slate-400 uppercase tracking-tighter italic">Sin horario asignado</span>
                                                                @else
                                                                    <span class="text-sm font-bold text-slate-700">{{ $cita->fecha->translatedFormat('d M, Y') }}</span>
                                                                    <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('g:i A') : 'Sin hora' }}</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="py-4">
                                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border
                                                                {{ $cita->estado === 'realizada' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 
                                                                   ($cita->estado === 'cancelada' ? 'bg-rose-50 text-rose-600 border-rose-100' : 
                                                                   ($cita->estado === 'confirmada' ? 'bg-sky-50 text-sky-700 border-sky-100' : 'bg-slate-50 text-slate-600 border-slate-100')) }}">
                                                                {{ $cita->estado }}
                                                            </span>
                                                        </td>
                                                        <td class="py-4 text-right">
                                                            <a href="{{ route('historias.show', $cita->user_id) }}" class="p-2 text-slate-300 hover:text-sky-700 transition-colors">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-8 flex justify-center">
                                        {{ $citasCalendario->appends(request()->query())->links('pacientes.partials.pagination') }}
                                    </div>
                                </div>
                            @else
                                {{-- VISTA SEMANAL --}}
                                @if(isset($grupoActivo) && $horarios->isNotEmpty())
                                    @php
                                        // Asegurar que para la vista semanal, el cálculo de fechas siempre empiece desde el lunes
                                        $currentDate = ($currentDate->dayOfWeek === \Carbon\Carbon::SUNDAY) ? $currentDate->copy()->next(\Carbon\Carbon::MONDAY) : $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);

                                        $normalizeBlock = function ($text) {
                                            $value = trim($text ?? '');
                                            
                                            // Reemplazar formato am/pm a 24 horas si lo hay
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
                                            
                                            // Asegurar zero-padding a las horas de 1 digito ej. "lunes 2:" -> "lunes 02:"
                                            $value = preg_replace('/(^|\s|-)(\d):/', '${1}0$2:', $value);
                                            
                                            return strtolower($value);
                                        };
                                        
                                        $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes'];

                                        $defaultIntervalos = collect([
                                            ['inicio' => '07:30', 'fin' => '08:15'],
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
                                    <div class="overflow-x-auto rounded-[32px] border border-slate-100 bg-white shadow-sm">
                                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                                            <thead class="bg-slate-50/50">
                                                <tr>
                                                    <th class="px-4 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest border-r border-slate-100">Hora</th>
                                                    @foreach($dias as $diaHeaderIndex => $dia)
                                                        @php
                                                            $fechaColumna = $currentDate->copy()->addDays($diaHeaderIndex);
                                                            $esHoy = $fechaColumna->isToday();
                                                        @endphp
                                                        <th class="px-4 py-3 text-center uppercase tracking-widest {{ $esHoy ? 'bg-sky-50/80' : '' }}">
                                                            <div class="flex flex-col items-center gap-1">
                                                                <span class="text-[9px] font-black {{ $esHoy ? 'text-sky-600' : 'text-slate-400' }} tracking-[0.15em]">{{ $dia }}</span>
                                                                <span class="@if($esHoy) w-7 h-7 bg-sky-700 text-white rounded-lg flex items-center justify-center text-[11px] font-black shadow-sm @else text-[13px] font-black text-slate-700 @endif">
                                                                    {{ $fechaColumna->day }}
                                                                </span>
                                                            </div>
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50">
                                                @php $sectionActual = null; @endphp
                                                @foreach($intervalos as $intervalo)
                                                    @php
                                                        $t = \Carbon\Carbon::parse($intervalo['inicio']);
                                                        $seccion = $t->lt(\Carbon\Carbon::parse('12:30')) ? 'Mañana' : ($t->lt(\Carbon\Carbon::parse('18:00')) ? 'Vespertino' : 'Nocturno');
                                                    @endphp

                                                    @if($sectionActual !== $seccion)
                                                        <tr class="bg-slate-50/30">
                                                            <td colspan="6" class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">{{ $seccion }}</td>
                                                        </tr>
                                                        @php $sectionActual = $seccion; @endphp
                                                    @endif

                                                    <tr>
                                                        <td class="px-4 py-4 text-center text-[10px] font-black text-slate-400 border-r border-slate-50 bg-slate-50/10">
                                                            {{ \Carbon\Carbon::parse($intervalo['inicio'])->format('g:i') }} - {{ \Carbon\Carbon::parse($intervalo['fin'])->format('g:i') }}
                                                        </td>
                                                        @foreach($dias as $diaIndex => $dia)
                                                            @php
                                                                $horaInicio = $intervalo['inicio'];
                                                                $horaFin = $intervalo['fin'];
                                                                $horarioBloque = $horarios->where('dia', $dia)->first(fn($h) => $h->hora_inicio < $horaFin && $h->hora_fin > $horaInicio);
                                                                
                                                                $bloqueLabel = $horarioBloque ? ($dia . ' ' . \Carbon\Carbon::parse($horarioBloque->hora_inicio)->format('H:i') . ' - ' . \Carbon\Carbon::parse($horarioBloque->hora_fin)->format('H:i')) : "$dia $horaInicio - $horaFin";
                                                                $normalizedSlotText = $normalizeBlock($bloqueLabel);

                                                                // Calcular la fecha exacta de este bloque basado en la semana actual
                                                                $fechaDelDia = $currentDate->copy()->addDays($diaIndex)->toDateString();
                                                                
                                                                $citasConfirmadasEnSlot = $citasCalendario->filter(fn($cita) => $cita->estado === 'confirmada' && $cita->fecha->isSameDay($fechaDelDia) && $cita->bloque_propuesto && str_contains($normalizeBlock($cita->bloque_propuesto), $normalizedSlotText));
                                                                $assignedCita = $citasConfirmadasEnSlot->first();

                                                                $citasEnSlot = $citasPendientes->filter(function ($cita) use ($normalizedSlotText, $normalizeBlock, $dia, $horaInicio, $horaFin) {
                                                                    if (!$cita->bloques_sugeridos) return false;
                                                                    $bloques = array_map('trim', explode(';', $cita->bloques_sugeridos));
                                                                    foreach ($bloques as $bloque) {
                                                                        if (str_contains($normalizeBlock($bloque), strtolower($dia))) {
                                                                            if (preg_match('/(\d{1,2}:\d{2}.*?)\s*[-\x96\x97]\s*(\d{1,2}:\d{2}.*?)/i', $bloque, $m)) {
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
                                                                        {{ $assignedCita ? 'bg-indigo-50 border-indigo-100 text-indigo-700 shadow-sm' : 
                                                                           ($horarioBloque->activo === \App\Models\Horario::STATUS_ACTIVE ? 'bg-white border-slate-100 text-slate-600 hover:border-indigo-200 hover:shadow-md' : 'bg-orange-50 border-orange-100 text-orange-700 hover:border-orange-200') }}"
                                                                        data-block-label="{{ $bloqueLabel }}" 
                                                                        data-block-date="{{ $fechaDelDia }}"
                                                                        data-block-active="{{ $horarioBloque->activo === \App\Models\Horario::STATUS_ACTIVE ? 'true' : 'false' }}"
                                                                        @if($assignedCita) data-assigned-cita-id="{{ $assignedCita->id }}" data-assigned-paciente="{{ $assignedCita->paciente_short_name }}" data-assigned-block="true" @endif>
                                                                        
                                                                        <div class="flex items-center justify-center mb-1">
                                                                            <span class="text-[9px] font-black uppercase tracking-tighter opacity-50 group-hover:opacity-100 transition-opacity">
                                                                                {{ \Carbon\Carbon::parse($horarioBloque->hora_inicio)->format('g:i') }} - {{ \Carbon\Carbon::parse($horarioBloque->hora_fin)->format('g:i') }}
                                                                            </span>
                                                                            @if($assignedCita)
                                                                                {{-- El punto ahora solo aparece junto al nombre vía JS --}}
                                                                            @endif
                                                                        </div>

                                                                        <div class="block-slot-status flex flex-col items-center">
                                                                            @if($assignedCita)
                                                                                <p class="text-[10px] font-black leading-tight truncate uppercase">{{ $assignedCita->paciente_short_name }}</p>
                                                                            @elseif($citasEnSlot->isNotEmpty())
                                                                                <p class="text-[9px] font-black text-orange-600 uppercase">{{ $citasEnSlot->count() }} Solic.</p>
                                                                            @else
                                                                                <p class="text-[9px] font-bold text-slate-300 group-hover:text-slate-400 uppercase">Libre</p>
                                                                            @endif
                                                                        </div>
                                                                    </button>
                                                                @else
                                                                    <div class="h-10 flex items-center justify-center">
                                                                        <div class="w-1 h-1 bg-slate-100 rounded-full"></div>
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
                                    <div class="mt-6 min-h-[400px] bg-white rounded-[32px] border-2 border-dashed border-slate-100 p-12 flex flex-col items-center justify-center text-center">
                                        <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-3xl flex items-center justify-center mb-6">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <h3 class="text-xl font-black text-slate-800 mb-2">Sin Horarios Activos</h3>
                                        <p class="text-slate-400 text-sm max-w-xs mx-auto">Gestiona tus grupos de horarios para comenzar a agendar citas en esta semana.</p>
                                    </div>
                                @endif
                            @endif
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        /**
         * 1. CONFIGURACIÓN Y ESTADO
         */
        const CONFIG = {
            endpoints: {
                json: (id) => `{{ url('citas') }}/${id}/json`,
                prioridad: (id) => `{{ url('citas') }}/${id}/prioridad`,
                rechazar: (id) => `{{ url('citas') }}/${id}/rechazar`,
                aceptar: (id) => `{{ url('citas') }}/${id}/aceptar`,
                proponer: (id) => `{{ url('citas') }}/${id}/proponer`,
                quitarPropuesta: (id) => `{{ url('citas') }}/${id}/quitar-propuesta`,
                realizar: (id) => `{{ url('citas') }}/${id}/realizar`,
                noAsistio: (id) => `{{ url('citas') }}/${id}/no-asistio`,
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
            currentBlockIndex: -1,
            blockLabels: [],
            blockLabelsNormalized: []
        };

        /**
         * 2. UTILIDADES
         */
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

                    // Configurar color del botón Aceptar
                    const btnColor = options.btnColor || 'bg-sky-700 hover:bg-sky-800 shadow-sky-200';
                    y.className = `flex-1 py-4 px-6 ${btnColor} text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg transition-all`;

                    // Configurar ícono
                    const iconColor = options.iconColor || 'bg-sky-50 text-sky-700';
                    iconBox.className = `w-16 h-16 ${iconColor} rounded-2xl flex items-center justify-center mb-6 mx-auto`;
                    if (iconSvg && options.icon) iconSvg.innerHTML = options.icon;

                    // Mostrar/ocultar textarea
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
                        resolve(val);
                    };

                    y.onclick = () => cleanup(options.inputLabel ? inputField.value.trim() || true : true);
                    n.onclick = () => cleanup(false);
                });
            }
        };

        /**
         * 3. GESTIÓN DE CITAS (MODAL DETALLES)
         */
        function openCitaModal(id) {
            state.pendingCitaIds = Array.from(document.querySelectorAll('.pending-item')).map(i => i.dataset.citaId);
            state.currentCitaId = id;
            state.currentCitaIndex = state.pendingCitaIds.indexOf(String(id));
            
            updateCitaNavButtons();
            Utils.api(CONFIG.endpoints.json(id))
                .then(renderCitaDetails)
                .catch(err => { console.error(err); alert('Error al cargar la cita.'); });
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

            // Prioridad Dot
            const pMap = { baja: 'bg-sky-400', media: 'bg-yellow-400', alta: 'bg-orange-400', 'super-alta': 'bg-rose-500' };
            const dot = document.getElementById('citaPrioridadDot');
            if (dot) dot.className = `h-2 w-2 rounded-full ${pMap[cita.prioridad] || pMap.media}`;
            set('citaPrioridadTexto', cita.prioridad === 'super-alta' ? 'Crítica' : (cita.prioridad || 'Media').charAt(0).toUpperCase() + (cita.prioridad || 'media').slice(1));

            // Radios
            document.querySelectorAll('.prioridad-radio').forEach(r => r.checked = r.value === cita.prioridad);
            document.getElementById('prioridadMensaje')?.classList.add('hidden');

            // Avatar
            const initials = (cita.paciente || 'P').split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
            const av = document.getElementById('citaAvatar'); if (av) av.textContent = initials;

            // Bloques
            const cont = document.getElementById('citaBloquesSugeridos');
            if (cont) {
                cont.innerHTML = '';
                const raw = cita.bloques_sugeridos || cita.bloques_propuestos || document.querySelector(`.pending-item[data-cita-id="${state.currentCitaId}"]`)?.dataset.bloquesSugeridos || '';
                const list = raw.split(/[;,]/).map(s => s.trim()).filter(Boolean);
                
                if (!list.length) cont.innerHTML = '<span class="text-[10px] text-slate-400 italic">No hay horarios sugeridos</span>';
                else list.forEach(txt => {
                    const chip = document.createElement('span');
                    chip.className = 'px-3 py-1 text-[9px] font-black uppercase rounded-xl bg-white text-slate-600 border border-slate-200 shadow-sm';
                    chip.textContent = Utils.formatAmPm(txt);
                    cont.appendChild(chip);
                });
            }

            const m = document.getElementById('citaDetailsModal');
            if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
        }

        function updateCitaNavButtons() {
            const p = document.getElementById('prevCitaBtn'), n = document.getElementById('nextCitaBtn');
            if (p && n) { p.disabled = state.currentCitaIndex <= 0; n.disabled = state.currentCitaIndex < 0 || state.currentCitaIndex >= state.pendingCitaIds.length - 1; }
        }

        /**
         * 4. GESTIÓN DE BLOQUES (CALENDARIO)
         */
        function openBlockModal(cell) {
            state.currentBlockLabel = cell.dataset.blockLabel;
            state.currentBlockDate = cell.dataset.blockDate; // Guardar la fecha exacta del bloque
            refreshBlockLabels();
            state.currentBlockIndex = state.blockLabelsNormalized.indexOf(Utils.normalize(state.currentBlockLabel));
            
            updateBlockNavButtons();
            
            const header = document.getElementById('blockModalHeader');
            if (header) header.textContent = Utils.formatAmPm(state.currentBlockLabel);

            renderBlockRequests(cell);

            const m = document.getElementById('blockRequestsModal');
            if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
        }

        /**
         * 4.1 AGENDA DIARIA (VISTA MENSUAL)
         */
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
            const list = document.getElementById('blockModalRequestsList');
            const assignedList = document.getElementById('blockModalAssignedList');
            const badge = document.getElementById('statusBadgePlaceholder');
            const assignedInfo = document.getElementById('blockModalAssignedInfo');
            const assignedActions = document.getElementById('blockModalAssignedActions');

            list.innerHTML = ''; assignedList.innerHTML = '';
            const assignedPac = cell.dataset.assignedPaciente;
            const assignedId = cell.dataset.assignedCitaId;

            if (assignedPac) {
                assignedInfo.classList.remove('hidden');
                assignedActions?.classList.remove('hidden');
                document.getElementById('blockModalAssignedEmptyMessage')?.classList.add('hidden');
                document.getElementById('blockModalEmptyMessage')?.classList.add('hidden');
                if (badge) badge.innerHTML = '<span class="px-3 py-1 bg-emerald-500 text-white rounded-full text-[10px] font-black uppercase">Confirmado</span>';
                
                ['blockModalMarkRealizada', 'blockModalMarkNoAsistio', 'blockModalCancelConfirmed'].forEach(id => {
                    const btn = document.getElementById(id); if (btn) btn.dataset.citaId = assignedId;
                });

                const li = document.createElement('li');
                li.className = 'group rounded-3xl border-2 border-sky-100 p-5 bg-gradient-to-br from-sky-50 to-white shadow-sm';
                li.innerHTML = `<div class="flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-sky-600 text-white flex items-center justify-center shadow-lg"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div><div class="flex flex-col"><span class="text-xs font-bold text-sky-600 uppercase">Paciente Asignado</span><span class="text-xl font-black text-slate-800">${Utils.escapeHtml(assignedPac)}</span></div></div>`;
                assignedList.appendChild(li);
            } else {
                assignedInfo.classList.add('hidden');
                assignedActions?.classList.add('hidden');
                document.getElementById('blockModalAssignedEmptyMessage')?.classList.remove('hidden');
                if (badge) badge.innerHTML = '<span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase">Disponible</span>';

                const candidates = getCandidatesForBlock(state.currentBlockLabel);
                if (!candidates.length) {
                    document.getElementById('blockModalEmptyMessage')?.classList.remove('hidden');
                } else {
                    document.getElementById('blockModalEmptyMessage')?.classList.add('hidden');
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
                                    ${can.status === 'proposed' ? '<span class="text-[9px] font-black text-sky-600 uppercase">Propuesta Enviada</span>' : ''}
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

        function getCandidatesForBlock(label) {
            const norm = Utils.normalize(label);
            return Array.from(document.querySelectorAll('.pending-item')).filter(i => {
                const sug = i.dataset.bloquesSugeridos || '', pro = i.dataset.bloquesPropuestos || '';
                return sug.split(';').some(b => Utils.normalize(b) === norm) || pro.split(';').some(b => Utils.normalize(b) === norm);
            }).map(i => ({
                citaId: i.dataset.citaId,
                paciente: i.dataset.patientName || 'Paciente',
                status: (i.dataset.bloquesPropuestos || '').split(';').some(b => Utils.normalize(b) === norm) ? 'proposed' : 'interested'
            }));
        }

        function refreshBlockLabels() {
            const buttons = Array.from(document.querySelectorAll('.block-slot-button'));
            state.blockLabels = buttons.map(b => b.dataset.blockLabel).filter(Boolean);
            state.blockLabelsNormalized = state.blockLabels.map(Utils.normalize);
        }

        function updateBlockNavButtons() {
            const p = document.getElementById('prevBlockBtn'), n = document.getElementById('nextBlockBtn');
            if (p && n) { p.disabled = state.currentBlockIndex <= 0; n.disabled = state.currentBlockIndex < 0 || state.currentBlockIndex >= state.blockLabels.length - 1; }
        }

        /**
         * 5. ACCIONES API
         */
        function handleAction(action, id, targetBtn = null) {
            let endpoint = '';
            let body = null;
            let successMsg = '';

            // Map frontend actions to backend logic
            const normalizedAction = action === 'complete' ? 'realizar' : 
                                    (action === 'cancel_confirmada' ? 'cancelar' : action);

            // OPTIMISTIC UI: Feedback visual inmediato
            if (targetBtn) {
                targetBtn.disabled = true;
                targetBtn.classList.add('opacity-50', 'cursor-wait');
                if (!targetBtn.innerHTML.includes('svg')) {
                    targetBtn.innerText = '...';
                }
            }

            switch(normalizedAction) {
                case 'reject':
                    const reason = prompt('Motivo del rechazo:', 'Lo siento, no puedo atenderte en este momento.');
                    if (reason === null) {
                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                        return;
                    }
                    endpoint = CONFIG.endpoints.rechazar(id); body = { motivo_rechazo: reason }; 
                    if (targetBtn) {
                        const item = targetBtn.closest('.block-request-item');
                        if (item) item.style.display = 'none'; // Optimistic hide
                    }
                    break;
                case 'accept':
                    // Validación preventiva Frontend: No permitir fechas o horas pasadas (valida fecha y hora exactas del bloque)
                    const timeMatch = state.currentBlockLabel ? state.currentBlockLabel.match(/(\d{1,2}:\d{2})/) : null;
                    const timeStr = timeMatch ? timeMatch[1] : '00:00';
                    const selectedDateTime = state.currentBlockDate ? new Date(state.currentBlockDate + 'T' + timeStr + ':00') : new Date();
                    const now = new Date();
                    
                    if (selectedDateTime < now) {
                        alert('No puedes agendar citas en fechas u horas pasadas.');
                        if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                        return;
                    }

                    endpoint = CONFIG.endpoints.aceptar(id); 
                    body = { 
                        fecha: state.currentBlockDate || new Date().toISOString().split('T')[0], 
                        hora: state.currentBlockLabel.match(/(\d{1,2}:\d{2})/)?.[1], 
                        bloque: state.currentBlockLabel 
                    }; 
                    closeModals(); // Optimistic close
                    break;
                case 'propose': endpoint = CONFIG.endpoints.proponer(id); body = { bloque: state.currentBlockLabel }; break;
                case 'remove_proposal': 
                    endpoint = CONFIG.endpoints.quitarPropuesta(id); body = { bloque: state.currentBlockLabel }; 
                    if (targetBtn) {
                        const item = targetBtn.closest('.block-request-item');
                        if (item) item.style.display = 'none'; // Optimistic hide
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
                                window.location.href = res.redirect_url || `{{ url('citas') }}/${id}/editar-nota`;
                            })
                            .catch(err => {
                                console.error(err);
                                if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                alert('Error al procesar la cita.');
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
                            .then(() => refreshAll())
                            .catch(err => {
                                console.error(err);
                                if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                alert('Error al registrar la inasistencia.');
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
                            inputLabel: 'Motivo de cancelación',
                            inputDefault: 'Surgió un inconveniente.'
                        }
                    ).then(result => {
                        if (result === false) {
                            if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                            return;
                        }
                        const motivo = typeof result === 'string' ? result : 'Cancelado por el psicólogo.';
                        closeModals();
                        Utils.api(CONFIG.endpoints.cancelar(id), 'PATCH', { motivo_cancelacion: motivo })
                            .then(() => refreshAll())
                            .catch(err => {
                                console.error(err);
                                if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                                alert('Error al cancelar la cita.');
                            });
                    });
                    return;
                default: console.error('Acción no reconocida:', action); return;
            }

            Utils.api(endpoint, 'PATCH', body)
                .then(res => {
                    // Update all calendar cell data locally to reflect the change immediately across all duplicate cells
                    const cells = document.querySelectorAll(`.block-slot-button[data-block-label="${state.currentBlockLabel}"]`);
                    cells.forEach(cell => {
                        if (normalizedAction === 'accept') {
                            cell.dataset.assignedBlock = 'true';
                            cell.dataset.assignedPaciente = res.paciente || 'Paciente';
                            cell.dataset.assignedCitaId = res.cita_id || id;
                        } else if (['realizar', 'no_asistio', 'cancelar', 'reject'].includes(normalizedAction)) {
                            // If we completed, cancelled or rejected, and it was the assigned one, clear it
                            if (cell.dataset.assignedCitaId == id) {
                                delete cell.dataset.assignedBlock;
                                delete cell.dataset.assignedPaciente;
                                delete cell.dataset.assignedCitaId;
                            }
                        }
                    });

                    if (successMsg === 'redirect') { 
                        window.location.href = `{{ url('historias') }}/${res.paciente_id}?tab=evolucion`; 
                        return; 
                    }
                    if (successMsg) alert(successMsg);
                    refreshAll();
                })
                .catch(err => { 
                    console.error(err); 
                    if (targetBtn) { targetBtn.disabled = false; targetBtn.classList.remove('opacity-50', 'cursor-wait'); }
                    
                    if (err instanceof Response) {
                        err.json().then(data => {
                            alert(data.message || 'Error al procesar la acción.');
                        }).catch(() => {
                            alert('Error al procesar la acción.');
                        });
                    } else {
                        alert('Error al procesar la acción.');
                    }
                });
        }

        function refreshAll() {
            const params = new URLSearchParams(window.location.search);
            const q = document.getElementById('pendingFilter')?.value;
            const p = document.getElementById('priorityFilter')?.value;
            if (q) params.set('q', q); else params.delete('q');
            if (p) params.set('prioridad', p); else params.delete('prioridad');

            fetch(`${CONFIG.endpoints.pendingList}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => {
                    if (!res.ok) throw new Error('Error al recargar lista');
                    return res.text();
                })
                .then(html => {
                    const wrapper = document.getElementById('pendingListWrapper');
                    if (wrapper) wrapper.outerHTML = html;
                    applyFilters();
                    updateCalendarStatuses();
                    const modal = document.getElementById('blockRequestsModal');
                    if (modal && !modal.classList.contains('hidden')) {
                        const cell = document.querySelector(`.block-slot-button[data-block-label="${state.currentBlockLabel}"]`);
                        if (cell) renderBlockRequests(cell); else closeModals();
                    }
                })
                .catch(err => console.error('Refresh error:', err));
        }

        function updateCalendarStatuses() {
            document.querySelectorAll('.block-slot-button').forEach(btn => {
                const status = btn.querySelector('.block-slot-status');
                if (!status) return;
                
                if (btn.dataset.assignedBlock === 'true') {
                    btn.classList.add('bg-blue-100', 'border-blue-200', 'text-blue-700');
                    btn.classList.remove('bg-blue-50', 'bg-yellow-50', 'text-yellow-700', 'border-yellow-200');
                    status.innerHTML = `<span class="inline-flex items-center justify-center text-xs text-sky-900 mt-1"><span class="h-2 w-2 mr-1 rounded-full bg-sky-500"></span>${Utils.escapeHtml(btn.dataset.assignedPaciente)}</span>`;
                } else {
                    const isActive = btn.dataset.blockActive === 'true';
                    btn.classList.remove('bg-blue-100', 'border-blue-200', 'text-blue-700');
                    if (isActive) {
                        btn.classList.add('bg-blue-50', 'border-blue-200', 'text-blue-700');
                        btn.classList.remove('bg-yellow-50', 'border-yellow-200', 'text-yellow-700');
                    } else {
                        btn.classList.add('bg-yellow-50', 'border-yellow-200', 'text-yellow-700');
                        btn.classList.remove('bg-blue-50', 'border-blue-200', 'text-blue-700');
                    }
                    const cands = getCandidatesForBlock(btn.dataset.blockLabel);
                    status.innerHTML = cands.length ? `<span class="inline-flex items-center justify-center text-xs text-orange-700 mt-1"><span class="h-2 w-2 mr-1 rounded-full bg-orange-500"></span>${cands.length} pendiente(s)</span>` : '';
                }
            });
        }

        /**
         * 6. FILTROS Y NAVEGACIÓN
         */
        function applyFilters() {
            const q = document.getElementById('pendingFilter')?.value?.toLowerCase();
            const p = document.getElementById('priorityFilter')?.value;
            let count = 0;
            document.querySelectorAll('.pending-item').forEach(i => {
                const match = (!q || i.dataset.patientName.toLowerCase().includes(q)) && (!p || i.dataset.prioridad === p);
                i.style.display = match ? '' : 'none';
                if (match) count++;
            });
            document.getElementById('pendingNoResultsMessage')?.classList.toggle('hidden', count > 0);
        }

        function closeModals() {
            ['citaDetailsModal', 'blockRequestsModal', 'dailyAgendaModal'].forEach(id => {
                const m = document.getElementById(id);
                if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
            });
        }

        /**
         * 7. EVENT LISTENERS
         */
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('button, a');
            if (!btn) return;

            if (btn.classList.contains('detail-btn')) openCitaModal(btn.dataset.citaId);
            else if (btn.classList.contains('block-slot-button')) openBlockModal(btn);
            else if (btn.classList.contains('block-request-action-btn')) handleAction(btn.dataset.action, btn.dataset.citaId, btn);
            else if (btn.id === 'blockModalMarkRealizada') handleAction('realizar', btn.dataset.citaId, btn);
            else if (btn.id === 'blockModalMarkNoAsistio') handleAction('no_asistio', btn.dataset.citaId, btn);
            else if (btn.id === 'blockModalCancelConfirmed') handleAction('cancelar', btn.dataset.citaId, btn);
            else if (['closeCitaModal', 'closeBlockModal'].includes(btn.id)) closeModals();
            else if (btn.id === 'prevCitaBtn') openCitaModal(state.pendingCitaIds[state.currentCitaIndex - 1]);
            else if (btn.id === 'nextCitaBtn') openCitaModal(state.pendingCitaIds[state.currentCitaIndex + 1]);
            else if (btn.id === 'prevBlockBtn') { state.currentBlockIndex--; openBlockModal(document.querySelector(`.block-slot-button[data-block-label="${state.blockLabels[state.currentBlockIndex]}"]`)); }
            else if (btn.id === 'nextBlockBtn') { state.currentBlockIndex++; openBlockModal(document.querySelector(`.block-slot-button[data-block-label="${state.blockLabels[state.currentBlockIndex]}"]`)); }
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

        // Filtros
        document.getElementById('pendingFilter')?.addEventListener('input', (e) => { 
            applyFilters(); 
            const p = new URLSearchParams(window.location.search); 
            p.set('q', e.target.value); 
            history.replaceState(null, '', `${window.location.pathname}?${p.toString()}`); 
        });
        document.getElementById('priorityFilter')?.addEventListener('change', (e) => { 
            applyFilters(); 
            const p = new URLSearchParams(window.location.search); 
            p.set('prioridad', e.target.value); 
            history.replaceState(null, '', `${window.location.pathname}?${p.toString()}`); 
        });

        // Modales click fuera
        [document.getElementById('citaDetailsModal'), document.getElementById('blockRequestsModal'), document.getElementById('dailyAgendaModal')].forEach(m => {
            m?.addEventListener('click', (e) => { if (e.target === m) closeModals(); });
        });

        // Drag & Drop
        let draggedId = null;
        document.addEventListener('dragstart', (e) => { if (e.target.classList.contains('draggable-patient')) draggedId = e.target.dataset.citaId; });
        document.addEventListener('dragover', (e) => { if (e.target.closest('.drop-zone')) e.preventDefault(); });
        document.addEventListener('drop', (e) => {
            const zone = e.target.closest('.drop-zone');
            if (zone && draggedId && zone.dataset.assignedBlock !== 'true') {
                state.currentBlockLabel = zone.dataset.blockLabel;
                handleAction('propose', draggedId);
            }
        });

        // Swipe Navigation
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
        addSwipe(document.getElementById('blockRequestsModal'), () => { state.currentBlockIndex--; openBlockModal(document.querySelector(`.block-slot-button[data-block-label="${state.blockLabels[state.currentBlockIndex]}"]`)); }, () => { state.currentBlockIndex++; openBlockModal(document.querySelector(`.block-slot-button[data-block-label="${state.blockLabels[state.currentBlockIndex]}"]`)); });

        // Keyboard
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                if (!document.getElementById('citaDetailsModal').classList.contains('hidden')) openCitaModal(state.pendingCitaIds[state.currentCitaIndex - 1]);
                else if (!document.getElementById('blockRequestsModal').classList.contains('hidden')) { state.currentBlockIndex--; openBlockModal(document.querySelector(`.block-slot-button[data-block-label="${state.blockLabels[state.currentBlockIndex]}"]`)); }
            } else if (e.key === 'ArrowRight') {
                if (!document.getElementById('citaDetailsModal').classList.contains('hidden')) openCitaModal(state.pendingCitaIds[state.currentCitaIndex + 1]);
                else if (!document.getElementById('blockRequestsModal').classList.contains('hidden')) { state.currentBlockIndex++; openBlockModal(document.querySelector(`.block-slot-button[data-block-label="${state.blockLabels[state.currentBlockIndex]}"]`)); }
            }
        });

        // Inicializar
        const initialParams = new URLSearchParams(window.location.search);
        if (initialParams.has('q')) document.getElementById('pendingFilter').value = initialParams.get('q');
        if (initialParams.has('prioridad')) document.getElementById('priorityFilter').value = initialParams.get('prioridad');
        applyFilters();
        updateCalendarStatuses();
    });
</script>

<!-- Modal: Agenda Diaria (Mes) -->
<div id="dailyAgendaModal" class="fixed inset-0 z-[140] hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 transition-all animate-in fade-in duration-200">
    <div class="bg-white w-full max-w-lg rounded-[32px] shadow-2xl shadow-slate-200/50 flex flex-col max-h-[85vh] overflow-hidden border border-slate-100">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
            <div>
                <h3 id="dailyAgendaTitle" class="text-lg font-black text-slate-800 tracking-tight uppercase">Agenda del Día</h3>
                <p id="dailyAgendaSubtitle" class="text-xs font-bold text-slate-400 mt-0.5 tracking-wide"></p>
            </div>
            <button onclick="closeModals()" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white border border-slate-100 text-slate-400 hover:text-rose-500 hover:border-rose-100 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="dailyAgendaContent" class="p-6 overflow-y-auto space-y-4 custom-scrollbar bg-white flex-1">
            <!-- Listado de citas -->
        </div>
        <!-- 
        <div class="p-4 bg-slate-50/50 border-t border-slate-50 text-center">
            <button onclick="closeModals()" class="px-8 py-3 bg-white border border-slate-200 text-slate-600 font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-slate-100 transition-all shadow-sm">Cerrar</button>
        </div>
        -->
    </div>
</div>

@include('components.cita-details-modal')
@include('components.block-requests-modal')
@include('components.aviso-atencion-modal')

<!-- Modal de Confirmación Personalizado (Multi-acción) -->
<div id="confirmModal" class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-[32px] p-8 max-w-sm w-full shadow-2xl border border-slate-100">
        <div id="confirmIconBox" class="w-16 h-16 bg-sky-50 text-sky-700 rounded-2xl flex items-center justify-center mb-6 mx-auto">
            <svg id="confirmIconSvg" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 id="confirmTitle" class="text-xl font-black text-slate-900 text-center mb-3 tracking-tight"></h3>
        <p id="confirmText" class="text-sm font-medium text-slate-500 text-center mb-6 leading-relaxed"></p>
        <!-- Textarea opcional para motivo -->
        <div id="confirmInputArea" class="hidden mb-6">
            <label id="confirmInputLabel" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Motivo</label>
            <textarea id="confirmInputField" rows="3" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-400 transition-all resize-none"></textarea>
        </div>
        <div class="flex gap-3">
            <button id="confirmNoBtn" class="flex-1 py-4 px-6 bg-slate-50 text-slate-400 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-100 hover:text-slate-600 transition-all">Cancelar</button>
            <button id="confirmYesBtn" class="flex-1 py-4 px-6 bg-sky-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-sky-200 hover:bg-sky-800 transition-all">Aceptar</button>
        </div>
    </div>
</div>
</x-app-layout>