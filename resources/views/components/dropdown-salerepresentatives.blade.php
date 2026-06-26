@props(['representatives', 'selected', 'dropdownName'])

<select class="form-select single-select-clear-field" id="{{ $dropdownName }}" name="{{ $dropdownName }}"
    data-placeholder="{{ __('Select Sales Representative') }}" {{ $required ? 'required' : '' }}>
    <option value=""></option>
    @foreach ($representatives as $rep)
        <option value="{{ $rep->id }}" {{ (string) $selected === (string) $rep->id ? 'selected' : '' }}>
            {{ $rep->full_name }}
            @if ($rep->mobile)
                - {{ $rep->mobile }}
            @endif
        </option>
    @endforeach
</select>
