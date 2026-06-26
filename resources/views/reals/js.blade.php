<script>

    $(document).ready(function () {
            var table = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('reals.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'real_id', name: 'real_id' },
                {
                    data: 'real_no',
                    name: 'real_no',
                    render: function (data, type, full, meta) {
                        let url = '/reals/details/' + full.id;
                        return `<a href="${url}" class="text-decoration-none">${data}</a>`;
                    }
                },
                { data: 'brand_relation.name', name: 'brands.name' },
                { data: 'category_relation.name', name: 'item_categories.name' },
                { data: 'gsm', name: 'gsm' },
                { data: 'subcode', name: 'subcode' },
                { data: 'width', name: 'width' },
                { data: 'length', name: 'length' },
                { data: 'weight', name: 'weight' },
                { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[1, 'desc']]
        });


  var table = $('#datatableone').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('reals.finished') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'real_id', name: 'real_id' },
                {
                    data: 'real_no',
                    name: 'real_no',
                    render: function (data, type, full, meta) {
                        let url = '/reals/details/' + full.id;
                        return `<a href="${url}" class="text-decoration-none">${data}</a>`;
                    }
                },
                { data: 'brand_relation.name', name: 'brands.name' },
                { data: 'category_relation.name', name: 'item_categories.name' },
                { data: 'gsm', name: 'gsm' },
                { data: 'subcode', name: 'subcode' },
                { data: 'width', name: 'width' },
                { data: 'length', name: 'length' },
                { data: 'weight', name: 'weight' },
                { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[1, 'desc']]
        });



           var table = $('#datatabletwo').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('reals.alllist') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'real_id', name: 'real_id' },
                {
                    data: 'real_no',
                    name: 'real_no',
                    render: function (data, type, full, meta) {
                        let url = '/reals/details/' + full.id;
                        return `<a href="${url}" class="text-decoration-none">${data}</a>`;
                    }
                },
                { data: 'brand_relation.name', name: 'brands.name' },
                { data: 'category_relation.name', name: 'item_categories.name' },
                { data: 'gsm', name: 'gsm' },
                { data: 'subcode', name: 'subcode' },
                { data: 'width', name: 'width' },
                { data: 'length', name: 'length' },
                { data: 'weight', name: 'weight' },
                { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[3, 'asc'],[4, 'asc'],[5, 'asc'],[6,'asc']]
        });


      
        $(document).on('click', '.delete-btn', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        success: function (response) {
                            Swal.fire(
                                'Deleted!',
                                response.message || 'The real has been deleted.',
                                'success'
                            );
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            Swal.fire(
                                'Error!',
                                xhr.responseJSON?.message || 'Something went wrong.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
        
        $(document).on('click', '.manage-stock-btn', function () {
            let realId = $(this).data('id');
            $.get("{{ url('reals/stock') }}/" + realId, function (res) {
                if (res.success) {
                    $('#manageStockModal .modal-body').html(res.html);
                    $('#modal_real_no').text(res.real_no);
                    $('#manageStockModal').modal('show');
                }
            });

        });

        $(document).on('submit', '#addStockForm', function (e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('reals.stock.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    if (res.success) {
                        Swal.fire('Success', 'Stock added successfully', 'success');
                        $('#manageStockModal').modal('hide');
                    }
                },
                error: function (err) {
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });

    });


</script>

@if(session('success'))
    <script>
        swal.fire(
            'Success!',
            '{{ session('success') }}',
            'success'
        );
    </script>
@endif

@if(session('error'))
    <script>
        swal.fire(
            'Error!',
            '{{ session('error') }}',
            'error'
        );
    </script>
@endif

@if(session('warning'))
    <script>
        swal.fire(
            'Warning!',
            '{{ session('warning') }}',
            'warning'
        );
    </script>
@endif