<select class="form-select single-select-clear-field" {{ $disabled ? 'disabled' : '' }} name="packed_by"
    data-placeholder="Choose one thing">
    <option></option>
    @foreach ($users as $user)
        <option value="{{ $user->id }}" {{ $selected == $user->id ? 'selected' : '' }}>
            {{ $user->full_name }}
        </option>
    @endforeach
</select>
