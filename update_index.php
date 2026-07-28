<?php
$file = 'resources/views/publicaciones/index.blade.php';
$content = file_get_contents($file);

$oldImg = '<img src="{{ route(\'media.publicaciones\', basename($pub->media_path)) }}" class="w-full h-auto object-cover max-h-[500px]">';
$newImg = '<img @click="$dispatch(\'open-image-modal\', \'{{ route(\'media.publicaciones\', basename($pub->media_path)) }}\')" src="{{ route(\'media.publicaciones\', basename($pub->media_path)) }}" class="w-full h-auto object-cover max-h-[500px] cursor-pointer hover:opacity-90 transition-opacity">';
$content = str_replace($oldImg, $newImg, $content);

$modalHtml = <<<EOT
        </div>

        <!-- Image Modal -->
        <div x-data="{ open: false, src: '' }"
             @open-image-modal.window="src = \$event.detail; open = true"
             x-show="open" 
             style="display: none;" 
             class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="open = false"
             @click.self="open = false">
             
             <!-- Close Button -->
             <button @click="open = false" class="absolute top-4 right-4 text-white hover:text-gray-300 p-2 rounded-full bg-black/50 transition-colors">
                 <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
             </button>
             
             <!-- Modal Image -->
             <img :src="src" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl" @click.stop>
        </div>
    </div>
</x-app-layout>
EOT;

$content = str_replace("        </div>\n    </div>\n</x-app-layout>", $modalHtml, $content);
file_put_contents($file, $content);
echo "index.blade.php updated.";
