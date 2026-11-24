@props(['dark' => false, 'compact' => false])
<a href="{{ url('/') }}" {{ $attributes->class(['platform-logo']) }} aria-label="Dulcería POS, página de inicio" translate="no">
    <img src="{{ asset('brand/logo-'.($dark ? 'white' : ($compact ? 'compact' : 'primary')).'.svg') }}" width="{{ $dark ? 301 : 300 }}" height="65" alt="Dulcería POS">
</a>
