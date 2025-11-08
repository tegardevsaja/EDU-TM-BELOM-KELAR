@php
    use Illuminate\Support\Facades\Route;
@endphp

<div class="flex w-full justify-center items-center min-h-92 px-2 py-2">
    <div class="flex flex-col gap-6 w-full max-w-md">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-2xl md:text-3xl font-semibold text-zinc-900 dark:text-white">
                {{ __('Lupa Password') }}
            </h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Masukkan email Anda untuk menerima kode OTP.') }}
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <!-- Form -->
        <form action="{{ route('send.otp.post') }}" method="POST" class="flex flex-col gap-5 w-full">
            @csrf

            <!-- Email -->
            <div class="w-full">
                <flux:input
                    name="email"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    :label="__('Email address')"
                    placeholder="email@example.com"
                    class="w-full"
                />
                @error('email')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Submit Button -->
            <flux:button
                variant="primary"
                type="submit"
                class="w-full py-2.5 md:py-3 rounded-lg font-medium transition-all cursor-pointer bg-blue-600"
            >
                {{ __('Kirim Kode OTP') }}
            </flux:button>

            <!-- Back to Login -->
            <div class="text-center">
                <flux:link
                    :href="route('login')"
                    class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline transition-colors"
                    wire:navigate
                >
                    {{ __('Kembali ke Login') }}
                </flux:link>
            </div>
        </form>
    </div>
</div>
