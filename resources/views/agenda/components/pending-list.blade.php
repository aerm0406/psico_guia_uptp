<div id="pendingListWrapper">
    @if($citasPendientes->isEmpty())
        <p id="pendingNoResultsMessage" class="mt-3 text-sm text-gray-500">Sin pacientes encontrados.</p>
    @else
        <p id="pendingNoResultsMessage" class="mt-3 text-sm text-gray-500 hidden">Sin pacientes encontrados.</p>
        <ul id="pendingList" class="mt-2 space-y-3">
            @foreach($citasPendientes as $cita)
                <li class="pending-item bg-blue-50 border border-blue-100 rounded-lg p-3 flex items-center justify-between draggable-patient" data-patient-name="{{ $cita->paciente_short_name ?: 'Paciente' }}" data-cita-id="{{ $cita->id }}" data-prioridad="{{ $cita->prioridad ?? 'media' }}" data-bloques-sugeridos="{{ preg_replace('/(\d{1,2}:\d{2}):\d{2}/', '$1', $cita->bloques_sugeridos ?? '') }}" data-bloques-propuestos="{{ preg_replace('/(\d{1,2}:\d{2}):\d{2}/', '$1', $cita->bloques_propuestos ?? '') }}" data-bloque-propuesto="{{ $cita->bloque_propuesto }}" draggable="true">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                        <span class="text-sm font-semibold text-gray-800">{{ $cita->paciente_short_name ?: 'Paciente' }}</span>
                    </div>
                    <button type="button" class="detail-btn text-xs text-blue-700 font-semibold rounded px-2 py-1 border border-blue-200 hover:bg-blue-100" data-cita-id="{{ $cita->id }}" data-cita-json-url="{{ route('citas.show.json', $cita->id) }}" data-cita-prioridad-url="{{ route('citas.update.prioridad', $cita->id) }}">
                        Detalles
                    </button>
                </li>
            @endforeach
        </ul>
    @endif
</div>
