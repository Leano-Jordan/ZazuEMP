@props([
    'options' => [],
    'selected' => null,
    'name' => 'primary_niche',
    'required' => false,
])

<div class="zazu-niche-grid">
    @foreach($options as $key => $option)
        <label class="zazu-niche-card {{ old($name, $selected) === $key ? 'is-selected' : '' }}">
            <span class="zazu-niche-card-main">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $key }}"
                    @checked(old($name, $selected) === $key)
                    @if($required) required @endif
                >
                <span class="zazu-niche-card-copy">
                    <strong>{{ $option['label'] }}</strong>
                    <small>{{ $option['description'] }}</small>
                </span>
            </span>
        </label>
    @endforeach
</div>
