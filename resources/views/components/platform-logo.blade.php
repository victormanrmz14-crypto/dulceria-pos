@props(['dark' => false])
<a href="{{ url('/') }}" {{ $attributes->class(['platform-logo']) }} aria-label="Dulcería POS, página de inicio" translate="no">
    <img src="{{ asset('brand/logo-'.($dark ? 'dark' : 'horizontal').'.svg') }}" width="264" height="64" alt="Dulcería POS">
</a>
