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
            background-color: #f0f9ff;
            background-image: radial-gradient(circle at 2px 2px, rgba(14, 165, 233, 0.05) 1px, transparent 0);
            background-size: 24px 24px;
        }

        :root[data-theme="dark"] .side-panel,
        .dark .side-panel {
            background-color: #0f172a !important;
            background-image: radial-gradient(circle at 2px 2px, rgba(14, 165, 233, 0.1) 1px, transparent 0) !important;
        }

        :root[data-theme="dark"] .login-input,
        .dark .login-input {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        :root[data-theme="dark"] .login-input:focus,
        .dark .login-input:focus {
            background-color: #0f172a !important;
            border-color: #38bdf8 !important;
        }

        :root[data-theme="dark"] .dark\:text-white,
        .dark .dark\:text-white { color: #ffffff !important; }

        :root[data-theme="dark"] .dark\:bg-gray-900,
        .dark .dark\:bg-gray-900 { background-color: #111827 !important; }

        :root[data-theme="dark"] .dark\:border-gray-800,
        .dark .dark\:border-gray-800 { border-color: #1f2937 !important; }

        :root[data-theme="dark"] .dark\:border-gray-700,
        .dark .dark\:border-gray-700 { border-color: #374151 !important; }

        :root[data-theme="dark"] .dark\:text-gray-300,
        .dark .dark\:text-gray-300 { color: #d1d5db !important; }

        :root[data-theme="dark"] .dark\:hidden,
        .dark .dark\:hidden { display: none !important; }
        
        :root[data-theme="dark"] .dark\:block,
        .dark .dark\:block { display: block !important; }

    </style>

    <div class="min-h-screen flex font-inter antialiased bg-white dark:bg-gray-900">
        <!-- Left Side: Professional Branding -->
        <div class="hidden lg:flex lg:w-1/2 lg:sticky lg:top-0 lg:h-screen side-panel relative overflow-hidden items-start justify-center pt-18 p-12 border-r border-sky-100 dark:border-gray-800">
            <div class="relative z-10 w-full max-w-lg">
                <div class="mb-12">
                    <div class="inline-flex p-4 bg-white dark:bg-gray-900 rounded-2xl border border-sky-200 dark:border-gray-700 mb-8 shadow-sm">
                        <img src="{{ asset('img/LOGO-DE-PSICOLOGIA-GRISOSCURO.png') }}" alt="Logo" class="w-12 h-12 dark:hidden" /><img src="{{ asset('img/LOGO-DE-PSICOLOGIA-BLANCO.png') }}" alt="Logo" class="w-12 h-12 hidden dark:block" />
                    </div>
                    <h1 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight mb-6">Psico-Guía</h1>
                    <div class="h-1.5 w-20 bg-sky-500 rounded-full mb-8"></div>
                    <p class="text-xl text-slate-600 dark:text-gray-300 leading-relaxed font-medium">
                        Únete a nuestra plataforma profesional para la gestión de atención psicológica y bienestar universitario.
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 flex-shrink-0 w-8 h-8 rounded-xl bg-white dark:bg-gray-900 flex items-center justify-center border border-sky-200 dark:border-gray-700 shadow-sm">
                            <svg class="w-4 h-4 text-sky-500" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-slate-900 dark:text-white font-bold text-sm">Registro Rápido</h3>
                            <p class="text-slate-500 text-xs">Crea tu cuenta en segundos y accede a los servicios institucionales.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="mt-1 flex-shrink-0 w-8 h-8 rounded-xl bg-white dark:bg-gray-900 flex items-center justify-center border border-sky-200 dark:border-gray-700 shadow-sm">
                            <svg class="w-4 h-4 text-sky-500" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-slate-900 dark:text-white font-bold text-sm">Confidencialidad</h3>
                            <p class="text-slate-500 text-xs">Tus datos están protegidos bajo estrictos protocolos de seguridad.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Registration Form -->
        <div class="w-full lg:w-1/2 flex flex-col items-center justify-start pt-20 p-8 lg:p-24 bg-white dark:bg-gray-900 overflow-y-auto">
            <div class="w-full max-w-md">
                <!-- Header -->
                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">Crear Cuenta</h2>
                    <p class="text-slate-500 mt-3 font-medium">Completa tus datos para registrarte en el sistema.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf
                    
                    <!-- Row 1: Nombres y Apellidos -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="nombres" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Nombres</label>
                            <input id="nombres" type="text" name="nombres" value="{{ old('nombres') }}" required autofocus
                                class="login-input block w-full px-4 py-3.5 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none"
                                placeholder="Ej: Juan José">
                            <x-input-error :messages="$errors->get('nombres')" class="mt-1" />
                        </div>
                        <div class="space-y-2">
                            <label for="apellidos" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Apellidos</label>
                            <input id="apellidos" type="text" name="apellidos" value="{{ old('apellidos') }}" required
                                class="login-input block w-full px-4 py-3.5 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none"
                                placeholder="Ej: Rodríguez Marcano">
                            <x-input-error :messages="$errors->get('apellidos')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Row 2: Cédula y Perfil -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="cedula" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Cédula</label>
                            <input id="cedula" type="text" name="cedula" value="{{ old('cedula') }}" required
                                class="login-input block w-full px-4 py-3.5 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none"
                                placeholder="V-00000000">
                            <x-input-error :messages="$errors->get('cedula')" class="mt-1" />
                        </div>
                        <div class="space-y-2">
                            <label for="role" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Perfil</label>
                            <div class="relative">
                                <select id="role" name="role" required 
                                    class="login-input block w-full px-4 py-3.5 rounded-xl text-sm font-medium focus:outline-none appearance-none cursor-pointer">
                                    <option value="" class="text-slate-400">Seleccionar...</option>
                                    <option value="psicologo" {{ old('role') === 'psicologo' ? 'selected' : '' }}>Psicólogo</option>
                                    <option value="paciente" {{ old('role') === 'paciente' ? 'selected' : '' }}>Paciente</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('role')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Correo Electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            class="login-input block w-full px-4 py-3.5 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none"
                            placeholder="correo@ejemplo.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <label for="password" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Contraseña</label>
                        <input id="password" type="password" name="password" required
                            class="login-input block w-full px-5 py-4 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none"
                            placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        
                        <!-- Password Validation Helper -->
                        <div id="password-helper" class="text-[10px] text-slate-500 mt-2 space-y-1 hidden">
                            <p class="font-bold text-slate-700">La contraseña debe tener:</p>
                            <ul class="space-y-1 pl-1">
                                <li id="req-length" class="flex items-center gap-1.5"><svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path></svg> 8 a 16 caracteres</li>
                                <li id="req-upper" class="flex items-center gap-1.5"><svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path></svg> Al menos una mayúscula</li>
                                <li id="req-lower" class="flex items-center gap-1.5"><svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path></svg> Al menos una minúscula</li>
                                <li id="req-num" class="flex items-center gap-1.5"><svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path></svg> Al menos un número</li>
                                <li id="req-spec" class="flex items-center gap-1.5"><svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path></svg> Un carácter especial (@$!%*?&)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Confirmar Contraseña</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            class="login-input block w-full px-5 py-4 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none"
                            placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                    </div>

                    <div class="pt-4">
                        <button type="submit" id="btn-submit" disabled class="btn-primary w-full py-4 px-6 rounded-xl text-white font-black text-sm shadow-lg shadow-sky-900/10 uppercase tracking-widest transition-all opacity-50 cursor-not-allowed">
                            Registrarse Ahora
                        </button>
                    </div>

                    <div class="text-center pt-4">
                        <a href="{{ route('login') }}" class="text-xs font-bold text-slate-400 hover:text-sky-600 transition-colors">
                            ¿Ya tienes una cuenta? <span class="text-sky-500 underline decoration-sky-200 underline-offset-4">Inicia sesión aquí</span>
                        </a>
                    </div>
                </form>

                <div class="mt-16 pt-8 border-t border-slate-50 text-center">
                    <p class="text-slate-300 text-[10px] font-black uppercase tracking-[0.3em]">Psico-Guía UPTP © 2026</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const btnSubmit = document.getElementById('btn-submit');
            const passInput = document.getElementById('password');
            const confInput = document.getElementById('password_confirmation');
            const passHelper = document.getElementById('password-helper');
            
            const reqLength = document.getElementById('req-length');
            const reqUpper = document.getElementById('req-upper');
            const reqLower = document.getElementById('req-lower');
            const reqNum = document.getElementById('req-num');
            const reqSpec = document.getElementById('req-spec');
            
            const svgSuccess = '<svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            const svgWait = '<svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path></svg>';

            function updateRequirement(el, isValid, text) {
                el.innerHTML = (isValid ? svgSuccess : svgWait) + ' <span class="' + (isValid ? 'text-emerald-600 font-bold' : 'text-slate-500') + '">' + text + '</span>';
                return isValid;
            }

            function validatePassword() {
                const val = passInput.value;
                if (val.length > 0) {
                    passHelper.classList.remove('hidden');
                } else {
                    passHelper.classList.add('hidden');
                }

                const isLength = val.length >= 8 && val.length <= 16;
                const isUpper = /[A-Z]/.test(val);
                const isLower = /[a-z]/.test(val);
                const isNum = /[0-9]/.test(val);
                const isSpec = /[@$!%*?&]/.test(val);

                updateRequirement(reqLength, isLength, '8 a 16 caracteres');
                updateRequirement(reqUpper, isUpper, 'Al menos una mayúscula');
                updateRequirement(reqLower, isLower, 'Al menos una minúscula');
                updateRequirement(reqNum, isNum, 'Al menos un número');
                updateRequirement(reqSpec, isSpec, 'Un carácter especial (@$!%*?&)');

                return isLength && isUpper && isLower && isNum && isSpec;
            }

            function checkFormValidity() {
                const isPassValid = validatePassword();
                const isConfValid = confInput.value !== '' && confInput.value === passInput.value;
                
                // Add red border to confirmation if it doesn't match
                if (confInput.value !== '' && !isConfValid) {
                    confInput.classList.add('border-red-400', 'ring-red-400/20');
                } else {
                    confInput.classList.remove('border-red-400', 'ring-red-400/20');
                }
                
                // Add logic for button
                if (isPassValid && isConfValid && form.checkValidity()) {
                    btnSubmit.disabled = false;
                    btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
                    btnSubmit.classList.add('active:scale-[0.98]');
                } else {
                    btnSubmit.disabled = true;
                    btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
                    btnSubmit.classList.remove('active:scale-[0.98]');
                }
            }

            passInput.addEventListener('input', checkFormValidity);
            confInput.addEventListener('input', checkFormValidity);
            
            // Check validity on all inputs
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('input', checkFormValidity);
            });
        });
    </script>
</x-guest-layout>


