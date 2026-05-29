<div id="blockRequestsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm px-3 transition-all duration-300">
    <div class="w-full max-w-4xl rounded-3xl bg-white shadow-2xl overflow-hidden border border-slate-100 transform transition-all">
        <!-- Header con el azul de la Agenda -->
        <div class="bg-sky-700 text-white px-6 py-5 relative shadow-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button id="prevBlockBtn" class="flex items-center justify-center text-white bg-white/20 hover:bg-white/30 rounded-xl h-10 w-10 transition-all active:scale-95 backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold uppercase tracking-widest text-sky-100 opacity-80">Gestionar Horario</span>
                        <span id="blockModalHeader" class="text-2xl font-black tracking-tight leading-tight">Bloque</span>
                    </div>
                    <button id="nextBlockBtn" class="flex items-center justify-center text-white bg-white/20 hover:bg-white/30 rounded-xl h-10 w-10 transition-all active:scale-95 backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
                <button id="closeBlockModal" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors backdrop-blur-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <div class="p-6 md:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- Columna de Candidatos -->
                <div class="flex flex-col h-full">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Candidatos Disponibles</h3>
                        <span class="px-2 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-bold">LISTA DE ESPERA</span>
                    </div>
                    <ul id="blockModalRequestsList" class="space-y-4 max-h-[400px] overflow-y-auto pr-3 custom-scrollbar"></ul>
                    <div id="blockModalEmptyMessage" class="flex flex-col items-center justify-center py-12 px-4 border-2 border-dashed border-slate-100 rounded-3xl bg-slate-50/50 hidden">
                        <div class="bg-white p-3 rounded-full shadow-sm mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <p class="text-sm text-slate-400 font-medium text-center">No hay candidatos para este bloque.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Arrastra un paciente aquí para postularlo.</p>
                    </div>
                </div>

                <!-- Columna de Paciente Asignado -->
                <div class="flex flex-col h-full">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Estado de Confirmación</h3>
                        <div id="statusBadgePlaceholder"></div>
                    </div>
                    
                    <div id="blockModalAssignedContainer" class="flex-grow">
                        <ul id="blockModalAssignedList" class="space-y-4"></ul>
                        
                        <div id="blockModalAssignedEmptyMessage" class="flex flex-col items-center justify-center py-12 px-4 border-2 border-dashed border-slate-100 rounded-3xl bg-slate-50/50">
                            <div class="bg-white p-3 rounded-full shadow-sm mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <p class="text-sm text-slate-400 font-medium text-center">No hay paciente confirmado aún.</p>
                        </div>

                        <div id="blockModalAssignedInfo" class="mt-4 p-4 bg-indigo-50 border border-indigo-100 rounded-2xl hidden">
                            <div class="flex gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                <p class="text-xs text-indigo-700 leading-relaxed font-medium">Este bloque ya está reservado. Si deseas cambiar de paciente, primero debes cancelar la cita actual.</p>
                            </div>
                        </div>

                        <!-- Acciones Rápidas -->
                        <div id="blockModalAssignedActions" class="mt-8 pt-6 border-t border-slate-100 hidden">
                            <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4">Gestión de Cita</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <button type="button" id="blockModalMarkRealizada" class="block-request-action-btn group flex flex-col items-center justify-center p-3 rounded-2xl border border-emerald-100 bg-emerald-50/50 hover:bg-emerald-50 transition-all active:scale-95" data-action="complete" data-cita-id="">
                                    <div class="bg-emerald-500 text-white p-2 rounded-xl mb-2 shadow-sm group-hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <span class="text-[11px] font-black text-emerald-700 uppercase tracking-tight">Realizada</span>
                                </button>
                                <button type="button" id="blockModalMarkNoAsistio" class="block-request-action-btn group flex flex-col items-center justify-center p-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition-all active:scale-95" data-action="no_asistio" data-cita-id="">
                                    <div class="bg-slate-100 text-slate-500 p-2 rounded-xl mb-2 shadow-sm group-hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </div>
                                    <span class="text-[11px] font-black text-slate-600 uppercase tracking-tight">Ausente</span>
                                </button>
                                <button type="button" id="blockModalCancelConfirmed" class="block-request-action-btn group flex flex-col items-center justify-center p-3 rounded-2xl border border-rose-100 bg-rose-50/50 hover:bg-rose-50 transition-all active:scale-95" data-action="cancel_confirmada" data-cita-id="">
                                    <div class="bg-rose-500 text-white p-2 rounded-xl mb-2 shadow-sm group-hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </div>
                                    <span class="text-[11px] font-black text-rose-700 uppercase tracking-tight">Cancelar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
