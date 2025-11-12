<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title><link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}"> 


<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
<script>
    (function(){
        if (!document.cookie.split('; ').find(r => r.startsWith('appearance='))) {
            document.cookie = 'appearance=light; path=/; max-age=31536000';
        }
        document.documentElement.classList.remove('dark');
    })();
</script>
@fluxAppearance
