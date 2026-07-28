<?php
$bladePath = 'resources/views/plantillas_globales/index.blade.php';
$content = file_get_contents($bladePath);

$xData = <<<EOT
            <div class="bg-white dark:bg-gray-800 rounded-[32px] p-8 shadow-sm border border-slate-100 dark:border-gray-700 pb-24" x-data="{
                secciones: @js(\$seccionesAlpine),
                isEditing: {{ \$plantilla->status == 2 ? 'true' : 'false' }},
                showModal: false,

                agregarSeccion() {
                    if(!this.isEditing) return;
                    this.secciones.push({
                        titulo: '',
                        descripcion_general: '',
                        numCampos: 1,
                        segmentos: ['']
                    });
                },

                eliminarSeccion(index) {
                    if(!this.isEditing) return;
                    if (this.secciones.length > 1) {
                        this.secciones.splice(index, 1);
                    }
                },

                actualizarSegmentos(seccion) {
                    if(!this.isEditing) return;
                    let n = parseInt(seccion.numCampos);
                    if (n < 1) n = 1;
                    if (n > 10) n = 10;
                    seccion.numCampos = n;

                    while (seccion.segmentos.length < n) {
                        seccion.segmentos.push('');
                    }
                    if (seccion.segmentos.length > n) {
                        seccion.segmentos = seccion.segmentos.slice(0, n);
                    }
                },
                
                guardar() {
                    if('{{ \$plantilla->status }}' == '1') {
                        this.showModal = true;
                    } else {
                        document.getElementById('formPlantilla').submit();
                    }
                },
                
                submitForm(aplicarTodos) {
                    document.getElementById('aplicar_a_todos_input').value = aplicarTodos ? '1' : '0';
                    document.getElementById('formPlantilla').submit();
                }
            }">
EOT;

$content = preg_replace('/<div class="bg-white dark:bg-gray-800[^>]*x-data="\{.*?\n            \}">/s', $xData, $content);

// replace name="titulo" with x-bind:readonly="!isEditing"
$content = preg_replace('/name="titulo" required class="(.*?)"/', 'name="titulo" required x-bind:readonly="!isEditing" class="$1" :class="!isEditing ? \'border-transparent bg-transparent pl-0 focus:ring-0\' : \'\'"', $content);
$content = preg_replace('/name="descripcion" class="(.*?)"/', 'name="descripcion" x-bind:readonly="!isEditing" class="$1" :class="!isEditing ? \'border-transparent bg-transparent pl-0 focus:ring-0\' : \'\'"', $content);

// replace seccion.titulo input
$content = preg_replace('/x-model="seccion\.titulo" required\s+class="(.*?)"/s', 'x-model="seccion.titulo" required x-bind:readonly="!isEditing" class="$1" :class="!isEditing ? \'border-transparent bg-transparent pl-0 focus:ring-0\' : \'\'"', $content);

// replace seccion.descripcion_general input
$content = preg_replace('/x-model="seccion\.descripcion_general"\s+class="(.*?)"/s', 'x-model="seccion.descripcion_general" x-bind:readonly="!isEditing" class="$1" :class="!isEditing ? \'border-transparent bg-transparent pl-0 focus:ring-0\' : \'\'"', $content);

// replace segmentos input
$content = preg_replace('/x-model="seccion\.segmentos\[segIndex\]"([^>]*?)\s+class="(.*?)"/s', 'x-model="seccion.segmentos[segIndex]"$1 x-bind:readonly="!isEditing" class="$2" :class="!isEditing ? \'border-transparent bg-transparent pl-0 focus:ring-0\' : \'\'"', $content);

// Add the modal logic at the bottom of the form
$modalHtml = <<<EOT
                        {{-- Modal Confirmación --}}
                        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm">
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl max-w-md w-full mx-4 border border-slate-100 dark:border-gray-700" @click.away="showModal = false">
                                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">¿Estás seguro de guardar estos cambios?</h3>
                                <p class="text-sm text-slate-600 dark:text-gray-400 mb-6">
                                    Al hacerlo, los cambios podrán aplicarse en todas las historias clínicas ya existentes. Sin embargo, si no lo deseas, los cambios solo se aplicarán para los siguientes expedientes clínicos futuros.
                                </p>
                                <input type="hidden" name="aplicar_a_todos" id="aplicar_a_todos_input" value="0">
                                
                                <div class="flex flex-col gap-3">
                                    <button type="button" @click="submitForm(true)" class="w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-colors text-sm">
                                        Sí, aplicar a todos
                                    </button>
                                    <button type="button" @click="submitForm(false)" class="w-full px-4 py-3 bg-slate-100 dark:bg-gray-700 hover:bg-slate-200 dark:hover:bg-gray-600 text-slate-700 dark:text-gray-200 font-bold rounded-xl transition-colors text-sm">
                                        Solo aplicar a expedientes futuros
                                    </button>
                                    <button type="button" @click="showModal = false" class="mt-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-gray-300 uppercase tracking-widest text-center w-full">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
EOT;

// replace form tag to have id
$content = str_replace('<form action="{{ route(\'plantillas-globales.update\') }}" method="POST">', '<form id="formPlantilla" action="{{ route(\'plantillas-globales.update\') }}" method="POST">', $content);

// inject modal before submit area
$content = str_replace('{{-- Submit --}}', $modalHtml . "\n                        {{-- Submit --}}", $content);

// Remove the old submit area completely
$content = preg_replace('/\{\{-- Submit --\}\}.*?<\/form>/s', '</form>', $content);

// Add the floating buttons container
$floatingButtons = <<<EOT
                    {{-- Floating Buttons --}}
                    <div class="fixed bottom-8 right-8 z-50 flex items-center gap-3 bg-white dark:bg-gray-800 p-2 rounded-full shadow-2xl border border-slate-100 dark:border-gray-700">
                        <template x-if="isEditing">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="isEditing = false; if('{{ \$plantilla->status }}' == '2') isEditing = true;" title="Cancelar" class="w-12 h-12 flex items-center justify-center rounded-full bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm" x-show="'{{ \$plantilla->status }}' == '1'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <button type="button" @click="guardar()" title="{{ \$plantilla->status == 1 ? 'Guardar' : 'Guardar y Activar' }}" class="w-12 h-12 flex items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="!isEditing">
                            <button type="button" @click="isEditing = true" title="Editar" class="w-12 h-12 flex items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                        </template>
                    </div>
                </form>
EOT;

$content = str_replace('</form>', $floatingButtons, $content);

// Make 'Agregar Sección' and 'eliminarSeccion' buttons show only when isEditing
$content = str_replace('<button type="button" @click="agregarSeccion()"', '<button type="button" x-show="isEditing" @click="agregarSeccion()"', $content);
$content = str_replace('<button type="button" @click="eliminarSeccion(secIndex)" x-show="secciones.length > 1"', '<button type="button" @click="eliminarSeccion(secIndex)" x-show="isEditing && secciones.length > 1"', $content);

// Make '+/-' buttons show only when isEditing
$content = preg_replace('/<button type="button" @click="if\(seccion\.numCampos\s*>\s*1\)\s*\{\s*seccion\.numCampos--;\s*actualizarSegmentos\(seccion\);\s*\}"/', '<button type="button" x-show="isEditing" @click="if(seccion.numCampos > 1) { seccion.numCampos--; actualizarSegmentos(seccion); }"', $content);
$content = preg_replace('/<button type="button" @click="if\(seccion\.numCampos\s*<\s*10\)\s*\{\s*seccion\.numCampos\+\+;\s*actualizarSegmentos\(seccion\);\s*\}"/', '<button type="button" x-show="isEditing" @click="if(seccion.numCampos < 10) { seccion.numCampos++; actualizarSegmentos(seccion); }"', $content);

file_put_contents($bladePath, $content);
echo "Replaced content successfully!\n";
