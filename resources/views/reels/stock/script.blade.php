<script>
$(function () {
    $('#reel_id').on('change', function () {
        const option = this.options[this.selectedIndex];
        if (!option?.value) return;
        const weight = option.dataset.volume === 'weight';
        $('#original_length').prop('required', !weight).prop('disabled', weight).closest('.col-md-6').toggle(!weight);
        if (!$('#original_length').val()) $('#original_length').val(option.dataset.length);
        if (!$('#purchase_price').val()) $('#purchase_price').val(option.dataset.purchase);
        if (!$('#selling_price').val()) $('#selling_price').val(option.dataset.selling);
    }).trigger('change');
});
</script>
