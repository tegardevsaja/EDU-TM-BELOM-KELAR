    <x-layouts.app.sidebar :title="$title ?? null">
        <flux:main class="{{ request()->routeIs('settings*') ? 'pt-0' : '' }}">
            <div class="flex min-h-screen flex-col">
                <div class="flex-1">
                    @unless (request()->routeIs('settings*'))
                        <x-top-controls />
                        
                        {{-- Global Notifications (Session-based) --}}
                        <div id="toast-wrapper" class="fixed top-4 right-4 z-50 space-y-2">
                            @foreach (['success' => 'bg-green-600', 'error' => 'bg-red-600', 'warning' => 'bg-amber-600', 'info' => 'bg-blue-600'] as $type => $color)
                                @if (session()->has($type))
                                    <div class="toast-item pointer-events-auto {{ $color }} text-white shadow-lg rounded-md px-4 py-3 min-w-[260px] max-w-[360px] flex items-start gap-3"
                                         role="alert" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition>
                                        <div class="shrink-0 mt-0.5">
                                            @if($type === 'success')
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            @elseif($type === 'error')
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.29 3.86l-7 12A1 1 0 004.17 18h13.66a1 1 0 00.88-1.5l-7-12a1 1 0 00-1.72 0z"/></svg>
                                            @elseif($type === 'warning')
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.93 19h14.14A2 2 0 0021 17.27L13.93 4.73a2 2 0 00-3.46 0L3 17.27A2 2 0 004.93 19z"/></svg>
                                            @else
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 18a9 9 0 110-18 9 9 0 010 18z"/></svg>
                                            @endif
                                        </div>
                                        <div class="text-sm leading-5">
                                            <div class="font-semibold capitalize">{{ $type }}</div>
                                            <div class="opacity-95">{{ session($type) }}</div>
                                        </div>
                                        <button type="button" class="ml-auto/ text-white/ hover:opacity-90"
                                                onclick="this.closest('.toast-item').style.display='none'">×</button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <script>
                            // Fallback auto-dismiss for browsers without Alpine
                            (function () {
                                setTimeout(function(){
                                    document.querySelectorAll('#toast-wrapper .toast-item').forEach(function(el){
                                        el.style.opacity = '0';
                                        el.style.transition = 'opacity .3s ease';
                                        setTimeout(function(){ el.style.display = 'none'; }, 300);
                                    });
                                }, 4000);
                            })();
                        </script>
                    @endunless
                    {{ $slot }}
                </div>
                <x-app-footer />
            </div>

            {{-- Global SweetAlert2 Delete Confirmation (event delegation) --}}
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-confirm-delete]');
                    if (!btn) return;

                    e.preventDefault();
                    const form = btn.closest('form');
                    if (!form) return;

                    const name  = btn.getAttribute('data-name')  || form.getAttribute('data-name') || 'data ini';
                    const title = btn.getAttribute('data-title') || 'Hapus Data?';
                    const html  = btn.getAttribute('data-text')  || `Data <strong>${name}</strong> akan dihapus secara permanen.`;

                    Swal.fire({
                        title: title,
                        html: html,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: btn.getAttribute('data-confirm-label') || 'Ya, hapus',
                        cancelButtonText: btn.getAttribute('data-cancel-label') || 'Batal',
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            </script>

        </flux:main>
    </x-layouts.app.sidebar>
