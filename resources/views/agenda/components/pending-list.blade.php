<div id="pendingListWrapper" class="flex flex-col flex-1 h-full min-h-0 overflow-hidden">
    <div class="flex-1 overflow-y-auto invisible-scrollbar">
        @if($citasPendientes->isEmpty())
            <p id="pendingNoResultsMessage" class="mt-3 text-sm text-gray-500 dark:text-gray-400">Sin pacientes encontrados.</p>
        @else
            <p id="pendingNoResultsMessage" class="mt-3 text-sm text-gray-500 dark:text-gray-400 hidden">Sin pacientes encontrados.</p>
            <ul id="pendingList" class="mt-2 space-y-3 mb-4">
                @foreach($citasPendientes as $cita)
                    @php
                        $prioridadClase = match(strtolower($cita->prioridad ?? 'media')) {
                            'baja' => 'bg-white dark:bg-gray-800 border-emerald-200 dark:border-emerald-800',
                            'media' => 'bg-white dark:bg-gray-800 border-sky-200 dark:border-sky-800',
                            'alta' => 'bg-white dark:bg-gray-800 border-amber-200 dark:border-amber-800',
                            'crítica' => 'bg-white dark:bg-gray-800 border-rose-200 dark:border-rose-800',
                            default => 'bg-white dark:bg-gray-800 border-indigo-200 dark:border-indigo-800'
                        };
                        $puntoClase = match(strtolower($cita->prioridad ?? 'media')) {
                            'baja' => 'bg-emerald-500',
                            'media' => 'bg-sky-500',
                            'alta' => 'bg-amber-500',
                            'crítica' => 'bg-rose-500',
                            default => 'bg-indigo-500'
                        };
                    @endphp
                    @php
                        $isManual = in_array($cita->motivo, ['Asignado manualmente por psicólogo', 'Gestionada por psicólogo']) || str_contains($cita->motivo, 'anualmente') || str_contains($cita->motivo, 'estionada');
                    @endphp
                    <li class="pending-item {{ $prioridadClase }} rounded-lg p-3 flex items-center justify-between draggable-patient"
                        data-patient-name="{{ $cita->paciente_short_name ?: 'Paciente' }}"
                        data-patient-cedula="{{ $cita->paciente_cedula ?? '' }}"
                        data-cita-id="{{ $cita->id }}"
                        data-prioridad="{{ $cita->prioridad ?? 'media' }}"
                        data-bloques-sugeridos="{{ preg_replace('/(\d{1,2}:\d{2}):\d{2}/', '$1', $cita->bloques_sugeridos ?? '') }}"
                        data-bloques-propuestos="{{ preg_replace('/(\d{1,2}:\d{2}):\d{2}/', '$1', $cita->bloques_propuestos ?? '') }}"
                        data-bloque-propuesto="{{ $cita->bloque_propuesto }}"
                        data-propuesta-estado="{{ $cita->propuesta_estado ?? '' }}"
                        data-is-manual="{{ $isManual ? '1' : '0' }}"
                        draggable="true">
                        <div class="flex items-center gap-2">
                            <span class="shrink-0 h-2 w-2 rounded-full {{ $puntoClase }}"></span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $cita->paciente_short_name ?: 'Paciente' }}</span>
                        </div>
                        @php
                            $isContrapropuesta = !empty($cita->propuesta_estado);
                            
                            if ($isManual) {
                                $btnClasses = 'text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-800 hover:bg-orange-100 dark:hover:bg-orange-900/30';
                            } elseif ($isContrapropuesta) {
                                $btnClasses = 'text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/30';
                            } else {
                                $btnClasses = 'text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/30';
                            }
                        @endphp
                        <button type="button" class="ml-3 shrink-0 detail-btn text-xs font-semibold rounded px-2 py-1 border transition {{ $btnClasses }}" data-cita-id="{{ $cita->id }}" data-cita-json-url="{{ route('citas.show.json', $cita->id) }}" data-cita-prioridad-url="{{ route('citas.update.prioridad', $cita->id) }}">
                            Detalles
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        @if(isset($pacientesSinCita) && $pacientesSinCita->isNotEmpty())
            <div class="{{ $citasPendientes->isEmpty() ? 'mt-2' : 'mt-4' }}">
                <ul class="space-y-3">
                    @foreach($pacientesSinCita as $pacienteSinCita)
                        @php
                            // Calculate short name manually since we don't have the attribute here
                            $nombresArr = array_filter(explode(' ', $pacienteSinCita->nombres ?? ''));
                            $apellidosArr = array_filter(explode(' ', $pacienteSinCita->apellidos ?? ''));
                            $primerNombre = !empty($nombresArr) ? array_values($nombresArr)[0] : '';
                            $primerApellido = !empty($apellidosArr) ? array_values($apellidosArr)[0] : '';
                            $shortName = trim($primerNombre . ' ' . $primerApellido) ?: 'Paciente';
                        @endphp
                        <li class="pending-item bg-white dark:bg-gray-800 border-orange-200 dark:border-orange-800 rounded-lg p-3 flex items-center justify-between" data-patient-name="{{ $shortName }}" data-patient-cedula="{{ $pacienteSinCita->cedula ?? '' }}">
                            <div class="flex items-center gap-2">
                                <span class="shrink-0 h-2 w-2 rounded-full bg-orange-500"></span>
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $shortName }}</span>
                            </div>
                            <button type="button" class="ml-3 shrink-0 agregar-manual-btn text-xs text-orange-700 dark:text-orange-400 font-semibold rounded px-2 py-1 border border-orange-200 dark:border-orange-800 hover:bg-orange-100 dark:hover:bg-orange-900/30 transition" data-paciente-id="{{ $pacienteSinCita->id }}">
                                Agregar
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @if(isset($citasPendientes) && method_exists($citasPendientes, 'hasPages') && $citasPendientes->hasPages())
        <div class="mt-auto flex justify-center pt-4 shrink-0">
            {{ $citasPendientes->appends(request()->query())->links('agenda.partials.pagination') }}
        </div>
    @endif
</div>
