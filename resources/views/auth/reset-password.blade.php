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
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">Restablecer Contraseña</h2>
                <p class="text-slate-500 mt-3 font-medium text-sm">Ingresa tu nueva contraseña a continuación.</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
                @csrf
                
                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <!-- Email (Hidden) -->
                <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

                <div x-data="{
                    password: '',
                    password_confirmation: '',
                    get passwordStrength() {
                        return {
                            length: this.password.length >= 8 && this.password.length <= 16,
                            upper: /[A-Z]/.test(this.password),
                            lower: /[a-z]/.test(this.password),
                            number: /[0-9]/.test(this.password),
                            special: /[@$!%*?&]/.test(this.password)
                        }
                    },
                    get passwordsMatch() {
                        return this.password === this.password_confirmation;
                    }
                }" class="space-y-6">

                    <div class="space-y-2">
                        <label for="password" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1 block text-center">Nueva Contraseña</label>
                        <input id="password" type="password" name="password" required x-model="password"
                            class="login-input block w-full px-5 py-4 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none text-center"
                            placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-center" />
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1 block text-center">Confirmar Contraseña</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required x-model="password_confirmation"
                            class="login-input block w-full px-5 py-4 rounded-xl text-sm font-medium placeholder:text-slate-300 focus:outline-none text-center"
                            placeholder="••••••••">
                        <p x-show="password_confirmation.length > 0 && !passwordsMatch" style="display: none;" class="text-sm text-red-500 mt-2 text-center">
                            La confirmación de la contraseña no coincide.
                        </p>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-center" />
                    </div>

                    {{-- Validaciones visuales en tiempo real --}}
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium bg-white p-3 rounded-xl border border-slate-100 shadow-sm text-left">
                        <div :class="passwordStrength.length ? 'text-green-600' : 'text-slate-400'" class="flex items-center gap-2 transition-colors">
                            <svg x-show="passwordStrength.length" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg x-show="!passwordStrength.length" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            8 - 16 caracteres
                        </div>
                        <div :class="passwordStrength.upper ? 'text-green-600' : 'text-slate-400'" class="flex items-center gap-2 transition-colors">
                            <svg x-show="passwordStrength.upper" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg x-show="!passwordStrength.upper" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Una letra mayúscula
                        </div>
                        <div :class="passwordStrength.lower ? 'text-green-600' : 'text-slate-400'" class="flex items-center gap-2 transition-colors">
                            <svg x-show="passwordStrength.lower" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg x-show="!passwordStrength.lower" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Una letra minúscula
                        </div>
                        <div :class="passwordStrength.number ? 'text-green-600' : 'text-slate-400'" class="flex items-center gap-2 transition-colors">
                            <svg x-show="passwordStrength.number" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg x-show="!passwordStrength.number" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Un número
                        </div>
                        <div :class="passwordStrength.special ? 'text-green-600' : 'text-slate-400'" class="flex items-center gap-2 sm:col-span-2 transition-colors">
                            <svg x-show="passwordStrength.special" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg x-show="!passwordStrength.special" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Un carácter especial (@$!%*?&)
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary w-full py-4 px-6 rounded-xl text-white font-black text-sm shadow-lg shadow-sky-900/10 uppercase tracking-widest transition-all active:scale-[0.98]">
                            Restablecer Contraseña
                        </button>
                    </div>
                </div>
            </form>

            <div class="mt-16 pt-8 border-t border-slate-50 text-center">
                <p class="text-slate-300 text-[10px] font-black uppercase tracking-[0.3em]">Psico-Guía UPTP © 2026</p>
            </div>
        </div>
    </div>
</x-guest-layout>
