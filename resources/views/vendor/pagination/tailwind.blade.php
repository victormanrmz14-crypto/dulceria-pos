@if ($paginator->hasPages())
    <div style="display:flex; align-items:center; justify-content:space-between;">

        <p style="font-size:0.85rem; color:#999;">
            Mostrando {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }}
            de {{ $paginator->total() }} productos
        </p>

        <div style="display:flex; gap:6px;">

            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <span style="padding:6px 14px; border-radius:6px; background:#f0f0f0;
                             color:#ccc; font-size:0.85rem; font-weight:600;">
                    ← Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   style="padding:6px 14px; border-radius:6px; background:#f0f0f0;
                          color:#555; font-size:0.85rem; font-weight:600;
                          text-decoration:none;">
                    ← Anterior
                </a>
            @endif

            {{-- Números de página --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span style="padding:6px 10px; color:#aaa; font-size:0.85rem;">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span style="padding:6px 12px; border-radius:6px;
                                         background:#8B0000; color:white;
                                         font-size:0.85rem; font-weight:600;">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               style="padding:6px 12px; border-radius:6px;
                                      background:#f0f0f0; color:#555;
                                      font-size:0.85rem; font-weight:600;
                                      text-decoration:none;">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   style="padding:6px 14px; border-radius:6px; background:#f0f0f0;
                          color:#555; font-size:0.85rem; font-weight:600;
                          text-decoration:none;">
                    Siguiente →
                </a>
            @else
                <span style="padding:6px 14px; border-radius:6px; background:#f0f0f0;
                             color:#ccc; font-size:0.85rem; font-weight:600;">
                    Siguiente →
                </span>
            @endif

        </div>
    </div>
@endif