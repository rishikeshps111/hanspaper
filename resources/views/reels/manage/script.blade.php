<script>
$(function () {
    const clean = value => String(value || '').replace(/[^A-Z0-9]/gi, '').toUpperCase();
    const numberPart = value => {
        const number = Number.parseFloat(value);
        return Number.isFinite(number) ? String(Number(number.toFixed(2))) : '';
    };
    const selectedShortName = selector => {
        const option = document.querySelector(selector)?.selectedOptions?.[0];
        return option?.value ? clean(option.dataset.shortName) : '';
    };
    const updateReelCode = () => {
        const weight = $('#reel_type_id option:selected').data('volume') === 'weight';
        $('#reelWidthUnit').text(weight ? @json(isset($reel) && $reel->width_unit === null && $reel->isWeightBased() ? 'mm' : 'cm') : 'mm');
        $('#length').prop('required', !weight).prop('disabled', weight).closest('.col-md-6').toggle(!weight);
        $('#weight_kg').prop('required', weight).prop('disabled', !weight);
        $('#weightField').toggle(weight);
        const parts = [
            selectedShortName('#reel_brand_id'),
            selectedShortName('#reel_type_id'),
            selectedShortName('#reel_gsm_id') + 'GSM',
            numberPart($('#width').val()),
            numberPart($(weight ? '#weight_kg' : '#length').val()),
        ];
        $('#code').val(parts.every(Boolean) ? parts.join('-') : '');
    };

    $('#reel_brand_id, #reel_type_id, #reel_gsm_id').on('change', updateReelCode);
    $('#length, #width, #weight_kg').on('input', updateReelCode);
    updateReelCode();
});
</script>
