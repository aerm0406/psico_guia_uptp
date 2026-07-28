<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        .font-inter { font-family: 'Inter', sans-serif; }
        
        .login-input {
            transition: all 0.2s ease-in-out;
            background-color: #f8fafc; /* slate-50 */
            border: 1px solid #e2e8f0; /* slate-200 */
            color: #0f172a; /* slate-900 */
        }

        .login-input:focus {
            background-color: #ffffff;
            border-color: #38bdf8; /* sky-400 */
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.1);
        }

        .btn-primary {
            background-color: #0ea5e9; /* sky-500 */
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #0284c7; /* sky-600 */
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3);
        }
    </style>

    <div class="min-h-screen flex items-center justify-center font-inter antialiased bg-white p-6">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="mb-10 text-center">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Preguntas de Seguridad</h2>
                <p class="text-slate-500 mt-4 font-medium text-sm leading-relaxed">
                    Responde a las siguientes preguntas para verificar tu identidad y poder restablecer tu contraseña.
                </p>
            </div>

            <x-auth-session-status class="mb-6 text-center" :status="session('status')" />
            
            <x-input-error :messages="$errors->get('respuestas')" class="mb-6 text-center" />

            <form method="POST" action="{{ route('password.questions.store') }}" class="space-y-6">
                @csrf
                
                @foreach($indices as $index)
                <div class="space-y-2">
                    <label for="respuesta_{{ $index }}" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] px-1 block text-center">
                        {{ $questions[$index] }}
                    </label>
                    <div class="relative max-w-sm mx-auto">
                        <input id="respuesta_{{ $index }}" type="password" name="respuesta_{{ $index }}" required
                            class="login-input block w-full px-5 py-4 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none text-center"
                            placeholder="Tu respuesta">
                        <button type="button" onclick="toggleVisibility('respuesta_{{ $index }}', 'eye_icon_{{ $index }}')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-sky-500 focus:outline-none">
                            <svg id="eye_icon_{{ $index }}" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('respuesta_' . $index)" class="mt-1 text-center" />
                </div>
                @endforeach

                <div class="pt-4 max-w-sm mx-auto">
                    <button type="submit" class="btn-primary w-full py-4 px-6 rounded-xl text-white font-black text-sm shadow-lg shadow-sky-900/10 uppercase tracking-widest transition-all active:scale-[0.98]">
                        Verificar Respuestas
                    </button>
                </div>

                <div class="text-center pt-4">
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-slate-400 hover:text-sky-600 transition-colors">
                        <span class="text-sky-500 underline decoration-sky-200 underline-offset-4">Regresar</span>
                    </a>
                </div>
            </form>

            <div class="mt-16 pt-8 border-t border-slate-50 text-center">
                <p class="text-slate-300 text-[10px] font-black uppercase tracking-[0.3em]">Psico-Guía UPTP © 2026</p>
            </div>
        </div>
    </div>

    <script>
        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>
</x-guest-layout>
