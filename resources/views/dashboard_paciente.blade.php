<x-app-layout>
    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-[calc(50%-80px)_1fr] gap-5 items-stretch">

                <!-- Columna Izquierda: Bienvenida y Agendar Cita -->
                <div class="bg-[#eef4fb] dark:bg-slate-800 rounded-[28px] shadow-sm border border-gray-100 dark:border-slate-700 flex flex-col overflow-hidden">

                    <!-- Encabezado -->
                    <div class="flex justify-between items-start px-6 sm:px-7 pt-6 sm:pt-7 pb-2">
                        <div class="max-w-[75%]">
                            <h2 class="text-[22px] sm:text-[26px] font-black text-gray-900 dark:text-white leading-[1.2] tracking-tight">
                                {{ $saludo ?? 'Buenos días' }},<br>
                                {{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}.
                            </h2>
                            <p class="mt-2 text-[13px] sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                Es un placer, en Psico-Guia estamos aquí para apoyarte con tu bienestar.
                            </p>
                        </div>
                        <div class="text-gray-400 dark:text-gray-500 shrink-0 mt-1">
                            <svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.3">
                                <rect x="3" y="4" width="18" height="18" rx="2.5" ry="2.5"></rect>
                                <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"></path>
                                <circle cx="18" cy="18" r="4.5" fill="#eef4fb" stroke="currentColor" stroke-width="1.3"></circle>
                                <path stroke-linecap="round" stroke-width="1.5" d="M18 15.8v4.4M15.8 18h4.4"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Ilustración a sangre completa, altura fija + curva inferior -->
                    <div class="relative h-[240px] sm:h-[280px] overflow-hidden">
                        <img
                            src="{{ asset('img/' . (auth()->user()->genero === 'Masculino' ? 'therapy_illustration_masc.png' : 'therapy_illustration.png')) }}"
                            alt=""
                            class="absolute inset-0 w-full h-full object-cover object-[50%_60%]"
                        >
                        <!-- Degradado que funde el texto de arriba con la foto -->
                        <div class="absolute inset-x-0 top-0 h-10 bg-gradient-to-b from-[#eef4fb] dark:from-slate-800 to-transparent pointer-events-none"></div>
                        <!-- Curva inferior que funde la foto con la tarjeta -->
                        <svg class="absolute bottom-0 left-0 w-full h-[36px] sm:h-[44px]" viewBox="0 0 400 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0,32 C90,60 150,8 220,20 C290,32 330,55 400,28 L400,60 L0,60 Z" fill="#eef4fb" class="dark:fill-slate-800"></path>
                        </svg>
                    </div>

                    <!-- Botón Agendar -->
                    <div class="px-6 sm:px-7 pt-1 pb-3 mt-auto relative">
                        <a href="{{ route('citas.create') }}" class="w-full flex items-center justify-center gap-2.5 py-3.5 bg-[#2563eb] hover:bg-[#1d4ed8] active:bg-[#1e40af] text-white font-semibold rounded-2xl transition-all duration-200 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 text-[15px]">
                            Agendar Nueva Cita
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M12 11v6m-3-3h6"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Columna Derecha: Cuadrícula Interactiva -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 h-full">

                    <!-- Widget 1: Citas en Gestión -->
                    <div class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 p-5 flex flex-col">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-[15px] font-bold text-gray-900 dark:text-white">Citas en Gestión</h3>
                        </div>
                        <div class="flex-1 flex flex-col justify-center">
                            @if(empty($proximaCita) && empty($citaPendiente))
                                <p class="text-sm text-gray-500 text-center">No hay citas en proceso.</p>
                            @else
                                <div class="relative pl-4 border-l-2 border-gray-100 dark:border-slate-700 space-y-4">
                                    @if(!empty($proximaCita))
                                    <div class="relative">
                                        <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-teal-500 border-2 border-white dark:border-gray-800"></div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $proximaCita->psicologo_nombre ?? 'Psicólogo' }}</p>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($proximaCita->fecha)->format('d M') }} - {{ $proximaCita->hora ? \Carbon\Carbon::parse($proximaCita->hora)->format('h:i A') : 'TBD' }} <span class="text-teal-600 font-medium">(Confirmada)</span></p>
                                    </div>
                                    @endif
                                    @if(!empty($citaPendiente))
                                    <div class="relative">
                                        <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-amber-400 border-2 border-white dark:border-gray-800"></div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $citaPendiente->psicologo_nombre ?? 'Psicólogo' }}</p>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($citaPendiente->fecha_sugerida ?? $citaPendiente->fecha)->format('d M') }} <span class="text-amber-600 font-medium">(Pendiente)</span></p>
                                    </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('citas.index') }}" class="relative block mt-4 text-sm text-teal-600 hover:text-teal-700 font-semibold text-center py-2 bg-teal-50 hover:bg-teal-100 dark:bg-teal-900/30 rounded-xl transition-colors ">
                            ir allá
                            @if(isset($notificacionCita) && $notificacionCita)
                                @php
                                    $esAprobada = str_contains($notificacionCita->type, 'Confirmed');
                                    $mensajeNotif = $esAprobada ? '¡Tu cita ha sido aprobada!' : ' cita rechazada';
                                    $colorMascota = $esAprobada ? 'text-amber-400' : 'text-blue-300';
                                @endphp
                                <!-- Notificación Estilo Manga Flotante sobre el Botón -->
                                <div class="absolute left-[75%] -translate-x-1/2 z-20 flex flex-col items-center @if($esAprobada) animate-bounce @endif pointer-events-none drop-shadow-xl" style="top: -110px;">
                                    <!-- Globo de texto -->
                                    <div class="bg-white dark:bg-gray-800 border-[2px] border-slate-200 dark:border-gray-600 text-slate-800 dark:text-white text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-2xl shadow-sm relative text-center max-w-[120px] leading-tight">
                                        {{ $mensajeNotif }}
                                        <!-- Pico del globo -->
                                        <div class="absolute -bottom-[5px] left-1/4 -translate-x-1/2 w-2.5 h-2.5 bg-white dark:bg-gray-800 border-b-[2px] border-r-[2px] border-slate-200 dark:border-gray-600 transform rotate-45"></div>
                                    </div>
                                    <!-- Personaje SVG -->
                                    <div class="mt-2 {{ $colorMascota }}">
                                        <!-- SVG de Estrellita -->
                                        <svg class="w-10 h-10 drop-shadow-md" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                            @if($esAprobada)
                                                <!-- Carita Feliz (Ojos y boca en trazo oscuro) -->
                                                <path d="M10 12h.01M14 12h.01M10 14a2 2 0 004 0H10z" stroke="#333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
                                            @else
                                                <!-- Carita Triste -->
                                                <!-- Ojos -->
                                                <path d="M9 13h.01M15 13h.01" stroke="#333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <!-- Boca triste (curva hacia abajo) -->
                                                <path d="M10 16q2-3 4 0" stroke="#333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
                                            @endif
                                        </svg>
                                    </div>
                                </div>
                            @elseif(!empty($citaPendiente) && empty($proximaCita))
                                <!-- Estrellita Parpadeante para cita pendiente -->
                                <style>
                                    @keyframes blink-eyes { 0%, 96%, 98% { opacity: 1; } 97% { opacity: 0; } }
                                    .animate-blink { animation: blink-eyes 3s infinite; transform-origin: center; }
                                </style>
                                <div class="absolute left-[75%] -translate-x-1/2 w-10 h-10 text-amber-400 pointer-events-none z-10" style="top: -32px;">
                                    <svg class="w-full h-full drop-shadow-md" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                        <!-- Carita esperando con pestañeo -->
                                        <g class="animate-blink">
                                            <path d="M10 12h.01M14 12h.01" stroke="#333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                        <!-- Boca neutra -->
                                        <path d="M10 15h4" stroke="#333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
                                    </svg>
                                </div>
                            @endif
                        </a>
                    </div>

                    <!-- Widget 2: Resumen de Actividad (Calendario) -->
                    <div class="bg-gradient-to-br from-blue-50/50 to-blue-50/50 dark:from-slate-800 dark:to-slate-800 rounded-3xl shadow-sm border border-blue-100 dark:border-slate-700 p-5 flex flex-col">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <h3 class="text-[15px] font-bold text-gray-900 dark:text-white">Actividad</h3>
                        </div>
                        
                        <div class="flex flex-col gap-3">
                            <div class="bg-white/80 dark:bg-gray-900/50 rounded-2xl p-2.5 shadow-sm border border-white dark:border-slate-700 flex-1">
                                <div class="grid grid-cols-7 gap-1 text-center mb-1">
                                    @foreach(['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $d)
                                        <div class="text-[9px] font-bold text-gray-400">{{ $d }}</div>
                                    @endforeach
                                </div>
                                <div class="grid grid-cols-7 gap-x-1 gap-y-0.5 text-center">
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $start = $now->copy()->startOfMonth();
                                        $startDay = $start->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
                                        $daysInMonth = $now->daysInMonth;
                                        $today = $now->day;
                                        
                                        // Prev month padding
                                        for ($i = 1; $i < $startDay; $i++) {
                                            echo '<div class="text-xs text-transparent">.</div>';
                                        }
                                        // Current month
                                        for ($i = 1; $i <= $daysInMonth; $i++) {
                                            $isToday = $i === $today;
                                            $classes = $isToday 
                                                ? 'bg-blue-500 text-white rounded-full font-bold shadow-sm shadow-blue-200' 
                                                : 'text-gray-600 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full';
                                            echo '<div class="text-[10px] w-5 h-5 flex items-center justify-center mx-auto ' . $classes . '">' . $i . '</div>';
                                        }
                                    @endphp
                                </div>
                            </div>
                            <div class="px-1">
                                <p class="text-[11px] text-gray-500 font-medium">Hoy, {{ $now->translatedFormat('d F') }}</p>
                                @if(!empty($proximaCita) && \Carbon\Carbon::parse($proximaCita->fecha)->isToday())
                                    <p class="text-sm font-bold text-blue-700 dark:text-blue-400">1 Sesión programada</p>
                                @else
                                    <p class="text-sm font-bold text-blue-700 dark:text-blue-400">Sin sesiones hoy</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Widget 3: Check-in de Bienestar -->
                    <div class="bg-gradient-to-br from-blue-50/50 to-orange-50/50 dark:from-slate-800 dark:to-slate-800 rounded-3xl shadow-sm border border-blue-100 dark:border-slate-700 p-5 flex flex-col">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </div>
                            <h3 class="text-[15px] font-bold text-gray-900 dark:text-white">Estado de Ánimo</h3>
                        </div>
                        
                        <div class="flex-1 flex flex-col justify-center">
                            @if($estadoAnimoHoy)
                                @php
                                    $valorAnimo = $estadoAnimoHoy->valor ?? 5;
                                @endphp
                                <div class="text-center py-2">
                                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400 mb-2">Ya registraste tu estado:</p>
                                    <div class="inline-flex items-center justify-center text-4xl" title="Nivel de ánimo: {{ $valorAnimo }}" id="saved-mood-icon">
                                        <!-- SVG will be injected here via JS -->
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            document.getElementById('saved-mood-icon').innerHTML = getMoodSVG({{ $valorAnimo }});
                                        });
                                    </script>
                                </div>
                            @else
                                <p class="text-[11px] text-gray-600 dark:text-gray-400 text-center mb-3">Del 1 al 10 ¿Qué tan bien te sientes?</p>
                                <form action="{{ route('estado_animo_diario.store') }}" method="POST">
                                    @csrf
                                    <div class="relative px-2">
                                        <input type="range" name="valor" min="1" max="10" value="7" class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-500" oninput="document.getElementById('mood-emoji').innerHTML = getMoodSVG(this.value);">
                                        <div class="flex justify-between text-[10px] text-gray-400 mt-1.5 px-0.5">
                                            <span>1</span><span>10</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center" id="mood-emoji">
                                        <!-- SVG will be injected here via JS -->
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            document.getElementById('mood-emoji').innerHTML = getMoodSVG(7);
                                        });
                                    </script>
                                    <button type="submit" class="w-full mt-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">Guardar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <script>
                        function getMoodSVG(value) {
                            value = parseInt(value);
                            const baseClass = "w-12 h-12 mx-auto transition-colors duration-300";
                            switch(value) {
                                case 1:
                                    // 1: Angry (Red)
                                    return `<svg class="${baseClass} text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><path d="M7.5 8L10 9.5"/><path d="M16.5 8L14 9.5"/><line x1="9" y1="12" x2="9.01" y2="12" stroke-width="3"/><line x1="15" y1="12" x2="15.01" y2="12" stroke-width="3"/></svg>`;
                                case 2:
                                    // 2: Crying (Red)
                                    return `<svg class="${baseClass} text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/><path d="M9 12v2.5l-.5 1-1-1v-2.5z" fill="currentColor" stroke="none"/></svg>`;
                                case 3:
                                    // 3: Sad (Orange)
                                    return `<svg class="${baseClass} text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
                                case 4:
                                    // 4: Confused (Orange)
                                    return `<svg class="${baseClass} text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 15l8 -1.5"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
                                case 5:
                                    // 5: Neutral (Yellow)
                                    return `<svg class="${baseClass} text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="15" x2="16" y2="15"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
                                case 6:
                                    // 6: Slight smile (Yellow)
                                    return `<svg class="${baseClass} text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9 14.5c1 1.5 2 2 3 2s2-.5 3-2"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
                                case 7:
                                    // 7: Smile (Lime)
                                    return `<svg class="${baseClass} text-lime-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
                                case 8:
                                    // 8: Grin / Open mouth (Green)
                                    return `<svg class="${baseClass} text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 3 4 3 4-3 4-3v-1H8v1z" fill="currentColor"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
                                case 9:
                                    // 9: Star-struck (Teal)
                                    return `<svg class="${baseClass} text-teal-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><polygon points="9 7 9.5 8.5 11 8.5 9.8 9.4 10.2 11 9 10 7.8 11 8.2 9.4 7 8.5 8.5 8.5" fill="currentColor"/><polygon points="15 7 15.5 8.5 17 8.5 15.8 9.4 16.2 11 15 10 13.8 11 14.2 9.4 13 8.5 14.5 8.5" fill="currentColor"/></svg>`;
                                case 10:
                                    // 10: Heart eyes (Blue)
                                    return `<svg class="${baseClass} text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 3 4 3 4-3 4-3v-1H8v1z" fill="currentColor"/><path d="M9 7.5a1.5 1.5 0 011.5 1.5c0 1.5-1.5 2.5-1.5 2.5S7.5 10.5 7.5 9A1.5 1.5 0 019 7.5z" fill="currentColor" stroke="none"/><path d="M15 7.5a1.5 1.5 0 011.5 1.5c0 1.5-1.5 2.5-1.5 2.5S13.5 10.5 13.5 9A1.5 1.5 0 0115 7.5z" fill="currentColor" stroke="none"/></svg>`;
                            }
                            return '';
                        }
                    </script>

                    <!-- Widget 4: Anuncios y Noticias -->
                    <div class="bg-gradient-to-br from-slate-50 to-green-50/50 dark:from-slate-800 dark:to-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 p-5 flex flex-col">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                            </div>
                            <h3 class="text-[15px] font-bold text-gray-900 dark:text-white">Anuncios</h3>
                        </div>
                        <div class="flex-1 overflow-y-auto pr-1 space-y-3">
                            @if(isset($publicaciones) && $publicaciones->count() > 0)
                                @foreach($publicaciones as $pub)
                                    <div class="pb-2 border-b border-gray-100 dark:border-slate-700 last:border-0 last:pb-0">
                                        <div class="flex items-start gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-green-500 mt-1.5 shrink-0"></div>
                                            <div class="flex-1 flex items-start justify-between gap-2">
                                                <span class="text-[13px] font-semibold text-gray-800 dark:text-gray-200 leading-snug line-clamp-2">
                                                    {{ $pub->titulo }}
                                                </span>
                                                <a href="{{ route('mural.index') }}#pub-{{ $pub->id }}" class="text-blue-500 hover:text-blue-700 mt-0.5 shrink-0" title="Ver publicación">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-xs text-gray-500 text-center mt-4">Sin anuncios recientes.</p>
                            @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>

<style>
@keyframes roll-back-and-forth {
    0% {
        left: 5%;
        transform: rotate(0deg);
    }
    50% {
        left: 85%;
        transform: rotate(360deg);
    }
    100% {
        left: 5%;
        transform: rotate(0deg);
    }
}
.animate-roll {
    animation: roll-back-and-forth 5s ease-in-out infinite;
}
</style>
