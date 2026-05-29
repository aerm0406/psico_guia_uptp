<x-app-layout>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border-l-8 border-blue-700">
                <div class="p-8 text-gray-900">
                    
                    {{-- VISTA CITAS ACTIVAS --}}
                    <div id="activeAppointmentsView">
                        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center border-b pb-6">
                            <h2 class="text-2xl font-black text-slate-800 mt-2 mb-4 sm:mb-0 uppercase tracking-tight">
                                {{ auth()->user()->role === 'admin' ? 'Gestión Global de Citas' : 'Mis Citas' }}
                            </h2>
                            @if(auth()->user()->role !== 'admin')
                                <div class="flex items-center gap-3">
                                    <button onclick="toggleHistoryView(true)" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-sky-600 hover:border-sky-200 transition-all shadow-sm group" title="Ver historial de sesiones">
                                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                    <a href="{{ route('citas.create') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-800 text-white font-bold rounded-2xl hover:bg-blue-700 transition-all shadow-md shadow-blue-100">+ Solicitar cita</a>
                                </div>
                            @endif
                        </div>

                        @php
                            $citasActivas = $citas->filter(fn($c) => in_array($c->estado, ['pendiente', 'confirmada']));
                        @endphp

                        @if($citasActivas->isEmpty())
                            <div class="text-center py-20 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                                <div class="bg-slate-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <p class="text-lg font-bold text-slate-900">No tienes citas activas.</p>
                                <p class="text-sm text-slate-500 mt-2">
                                    {{ auth()->user()->role === 'admin' ? 'Aún no se han generado solicitudes de citas en el sistema.' : 'Solicita una nueva cita o consulta tu historial.' }}
                                </p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($citasActivas as $cita)
                                    <div class="border border-gray-200 rounded-lg p-4 shadow-sm" data-ajax-remove-card="true">
                                        <div class="flex justify-between items-center">
                                            <h3 class="font-bold text-lg text-slate-800 tracking-tight">{{ optional($cita->fecha)->format('d/m/Y') ?: '-' }} {{ $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('g:i A') : '' }}</h3>
                                            <span class="text-xs font-black px-3 py-1 rounded-full uppercase tracking-widest shadow-sm border
                                                {{ $cita->estado === 'confirmada' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100' }}">
                                                {{ ucfirst($cita->estado ?: 'pendiente') }}
                                            </span>
                                        </div>
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2">
                                            @if(auth()->user()->role === 'admin')
                                                <p class="text-sm text-slate-600">
                                                    <strong class="text-slate-900">Paciente:</strong> 
                                                    <span class="font-semibold text-indigo-600">{{ optional($cita->paciente)->name ?: ($cita->paciente_nombre ?? 'N/A') }}</span>
                                                </p>
                                            @endif
                                            <p class="text-sm text-slate-600"><strong class="text-slate-900">Psicólogo:</strong> {{ optional($cita->psicologo)->name ?: ($cita->psicologo_nombre ?? 'No asignado') }}</p>
                                            <p class="text-sm text-slate-600 md:col-span-2"><strong class="text-slate-900">Motivo:</strong> {{ $cita->motivo ?: 'Sin motivo' }}</p>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1"><strong>Bloques sugeridos:</strong> {{ $cita->bloques_sugeridos ?: 'No definidos' }}</p>
                                        @php
                                            $bloqueConfirmado = $cita->bloque_propuesto;
                                            if ($bloqueConfirmado) {
                                                preg_match('/^([^\s]+)\s+(\d{1,2}:\d{2})\s*[-–—]\s*(\d{1,2}:\d{2})$/', $bloqueConfirmado, $matches);
                                                if (count($matches) === 4) {
                                                    $bloqueConfirmado = $matches[1] . ' ' . \Carbon\Carbon::createFromFormat('H:i', $matches[2])->format('g:i A') . ' - ' . \Carbon\Carbon::createFromFormat('H:i', $matches[3])->format('g:i A');
                                                }
                                            }
                                        @endphp
                                        <p class="text-sm text-gray-600 mt-1"><strong>Bloque confirmado:</strong> {{ $cita->fecha ? $cita->fecha->translatedFormat('l d \d\e F, Y') : '' }} - {{ $bloqueConfirmado ?: 'En espera' }}</p>
                                        
                                        <div class="mt-3 flex justify-end">
                                            <form method="POST" action="{{ route('citas.cancel', $cita->id) }}" data-ajax="true" data-ajax-remove="true" data-ajax-success-message="Cita cancelada correctamente." onsubmit="return confirm('¿Seguro que deseas cancelar esta cita?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-2 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                                                    Cancelar cita
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- VISTA HISTORIAL --}}
                    <div id="historyAppointmentsView" class="hidden animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center border-b pb-6">
                            <div class="flex items-center gap-4">
                                <button onclick="toggleHistoryView(false)" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                </button>
                                <div>
                                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Historial de Sesiones</h2>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5">Recorrido clínico completo</p>
                                </div>
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <span id="historyCount" class="text-[10px] font-black text-slate-300 uppercase tracking-widest">TOTAL: 0 REGISTROS</span>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-[32px] border border-slate-100 bg-white shadow-sm">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Psicólogo</th>
                                        <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha y Hora</th>
                                        <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Estado</th>
                                        <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody" class="divide-y divide-slate-50">
                                    <!-- AJAX CONTENT -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->role !== 'admin')
        <!-- Modal: Detalle de Cita Histórica -->
        <div id="historyDetailModal" class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 transition-all animate-in fade-in duration-200">
            <div class="bg-white w-full max-w-lg rounded-[32px] shadow-2xl shadow-slate-200/50 flex flex-col overflow-hidden border border-slate-100">
                <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-lg font-black text-slate-800 tracking-tight uppercase">Detalle de Sesión</h3>
                    <button onclick="closeHistoryDetail()" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white border border-slate-100 text-slate-400 hover:text-rose-500 transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div id="historyDetailContent" class="p-8 space-y-6 bg-white">
                    <!-- Detalle AJAX -->
                </div>
            </div>
        </div>
    @endif

    <script>
        let currentHistoryData = [];

        function toggleHistoryView(show) {
            const activeView = document.getElementById('activeAppointmentsView');
            const historyView = document.getElementById('historyAppointmentsView');
            const tableBody = document.getElementById('historyTableBody');
            const historyCount = document.getElementById('historyCount');

            if (show) {
                activeView.classList.add('hidden');
                historyView.classList.remove('hidden');
                
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400 font-bold text-xs uppercase tracking-widest">Cargando historial...</td>
                    </tr>
                `;

                fetch('{{ route('citas.history.json') }}')
                    .then(res => res.json())
                    .then(citas => {
                        currentHistoryData = citas;
                        tableBody.innerHTML = '';
                        historyCount.innerText = `TOTAL: ${citas.length} REGISTROS`;
                        
                        if (citas.length === 0) {
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400 font-bold text-xs uppercase tracking-widest">No hay historial disponible</td>
                                </tr>
                            `;
                        } else {
                            citas.forEach(cita => {
                                const tr = document.createElement('tr');
                                tr.className = 'group hover:bg-slate-50/50 transition-colors';
                                
                                let badgeClass = 'bg-slate-50 text-slate-500 border-slate-100';
                                if (cita.estado === 'realizada') badgeClass = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                else if (cita.estado === 'cancelada' || cita.estado === 'rechazada' || cita.estado === 'no_asistio') badgeClass = 'bg-rose-50 text-rose-600 border-rose-100';

                                tr.innerHTML = `
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-sky-50 text-sky-700 rounded-xl flex items-center justify-center text-[10px] font-black uppercase">
                                                ${cita.psicologo.charAt(0)}
                                            </div>
                                            <span class="text-sm font-bold text-slate-700">${cita.psicologo}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-700">${cita.fecha_formateada}</span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase">${cita.hora}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border ${badgeClass}">
                                            ${cita.estado === 'no_asistio' ? 'AUSENTE' : cita.estado.toUpperCase()}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button onclick="showHistoryDetail(${cita.id})" class="p-2 text-slate-300 hover:text-sky-700 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                    </td>
                                `;
                                tableBody.appendChild(tr);
                            });
                        }
                    });
            } else {
                activeView.classList.remove('hidden');
                historyView.classList.add('hidden');
            }
        }

        function showHistoryDetail(id) {
            const cita = currentHistoryData.find(c => c.id == id);
            if (!cita) return;

            const modal = document.getElementById('historyDetailModal');
            const content = document.getElementById('historyDetailContent');

            let statusLabel = cita.estado === 'no_asistio' ? 'Ausente' : cita.estado;
            
            // Procesar notas (pueden ser JSON estructurado)
            let parsedNotas = { 
                motivo_consulta: '', 
                observaciones: cita.notas || '', 
                intervenciones: '',
                avance_estado: '',
                avance_detalle: ''
            };
            try {
                const json = JSON.parse(cita.notas);
                if (typeof json === 'object' && json !== null) {
                    parsedNotas = {
                        motivo_consulta: json.motivo_consulta || '',
                        observaciones: json.observaciones || '',
                        intervenciones: json.intervenciones || '',
                        avance_estado: json.avance_estado || '',
                        avance_detalle: json.avance_detalle || ''
                    };
                }
            } catch(e) {}

            let avanceBadge = '';
            if (parsedNotas.avance_estado) {
                const aMap = {
                    'estancado': { color: 'text-rose-600 bg-rose-50 border-rose-100', label: 'Estancado' },
                    'en_progreso': { color: 'text-sky-600 bg-sky-50 border-sky-100', label: 'En Progreso' },
                    'logrado': { color: 'text-emerald-600 bg-emerald-50 border-emerald-100', label: 'Logrado' }
                };
                const style = aMap[parsedNotas.avance_estado] || { color: 'text-slate-400 bg-slate-50 border-slate-100', label: parsedNotas.avance_estado };
                avanceBadge = `<span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border ${style.color}">${style.label}</span>`;
            }

            content.innerHTML = `
                <div class="grid grid-cols-2 gap-y-6 overflow-y-auto max-h-[70vh] pr-2 custom-scrollbar">
                    <div class="col-span-2 flex items-center gap-4 pb-4 border-b border-slate-50">
                        <div class="w-12 h-12 bg-sky-50 text-sky-700 rounded-2xl flex items-center justify-center text-sm font-black uppercase">
                            ${cita.psicologo.charAt(0)}
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Profesional Asignado</p>
                            <h4 class="text-base font-black text-slate-800 tracking-tight">${cita.psicologo}</h4>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Evolución</p>
                            ${avanceBadge || '<span class="text-[9px] font-bold text-slate-300 italic">No registrado</span>'}
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Fecha</p>
                        <p class="text-sm font-bold text-slate-700">${cita.fecha_formateada}</p>
                    </div>

                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Estado</p>
                        <span class="text-[10px] font-black text-sky-600 uppercase tracking-widest">${statusLabel.toUpperCase()}</span>
                    </div>

                    <div class="col-span-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Motivo de Solicitud</p>
                        <p class="text-sm font-medium text-slate-600 italic leading-relaxed">"${cita.motivo || 'No especificado'}"</p>
                    </div>

                    ${parsedNotas.avance_detalle ? `
                        <div class="col-span-2 p-4 bg-emerald-50/30 rounded-2xl border border-emerald-100 border-dashed">
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2 flex items-center gap-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                Detalle del Avance Terapéutico
                            </p>
                            <p class="text-sm text-slate-700 leading-relaxed font-medium">${parsedNotas.avance_detalle}</p>
                        </div>
                    ` : ''}

                    ${parsedNotas.motivo_consulta ? `
                        <div class="col-span-2">
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Motivo de Consulta (Clínico)</p>
                            <p class="text-sm font-medium text-slate-700">${parsedNotas.motivo_consulta}</p>
                        </div>
                    ` : ''}

                    ${parsedNotas.observaciones ? `
                        <div class="col-span-2 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Observaciones / Evolución</p>
                            <p class="text-sm text-slate-600 leading-relaxed font-medium whitespace-pre-wrap">${parsedNotas.observaciones}</p>
                        </div>
                    ` : ''}

                    ${parsedNotas.intervenciones ? `
                        <div class="col-span-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Intervenciones realizadas</p>
                            <p class="text-sm text-slate-600 italic">${parsedNotas.intervenciones}</p>
                        </div>
                    ` : ''}
                </div>
            `;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeHistoryDetail() {
            const modal = document.getElementById('historyDetailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Cerrar al hacer clic fuera
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('historyDetailModal');
            if (e.target === modal) closeHistoryDetail();
        });
    </script>
</x-app-layout>
