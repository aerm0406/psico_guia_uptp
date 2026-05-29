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
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">¿Olvidaste tu contraseña?</h2>
                <p class="text-slate-500 mt-4 font-medium text-sm leading-relaxed">
                    No hay problema. Solo indícanos tu dirección de correo electrónico y te enviaremos un enlace para restablecerla.
                </p>
            </div>

            <x-auth-session-status class="mb-6 text-center" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf
                
                <div class="space-y-2">
                    <label for="email" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1 block text-center">Correo Electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="login-input block w-full px-5 py-4 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none text-center"
                        placeholder="usuario@uptp.edu.ve">
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-center" />
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full py-4 px-6 rounded-xl text-white font-black text-sm shadow-lg shadow-sky-900/10 uppercase tracking-widest transition-all active:scale-[0.98]">
                        Enviar Enlace
                    </button>
                </div>

                <div class="text-center pt-4">
                    <a href="{{ route('login') }}" class="text-xs font-bold text-slate-400 hover:text-sky-600 transition-colors">
                        <span class="text-sky-500 underline decoration-sky-200 underline-offset-4">Regresar al inicio de sesión</span>
                    </a>
                </div>
            </form>

            <div class="mt-16 pt-8 border-t border-slate-50 text-center">
                <p class="text-slate-300 text-[10px] font-black uppercase tracking-[0.3em]">Psico-Guía UPTP © 2026</p>
            </div>
        </div>
    </div>
</x-guest-layout>
