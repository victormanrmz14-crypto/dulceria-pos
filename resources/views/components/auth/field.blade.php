@props(['name', 'label', 'type' => 'text', 'value' => '', 'optional' => false, 'hint' => null, 'autocomplete' => null])
<div class="auth-field">
    <label for="{{ $name }}">{{ $label }} @if($optional)<span class="auth-optional">Opcional</span>@endif</label>
    <div class="auth-input-wrap">
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            @if($type !== 'password') value="{{ $value }}" @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if($hint || $errors->has($name)) aria-describedby="{{ trim(($hint ? $name.'-hint ' : '').($errors->has($name) ? $name.'-error' : '')) }}" @endif
            @if($errors->has($name)) aria-invalid="true" @endif
            {{ $attributes }}>
        @if($type === 'password')
            <button class="auth-reveal" type="button" data-password-toggle="{{ $name }}" aria-label="Mostrar {{ mb_strtolower($label) }}" aria-controls="{{ $name }}" aria-pressed="false" hidden>
                <span data-eye><x-auth.icon name="eye" /></span>
                <span data-eye-off hidden><x-auth.icon name="eye-off" /></span>
            </button>
        @endif
    </div>
    @if($hint)<p id="{{ $name }}-hint" class="auth-hint">{{ $hint }}</p>@endif
    @error($name)<p id="{{ $name }}-error" class="auth-field-error"><span>Error:</span> {{ $message }}</p>@enderror
</div>
