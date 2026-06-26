@php
    $selected = $selected ?? null;
@endphp

<select class="form-select single-select-clear-field" name="{{ $dropdownName }}" data-placeholder="Choose one thing">
    <option value="Company Vehicle" {{ $selected == 'Company Vehicle' ? 'selected' : '' }}>Company Vehicle</option>
    <option value="Direct Customer" {{ $selected == 'Direct Customer' ? 'selected' : '' }}>Direct Customer</option>
    <option value="Courier" {{ $selected == 'Courier' ? 'selected' : '' }}>Courier</option>
</select>
