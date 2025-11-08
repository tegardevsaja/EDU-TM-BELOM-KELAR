@php
    use Illuminate\Support\Facades\Route;
@endphp

<div class="flex w-full justify-center items-center max-h-screen px-2 py-2">
    <div class="flex flex-col gap-6 w-full max-w-md">
        <div class="text-center">
            <h2 class="text-2xl md:text-3xl font-semibold text-zinc-900 dark:text-white">Log in</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Silakan masukan email dan password untuk login.</p>
        </div>
        
        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />
        
        <form method="POST" wire:submit="login" class="flex flex-col gap-5 w-full">
            <!-- Email Address -->
            <div class="w-full">
                <flux:input
                    wire:model="email"
                    :label="__('Email address')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="smktm@gmail.com"
                    class="w-full"
                />
            </div>
            
            <!-- Password -->
            <div class="w-full space-y-2">
                <flux:input
                    wire:model="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                    class="w-full"
                />
                
                <!-- Remember Me & Forgot Password -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                    <flux:checkbox 
                        wire:model="remember" 
                        :label="__('Remember me')" 
                        class="text-sm"
                    />
                    @if (Route::has('password.request'))
                        <flux:link 
                            class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline transition-colors" 
                            :href="route('password.request')" 
                            wire:navigate
                        >
                            {{ __('Forgot your password?') }}
                        </flux:link>
                    @endif
                </div>
            </div>
    
            <!-- Login Button -->
            <div class="flex items-center justify-end mt-2">
                <flux:button 
                    variant="primary" 
                    type="submit" 
                    class="w-full py-2.5 md:py-3 rounded-lg font-medium transition-all cursor-pointer bg-blue-600" 
                    data-test="login-button"
                >
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>