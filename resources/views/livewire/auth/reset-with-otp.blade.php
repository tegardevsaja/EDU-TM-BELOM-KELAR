<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-8">
    <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-lg overflow-hidden max-w-4xl w-full my-auto">

        {{-- Left Side - Form --}}
        <div class="w-full md:w-1/2 p-8 flex flex-col justify-center">
            <x-auth-header 
                :title="__('Reset Password')" 
                :description="__('Buat password baru untuk akun Anda')" 
            />

            @if (session('success'))
                <div class="rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200 mt-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg bg-red-50 p-4 text-sm text-red-800 border border-red-200 mt-4">
                    {{ session('error') }}
                </div>
            @endif

            <form
                wire:submit.prevent="resetPassword"
                class="space-y-6 mt-6"
                x-data="{
                    password: @entangle('password').defer,
                    confirm: @entangle('password_confirmation').defer,
                    get lengthOk() { return this.password && this.password.length >= 8 },
                    get hasNumber() { return /[0-9]/.test(this.password || '') },
                    get hasSymbol() { return /[^A-Za-z0-9]/.test(this.password || '') },
                    get matchConfirm() { return this.password && this.password === this.confirm },
                    get allValid() { return this.lengthOk && this.hasNumber && this.hasSymbol && this.matchConfirm }
                }"
            >
                {{-- Email (Read Only) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Email') }}
                    </label>
                    <input 
                        type="email" 
                        value="{{ $email }}"
                        disabled
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed"
                    />
                    <p class="mt-1 text-xs text-gray-500">Email tidak dapat diubah</p>
                </div>

                {{-- Password Baru --}}
                <div x-data="{ showPassword: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Password Baru') }}
                    </label>
                    <div class="relative">
                        <input 
                            :type="showPassword ? 'text' : 'password'"
                            x-model="password"
                            wire:model.debounce.500ms="password"
                            class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Minimal 8 karakter"
                            autocomplete="new-password"
                        />
                        <button 
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                        >
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div x-data="{ showConfirm: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Konfirmasi Password Baru') }}
                    </label>
                    <div class="relative">
                        <input 
                            :type="showConfirm ? 'text' : 'password'"
                            x-model="confirm"
                            wire:model.debounce.500ms="password_confirmation"
                            class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Ketik ulang password"
                            autocomplete="new-password"
                        />
                        <button 
                            type="button"
                            @click="showConfirm = !showConfirm"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                        >
                            <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Password Requirements --}}
                <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
                    <p class="text-sm font-medium text-blue-900 mb-2">Persyaratan Password:</p>
                    <ul class="space-y-1 text-sm">
                        <li class="flex items-center gap-2" :class="lengthOk ? 'text-blue-800' : 'text-blue-500'">
                            <svg class="w-4 h-4" :class="lengthOk ? 'text-blue-600' : 'text-blue-400'" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Minimal 8 karakter
                        </li>
                        <li class="flex items-center gap-2" :class="(hasNumber && hasSymbol) ? 'text-blue-800' : 'text-blue-500'">
                            <svg class="w-4 h-4" :class="(hasNumber && hasSymbol) ? 'text-blue-600' : 'text-blue-400'" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Mengandung angka dan simbol (misalnya 1, 2, @, #, !)
                        </li>
                        <li class="flex items-center gap-2" :class="matchConfirm ? 'text-blue-800' : 'text-blue-500'">
                            <svg class="w-4 h-4" :class="matchConfirm ? 'text-blue-600' : 'text-blue-400'" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Password harus sama dengan konfirmasi
                        </li>
                    </ul>
                </div>

                {{-- Submit Button --}}
                <button 
                    type="submit"
                    wire:loading.attr="disabled"
                    :disabled="!allValid"
                    class="w-full py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="resetPassword">
                        {{ __('Reset Password') }}
                    </span>
                    <span wire:loading wire:target="resetPassword" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">
                        Kembali ke Login
                    </a>
                </div>
            </form>
        </div>

        {{-- Right Side - Image --}}
        <div class="hidden md:block w-full md:w-1/2">
            <img 
                src="{{ asset('img/img2.jpg') }}" 
                alt="Reset Password Illustration" 
                class="w-full h-full object-cover"
            >
        </div>
    </div>
</div>