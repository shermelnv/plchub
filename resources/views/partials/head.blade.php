<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0,viewport-fit=cover" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="{{ asset('images/logo.png') }}" sizes="any">
<link rel="icon" href="{{ asset('images/logo.png') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<script>
    document.addEventListener('DOMContentLoaded', () => {
       window.Echo.channel('dashboard.stats')
    .listen('.stats.updated', (e) => {
        window.dispatchEvent(new CustomEvent('stats-updated', {
            detail: e.stats
        }));
    });
 Echo.channel('dashboard.activity')
            .listen('.activity.created', (e) => {
                Livewire.dispatch('activity-created', { message: e.message })
            });
window.isAuthenticated = @json(auth()->check());
    if (window.isAuthenticated) {
         const userId = @js(auth()->id());
    window.Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            console.log('Realtime notification:', notification);

            // Dispatch to Livewire globally
            Livewire.dispatch('notificationReceived', { notification });
        });
    }



    });
</script>

@fluxAppearance