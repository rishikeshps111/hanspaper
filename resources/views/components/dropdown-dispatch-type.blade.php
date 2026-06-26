@php
    $selected = $selected ?? null;
@endphp

<select class="form-select single-select-clear-field" name="{{ $dropdownName }}" data-placeholder="Choose one thing">
    <option value="Dispatch Pending" {{ $selected == 'Dispatch Pending' ? 'selected' : '' }}>Dispatch Pending</option>
    <option value="Dispatched" {{ $selected == 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
    <option value="Completed" {{ $selected == 'Completed' ? 'selected' : '' }}>Completed</option>
</select>
