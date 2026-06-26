<input type="hidden" id="modal_employee_id" value="{{ $employee_id }}">
<div class="table-responsive">
    <table class="table table-striped table-bordered border w-100" id="productionTable">
        <thead>
            <tr>
                <th>SL NO</th>
                <th>Purchase Order</th>
                <th>Item</th>
                <th>Real No</th>
                <th>Quantity</th>
                <th>Date</th>
            </tr>
        </thead>
    </table>
</div>
<script>
    $(function () {

        let employeeId = $('#modal_employee_id').val();
        $('#productionTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,

            lengthMenu: [[100, 200, 500], [100, 200, 500]],
            pageLength: 100,

            ajax: {
                url: "{{ route('report.produced_by.production.list') }}",
                data: function (d) {
                    d.employee_id = employeeId;
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                }
            },

            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'purchase_order' },
                { data: 'item_name' },
                { data: 'real' },
                { data: 'quantity' },
                { data: 'date' }
            ],

            dom: 'l r t i p'
        });


    });
</script>