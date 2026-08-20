<script>
    $(function () {
        const stockModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addStockModal'));
        const quickReelModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('quickReelModal'));
        const stockReel = $('#stockReelId').select2({
            theme: 'bootstrap-5', width: '100%', allowClear: true, dropdownParent: $('#addStockModal'), placeholder: 'Search Reel',
            ajax: {
                url: @json(route('reels.manage.search', [], false)), dataType: 'json', delay: 300, cache: true,
                data: params => ({ q: params.term || '', page: params.page || 1 }), processResults: response => response
            }
        });
        $('#stockProviderId,#stockWarehouseId').select2({ theme: 'bootstrap-5', width: '100%', allowClear: true, dropdownParent: $('#addStockModal') });
        $('#quickReelBrand,#quickReelType,#quickReelGsm').select2({ theme: 'bootstrap-5', width: '100%', allowClear: true, dropdownParent: $('#quickReelModal') });

        window.openReelStockModal = function (reelId = '', reelCode = '') {
            $('#stockErrors').addClass('d-none').empty();
            if (reelId) {
                stockReel.append(new Option(reelCode || reelId, reelId, true, true)).trigger('change');
            } else stockReel.val(null).trigger('change');
            stockModal.show();
        };
        $(document).on('click', '.add-reel-stock', function () { window.openReelStockModal(this.dataset.reelId || '', this.dataset.reelCode || ''); });
        $('#openQuickReelModal').on('click', function () {
            $('#quickReelErrors').addClass('d-none').empty();
            $('#addStockModal').one('hidden.bs.modal', () => quickReelModal.show());
            stockModal.hide();
        });
        $('#quickReelModal').on('hidden.bs.modal', function () { if (!$('#addStockModal').hasClass('show')) stockModal.show(); });

        $('#quickReelForm').on('submit', function (event) {
            event.preventDefault(); const form = this;
            $.ajax({
                url: @json(route('reels.manage.store', [], false)), type: 'POST', data: $(form).serialize(), headers: { Accept: 'application/json' },
                success: response => {
                    const reel = response.reel;
                    stockReel.append(new Option(reel.text, reel.id, true, true)).trigger('change');
                    form.reset(); $('#quickReelBrand,#quickReelType,#quickReelGsm').val(null).trigger('change');
                    quickReelModal.hide();
                    iziToast.success({ title: 'Success', message: 'Reel added successfully.' });
                    $(document).trigger('reel:created', [reel]);
                },
                error: xhr => { const errors = xhr.responseJSON?.errors; const messages = errors ? Object.values(errors).flat() : [xhr.responseJSON?.message || 'Unable to add reel.']; $('#quickReelErrors').html(messages.map(message => `<div>${$('<div>').text(message).html()}</div>`).join('')).removeClass('d-none'); }
            });
        });

        $('#addStockForm').on('submit', function (event) {
            event.preventDefault(); const form = this;
            $.ajax({
                url: @json(route('reels.stock.bulk-store', [], false)), type: 'POST', data: $(form).serialize(),
                success: response => {
                    stockModal.hide(); form.reset();
                    $('#stockReelId,#stockProviderId,#stockWarehouseId').val(null).trigger('change');
                    $(document).trigger('reel:stock-added');
                    iziToast.success({ title: 'Success', message: 'Reel stock added successfully.' });
                },
                error: xhr => { const errors = xhr.responseJSON?.errors; const messages = errors ? Object.values(errors).flat() : [xhr.responseJSON?.message || 'Unable to add stock.']; $('#stockErrors').html(messages.map(message => `<div>${$('<div>').text(message).html()}</div>`).join('')).removeClass('d-none'); }
            });
        });
    });
</script>