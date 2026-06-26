
<select class="form-select single-select-clear-field" id="{{ $dropdownName }}" name="{{ $dropdownName }}" data-placeholder="Choose machine" required="true">
    <option></option>
    @foreach ($machines as $data)
        <option value="{{ $data->id }}" {{ $selected == $data->id ? 'selected' : '' }}>
            {{ $data->machine_name }}
        </option>
    @endforeach
</select>
