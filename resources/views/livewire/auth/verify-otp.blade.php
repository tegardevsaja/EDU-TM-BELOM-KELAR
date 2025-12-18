<div class="max-h-screen flex items-center justify-center bg-gray-100 px-4 py-8 -mt-24">
    <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-lg overflow-hidden max-w-4xl w-full my-auto">

        <div class="w-full md:w-1/2 p-8 flex flex-col justify-center">
            <div class="w-1/4">
                <img src="{{ asset('logo/EDUTMLogo.png') }}" alt="">
            </div>
            <x-auth-header 
                :title="__('Verifikasi Kode OTP')" 
                :description="__('Masukkan kode OTP rahasia yang baru saja kami kirim ke email kamu')"
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

            <form wire:submit.prevent="verify" class="space-y-6 mt-6" 
                x-data="{
                    countdown: 30,
                    canResend: false,
                    timer: null,
                    init() {
                        this.startCountdown();
                        
                        // Listen untuk event dari Livewire
                        Livewire.on('otp-resent', () => {
                            this.startCountdown();
                        });
                    },
                    startCountdown() {
                        this.canResend = false;
                        this.countdown = 30;
                        
                        if (this.timer) {
                            clearInterval(this.timer);
                        }
                        
                        this.timer = setInterval(() => {
                            this.countdown--;
                            
                            if (this.countdown <= 0) {
                                clearInterval(this.timer);
                                this.canResend = true;
                            }
                        }, 1000);
                    },
                    resendOtp() {
                        if (this.canResend) {
                            @this.call('resendOtp');
                        }
                    }
                }">
                <div>
                    <label class="block text-sm font-medium text-gray-800 mb-2 text-center">
                        {{ __('Masukkan Kode OTP') }}
                    </label>
                    <p class="text-xs text-gray-500 text-center mb-3">
                        Cek kotak masuk email kamu. Demi keamanan, jangan bagikan kode ini ke siapa pun, termasuk pihak yang mengaku dari {{ config('app.name') }}.
                    </p>
                    
                    {{-- OTP Input Component --}}
                    <div
                        x-data="{
                            digits: ['', '', '', '', '', ''],
                            init() {
                                this.$nextTick(() => {
                                    if (this.$refs.input0) {
                                        this.$refs.input0.focus();
                                    }
                                });
                            },
                            handleInput(index, event) {
                                const value = event.target.value.replace(/[^0-9]/g, '');
                                
                                if (value) {
                                    this.digits[index] = value.slice(-1);
                                    event.target.value = this.digits[index];
                                    
                                    if (index < 5 && this.$refs['input' + (index + 1)]) {
                                        this.$refs['input' + (index + 1)].focus();
                                    }
                                } else {
                                    this.digits[index] = '';
                                }
                                
                                this.updateOtp();
                            },
                            handleKeydown(index, event) {
                                if (event.key === 'Backspace' && !event.target.value && index > 0) {
                                    if (this.$refs['input' + (index - 1)]) {
                                        this.$refs['input' + (index - 1)].focus();
                                    }
                                }
                            },
                            handlePaste(event) {
                                event.preventDefault();
                                const paste = (event.clipboardData || window.clipboardData).getData('text');
                                const numbers = paste.replace(/[^0-9]/g, '').split('').slice(0, 6);
                                
                                numbers.forEach((num, idx) => {
                                    this.digits[idx] = num;
                                    if (this.$refs['input' + idx]) {
                                        this.$refs['input' + idx].value = num;
                                    }
                                });
                                
                                const lastFilledIndex = numbers.length - 1;
                                if (lastFilledIndex >= 0 && this.$refs['input' + lastFilledIndex]) {
                                    this.$refs['input' + lastFilledIndex].focus();
                                }
                                
                                this.updateOtp();
                            },
                            updateOtp() {
                                const otpValue = this.digits.join('');
                                @this.set('otp', otpValue);
                            }
                        }"
                        class="flex items-center justify-center gap-2"
                    >
                        @for ($i = 0; $i < 6; $i++)
                            <input
                                x-ref="input{{ $i }}"
                                type="text"
                                inputmode="numeric"
                                maxlength="1"
                                @input="handleInput({{ $i }}, $event)"
                                @keydown="handleKeydown({{ $i }}, $event)"
                                @paste="handlePaste($event)"
                                @focus="$el.select()"
                                class="w-12 h-12 text-center text-lg font-semibold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                                autocomplete="off"
                            />
                        @endfor
                    </div>
                    
                    @error('otp')
                        <p class="mt-2 text-sm text-red-600 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-3">
                    <button 
                        type="submit"
                        class="w-full py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition cursor-pointer"
                    >
                        {{ __('Verifikasi Kode OTP') }}
                    </button>

                    <button 
                        type="button"
                        @click="resendOtp()"
                        :disabled="!canResend"
                        :class="{
                            'opacity-50 cursor-not-allowed': !canResend,
                            'hover:bg-gray-50': canResend
                        }"
                        class="w-full px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition"
                    >
                        <span x-show="!canResend" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('Kirim ulang OTP dalam') }} <span x-text="countdown"></span>{{ __(' detik') }}
                        </span>
                        <span x-show="canResend">
                            {{ __('Kirim Ulang Kode OTP') }}
                        </span>
                    </button>
                </div>

                <div class="text-center space-y-2">
                    <p class="text-sm text-gray-500">
                        Kode OTP berlaku selama <span class="font-medium">10 menit</span> sejak dikirim.
                    </p>
                    <p class="text-xs text-gray-400">
                        Tidak menerima kode? Cek folder spam/promosi. Jika masih belum ada, tunggu
                        <span x-show="!canResend" x-text="countdown"></span><span x-show="!canResend"> detik</span>
                        <span x-show="canResend">dan klik tombol <span class="font-medium">Kirim Ulang Kode OTP</span>.</span>
                    </p>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">
                        Kembali ke Login
                    </a>
                </div>
            </form>
        </div>

        <div class="hidden md:block w-full md:w-1/2 relative">
            <img 
                src="{{ asset('img/tmbendera.png') }}" 
                alt="GEDUNG TM" 
                class="w-full h-full object-cover"
            >

            {{-- Gradient hitam ke transparan --}}
            <div class="absolute bottom-0 left-0 w-full h-1/3 bg-gradient-to-t from-black to-transparent"></div>

            {{-- Copyright --}}
            <div class="absolute bottom-5 left-1/2 transform -translate-x-1/2 text-gray-400 text-sm">
                <p class="text-center">@anrcreative</p>
            </div>

        </div>

    </div>
</div>