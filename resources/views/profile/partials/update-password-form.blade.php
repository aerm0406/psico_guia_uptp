<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('Actualizar contraseña') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Asegúrate de usar una contraseña segura y difícil de adivinar.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6" x-data="{
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
    }">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Contraseña actual')" class="dark:text-gray-300" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full dark:bg-gray-700 dark:text-gray-200" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Nueva contraseña')" class="dark:text-gray-300" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full dark:bg-gray-700 dark:text-gray-200" autocomplete="new-password" x-model="password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirmar contraseña')" class="dark:text-gray-300" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full dark:bg-gray-700 dark:text-gray-200" autocomplete="new-password" x-model="password_confirmation" />
            <p x-show="password_confirmation.length > 0 && !passwordsMatch" style="display: none;" class="text-sm text-red-500 mt-2">
                La confirmación de la contraseña no coincide.
            </p>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Validaciones visuales en tiempo real --}}
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium bg-white p-3 rounded-xl border border-slate-100 shadow-sm text-left dark:bg-gray-800 dark:border-gray-700">
            <div :class="passwordStrength.length ? 'text-green-600 dark:text-green-400' : 'text-slate-400 dark:text-gray-400'" class="flex items-center gap-2 transition-colors">
                <svg x-show="passwordStrength.length" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <svg x-show="!passwordStrength.length" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                8 - 16 caracteres
            </div>
            <div :class="passwordStrength.upper ? 'text-green-600 dark:text-green-400' : 'text-slate-400 dark:text-gray-400'" class="flex items-center gap-2 transition-colors">
                <svg x-show="passwordStrength.upper" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <svg x-show="!passwordStrength.upper" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Una letra mayúscula
            </div>
            <div :class="passwordStrength.lower ? 'text-green-600 dark:text-green-400' : 'text-slate-400 dark:text-gray-400'" class="flex items-center gap-2 transition-colors">
                <svg x-show="passwordStrength.lower" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <svg x-show="!passwordStrength.lower" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Una letra minúscula
            </div>
            <div :class="passwordStrength.number ? 'text-green-600 dark:text-green-400' : 'text-slate-400 dark:text-gray-400'" class="flex items-center gap-2 transition-colors">
                <svg x-show="passwordStrength.number" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <svg x-show="!passwordStrength.number" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Un número
            </div>
            <div :class="passwordStrength.special ? 'text-green-600 dark:text-green-400' : 'text-slate-400 dark:text-gray-400'" class="flex items-center gap-2 sm:col-span-2 transition-colors">
                <svg x-show="passwordStrength.special" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <svg x-show="!passwordStrength.special" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Un carácter especial (@$!%*?&)
            </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-gray-700 mt-6">
            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ __('Guardado.') }}
                </p>
            @endif

            <button type="submit" class="px-10 py-2.5 bg-[#050b1d] dark:bg-gray-700 text-white text-sm font-bold rounded-xl hover:bg-slate-800 dark:hover:bg-gray-600 transition-all shadow-lg active:scale-95">
                {{ __('Guardar') }}
            </button>
        </div>
    </form>
</section>
