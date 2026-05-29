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

        .side-panel {
            background-color: #f0f9ff; /* sky-50 */
            background-image: radial-gradient(circle at 2px 2px, rgba(14, 165, 233, 0.05) 1px, transparent 0);
            background-size: 24px 24px;
        }
    </style>

    <div class="min-h-screen flex font-inter antialiased bg-white">
        <!-- Left Side: Professional Branding -->
        <div class="hidden lg:flex lg:w-1/2 side-panel relative overflow-hidden items-start justify-center pt-18 p-12 border-r border-sky-100">
            <div class="relative z-10 w-full max-w-lg">
                <div class="mb-12">
                    <div class="inline-flex p-4 bg-white rounded-2xl border border-sky-200 mb-8 shadow-sm">
                        <img src="{{ asset('img/LOGO-DE-PSICOLOGIA-GRISOSCURO.png') }}" alt="Logo" class="w-12 h-12" />
                    </div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-6">Psico-Guía</h1>
                    <div class="h-1.5 w-20 bg-sky-500 rounded-full mb-8"></div>
                    <p class="text-xl text-slate-600 leading-relaxed font-medium">
                        Plataforma profesional para la gestión de atención psicológica y seguimiento clínico institucional.
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 flex-shrink-0 w-8 h-8 rounded-xl bg-white flex items-center justify-center border border-sky-200 shadow-sm">
                            <svg class="w-4 h-4 text-sky-500" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-slate-900 font-bold text-sm">Seguridad de Datos</h3>
                            <p class="text-slate-500 text-xs">Encriptación de grado clínico para toda la información sensible.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="mt-1 flex-shrink-0 w-8 h-8 rounded-xl bg-white flex items-center justify-center border border-sky-200 shadow-sm">
                            <svg class="w-4 h-4 text-sky-500" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-slate-900 font-bold text-sm">Gestión Eficiente</h3>
                            <p class="text-slate-500 text-xs">Optimización de agenda y recursos de atención psicológica.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Clean Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col items-center justify-start pt-32 p-8 lg:p-24 bg-white">
            <div class="w-full max-w-md">
                <!-- Header -->
                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight">Acceso al Sistema</h2>
                    <p class="text-slate-500 mt-3 font-medium">Por favor, ingrese sus credenciales autorizadas.</p>
                </div>

                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('login', absolute: false) }}" class="space-y-6">
                    @csrf
                    
                    <div class="space-y-2">
                        <label for="email" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Correo Electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="login-input block w-full px-5 py-4 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none"
                            placeholder="usuario@uptp.edu.ve">
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Contraseña</label>
                        <input id="password" type="password" name="password" required
                            class="login-input block w-full px-5 py-4 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none"
                            placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary w-full py-4 px-6 rounded-xl text-white font-black text-sm shadow-lg shadow-sky-900/10 uppercase tracking-widest transition-all active:scale-[0.98]">
                            Iniciar Sesión
                        </button>
                    </div>

                    <div class="flex items-center justify-between px-1 pt-4">
                        <label class="flex items-center cursor-pointer group">
                            <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-sky-500 border-slate-300 rounded focus:ring-sky-500">
                            <span class="ml-2 text-xs font-bold text-slate-400 group-hover:text-slate-600 transition-colors">Guardar sesión</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-slate-400 hover:text-sky-600 transition-colors">¿Olvidaste tu contraseña?</a>
                        @endif
                    </div>
                </form>

                <div class="mt-10 text-center">
                    <p class="text-sm text-slate-500">
                        ¿No tienes una cuenta? 
                        <a href="{{ route('register') }}" class="text-sky-500 font-bold hover:underline underline-offset-4">Regístrate aquí</a>
                    </p>
                </div>

                <div class="mt-16 pt-8 border-t border-slate-50 text-center">
                    <p class="text-slate-300 text-[10px] font-black uppercase tracking-[0.3em]">Psico-Guía UPTP © 2026</p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
