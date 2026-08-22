@filamentScripts
@livewireScripts

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('toast', {
            show: false,
            type: 'success',
            message: '',
            timeout: null,
            open(type, message) {
                this.type = type;
                this.message = message;
                this.show = true;
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => this.close(), 4000);
            },
            close() {
                this.show = false;
            },
        });
    });
</script>

@if(config('wave.dev_bar'))
    @include('theme::partials.dev_bar')
@endif

@if(setting('site.google_analytics_tracking_id', ''))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('site.google_analytics_tracking_id') }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ setting("site.google_analytics_tracking_id") }}');
</script>
@endif
