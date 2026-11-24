@props(['loading' => 'Enviando…'])
<button type="submit" class="auth-submit" data-loading-text="{{ $loading }}">
    <svg class="auth-loading" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" hidden><circle cx="12" cy="12" r="9" stroke-dasharray="42 16"/></svg>
    <span data-submit-label>{{ $slot }}</span>
</button>
