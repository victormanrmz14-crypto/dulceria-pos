@props(['name'])
{{-- Lucide outlines, ISC license, matching the existing @lucide/vue library. --}}
<svg {{ $attributes }} xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('arrow-left') <path d="m12 19-7-7 7-7M5 12h14"/> @break
        @case('eye') <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/> @break
        @case('eye-off') <path d="m2 2 20 20M9.88 9.88a3 3 0 0 0 4.24 4.24M10.73 5.08A10.94 10.94 0 0 1 12 5c5 0 9 7 9 7a17.2 17.2 0 0 1-2.24 3.05M6.61 6.61A21.81 21.81 0 0 0 3 12s4 7 9 7a9.74 9.74 0 0 0 5.39-1.61"/> @break
        @case('store') <path d="m3 9 2-6h14l2 6M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0M5 12v9h14v-9M9 21v-7h6v7"/> @break
        @case('box') <path d="m21 8-9-5-9 5 9 5 9-5ZM3 8v10l9 5 9-5V8M12 13v10M7.5 5.5l9 5"/> @break
        @case('wallet') <path d="M20 8V5a2 2 0 0 0-2-2H6a3 3 0 0 0 0 6h14v12H6a3 3 0 0 1-3-3V6M20 13h-4v4h4"/> @break
        @case('mail') <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/> @break
        @case('lock') <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/> @break
    @endswitch
</svg>
