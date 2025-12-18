<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>
<link rel="icon" type="image/png" href="{{ asset('logo/favicon.png') }}">
<link rel="icon" type="image/x-icon" href="{{ asset('logo/favicon.png') }}"> 


<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
<script>
    (function(){
        const getCookie = (name) => {
            const v = document.cookie.split('; ').find(r => r.startsWith(name + '='));
            return v ? decodeURIComponent(v.split('=')[1]) : null;
        };

        let appearance = getCookie('appearance');
        if (!appearance) {
            // Keep existing default as light if not set yet
            appearance = 'light';
            document.cookie = 'appearance=' + encodeURIComponent(appearance) + '; path=/; max-age=31536000';
        }

        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const shouldDark = appearance === 'dark' || (appearance === 'system' && prefersDark);
        document.documentElement.classList.toggle('dark', shouldDark);
    })();
</script>
@fluxAppearance
