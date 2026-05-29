@props(['horario' => null, 'dias' => []])

{{-- ================================Formulario Compartido para Crear/Editar Horario================================== --}}
<form action="{{ $horario ? route('horarios.update', $horario->id) : route('horarios.store') }}" method="POST" class="space-y-6">
    @csrf

    @if($horario)
        @method('PUT')
    @endif

    @if(isset($grupoRetorno) && $grupoRetorno)
        <input type="hidden" name="grupo_id" value="{{ $grupoRetorno }}">
    @endif

    {{-- ================================Errores de Validación================================== --}}
    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================================Campos de Día, Hora Inicio y Hora Fin================================== --}}
    <div class="grid grid-cols-1 gap-4">
        @php
            $horaInicioHora = old('hora_inicio_hora', isset($horario->hora_inicio) ? \Carbon\Carbon::parse($horario->hora_inicio)->format('g') : '');
            $horaInicioMinuto = old('hora_inicio_minuto', isset($horario->hora_inicio) ? \Carbon\Carbon::parse($horario->hora_inicio)->format('i') : '00');
            $horaInicioPeriodo = old('hora_inicio_periodo', isset($horario->hora_inicio) ? \Carbon\Carbon::parse($horario->hora_inicio)->format('A') : 'AM');
            $horaFinHora = old('hora_fin_hora', isset($horario->hora_fin) ? \Carbon\Carbon::parse($horario->hora_fin)->format('g') : '');
            $horaFinMinuto = old('hora_fin_minuto', isset($horario->hora_fin) ? \Carbon\Carbon::parse($horario->hora_fin)->format('i') : '00');
            $horaFinPeriodo = old('hora_fin_periodo', isset($horario->hora_fin) ? \Carbon\Carbon::parse($horario->hora_fin)->format('A') : 'AM');
        @endphp

        @if(isset($horario))
            <input type="hidden" name="dia" value="{{ old('dia', $horario->dia) }}">
        @else
            <label class="block">
                <span class="text-gray-700">Día</span>
                <select name="dia" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <option value="" disabled {{ old('dia', $horario->dia ?? '') === '' ? 'selected' : '' }}>Selecciona un día</option>
                    @foreach ($dias as $dia)
                        <option value="{{ $dia }}" {{ old('dia', $horario->dia ?? '') === $dia ? 'selected' : '' }}>{{ $dia }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <label class="block">
                <span class="text-gray-700">Hora inicio</span>
                <div class="mt-1 flex gap-2 items-center">
                    <select name="hora_inicio_hora" class="block w-20 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2" required>
                        <option value="" disabled {{ $horaInicioHora === '' ? 'selected' : '' }}>HH</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ (string)$i === (string)$horaInicioHora ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    <select name="hora_inicio_minuto" class="block w-20 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2" required>
                        @for ($i = 0; $i < 60; $i++)
                            @php $minute = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $minute }}" {{ $minute === $horaInicioMinuto ? 'selected' : '' }}>{{ $minute }}</option>
                        @endfor
                    </select>
                    <select name="hora_inicio_periodo" class="block w-24 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2" required>
                        <option value="AM" {{ $horaInicioPeriodo === 'AM' ? 'selected' : '' }}>AM</option>
                        <option value="PM" {{ $horaInicioPeriodo === 'PM' ? 'selected' : '' }}>PM</option>
                    </select>
                </div>
            </label>

            <label class="block">
                <span class="text-gray-700">Hora fin</span>
                <div class="mt-1 flex gap-2 items-center">
                    <select name="hora_fin_hora" class="block w-20 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2" required>
                        <option value="" disabled {{ $horaFinHora === '' ? 'selected' : '' }}>HH</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ (string)$i === (string)$horaFinHora ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    <select name="hora_fin_minuto" class="block w-20 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2" required>
                        @for ($i = 0; $i < 60; $i++)
                            @php $minute = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $minute }}" {{ $minute === $horaFinMinuto ? 'selected' : '' }}>{{ $minute }}</option>
                        @endfor
                    </select>
                    <select name="hora_fin_periodo" class="block w-24 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2" required>
                        <option value="AM" {{ $horaFinPeriodo === 'AM' ? 'selected' : '' }}>AM</option>
                        <option value="PM" {{ $horaFinPeriodo === 'PM' ? 'selected' : '' }}>PM</option>
                    </select>
                </div>
            </label>
        </div>
    </div>

    {{-- ================================Campo de Descripción================================== --}}
    <label class="block">
        <span class="text-gray-700">Descripción (opcional)</span>
        <textarea name="descripcion" rows="3" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('descripcion', $horario->descripcion ?? '') }}</textarea>
    </label>

    {{-- El sistema backend impone automáticamente el estado de actividad basado en si este bloque pertenece a un grupo activo --}}

    {{-- ================================Botones de Acción================================== --}}
                        <div class="flex justify-end gap-3 mt-4">
                            <a href="{{ route('horarios.index', isset($grupoRetorno) && $grupoRetorno ? ['grupo' => $grupoRetorno] : []) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition">
                                Cancelar
                            </a>

                            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                                {{ $horario ? 'Actualizar bloque' : 'Agregar bloque' }}
                            </button>
                        </div>
    </div>
</form>
