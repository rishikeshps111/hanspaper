@extends('layouts.app')
@section('title', 'Core Stock')
@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="page-wrapper"><div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h5 class="mb-0">Core Stock</h5><small class="text-muted">Manage core sizes and available quantities</small></div>
        <button class="btn btn-primary" id="addCore"><i class="bx bx-plus"></i> Add Core</button>
    </div>
    <div class="card"><div class="card-body"><div class="table-responsive">
        <table class="table table-bordered align-middle" id="coresTable"><thead class="table-light"><tr>
            <th>Code</th><th>Size (mm)</th><th>Total Quantity</th><th>Reserved</th><th>Available</th><th>Status</th><th>Action</th>
        </tr></thead><tbody></tbody></table>
    </div></div></div>
</div></div>

<div class="modal fade" id="coreModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" id="coreForm">
    @csrf <input type="hidden" id="coreId">
    <div class="modal-header"><h5 class="modal-title">Add Core</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Size (mm) <span class="text-danger">*</span></label><input type="number" step="0.01" min="0.01" class="form-control" name="size_mm" required><div class="text-danger small field-error" data-field="size_mm"></div></div>
        <div class="col-md-6 opening-field"><label class="form-label">Opening Quantity <span class="text-danger">*</span></label><input type="number" min="0" class="form-control" name="quantity" value="0"><div class="text-danger small field-error" data-field="quantity"></div></div>
        <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="is_active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
    </div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" type="submit">Save</button></div>
</form></div></div>

<div class="modal fade" id="adjustCoreModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" id="adjustCoreForm">
    @csrf <input type="hidden" id="adjustCoreId">
    <div class="modal-header"><h5 class="modal-title">Adjust Core Quantity</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Adjustment</label><select name="adjustment_type" class="form-select" required><option value="add">Add</option><option value="remove">Remove</option></select></div>
        <div class="col-md-6"><label class="form-label">Quantity</label><input type="number" name="quantity" min="1" class="form-control" required></div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" required></textarea></div>
    </div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><button class="btn btn-primary">Update Quantity</button></div>
</form></div></div>

<div class="modal fade" id="coreHistoryModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Core Stock Transaction History</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="table-responsive"><table class="table table-bordered align-middle w-100" id="coreHistoryTable"><thead class="table-light"><tr>
        <th>Date</th><th>Transaction</th><th>Quantity Change</th><th>Quantity Before</th><th>Quantity After</th><th>Reference</th><th>Remarks</th>
    </tr></thead><tbody></tbody></table></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
</div></div></div>
@endsection
@section('js')
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(function () {
    const urls = {data:@json(route('cores.data', [], false)), store:@json(route('cores.store', [], false)), update:@json(route('cores.update', ['core'=>'__ID__'], false)), adjust:@json(route('cores.adjust', ['core'=>'__ID__'], false)), history:@json(route('cores.history', ['core'=>'__ID__'], false))};
    const toast = message => window.iziToast ? iziToast.success({title:'Success',message}) : Swal.fire('Success',message,'success');
    const table = $('#coresTable').DataTable({processing:true,serverSide:true,ajax:urls.data,pageLength:10,lengthMenu:[10,25,50,100],order:[],columns:[
        {data:'code',name:'code'}, {data:'size_mm',name:'size_mm'}, {data:'quantity',name:'quantity'},
        {data:'reserved_quantity',name:'reserved_quantity',searchable:false,orderable:false,defaultContent:0},
        {data:'available_quantity',name:'available_quantity',searchable:false,orderable:false,render:d=>`<span class="badge bg-success">${d}</span>`},
        {data:'is_active',name:'is_active',render:d=>`<span class="badge ${d?'bg-success':'bg-secondary'}">${d?'Active':'Inactive'}</span>`},
        {data:'action',name:'action',searchable:false,orderable:false}
    ]});
    $('#addCore').click(function(){ $('#coreForm')[0].reset(); $('#coreId').val(''); $('.opening-field').show(); $('#coreModal .modal-title').text('Add Core'); $('#coreModal').modal('show'); });
    $(document).on('click','.edit-core',function(){const b=$(this); $('#coreForm')[0].reset(); $('#coreId').val(b.data('id')); $('#coreForm [name=size_mm]').val(b.data('size')); $('#coreForm [name=is_active]').val(b.data('active')); $('.opening-field').hide(); $('#coreModal .modal-title').text('Edit ' + b.data('code')); $('#coreModal').modal('show');});
    $(document).on('click','.adjust-core',function(){ $('#adjustCoreForm')[0].reset(); $('#adjustCoreId').val($(this).data('id')); $('#adjustCoreModal').modal('show'); });
    $(document).on('click','.core-history',function(){
        const button=$(this), historyTable=$('#coreHistoryTable');
        $('#coreHistoryModal .modal-title').text(button.data('code') + ' - Stock Transaction History');
        if ($.fn.DataTable.isDataTable(historyTable)) historyTable.DataTable().destroy();
        historyTable.DataTable({processing:true,serverSide:true,ajax:urls.history.replace('__ID__',button.data('id')),pageLength:10,lengthMenu:[10,25,50,100],order:[],columns:[
            {data:'created_at',name:'created_at'}, {data:'transaction_type',name:'transaction_type'},
            {data:'quantity_change',name:'quantity_change',render:function(data){const value=parseInt(data)||0;return `<span class="badge ${value>=0?'bg-success':'bg-danger'}">${data}</span>`;}},
            {data:'quantity_before',name:'quantity_before'}, {data:'quantity_after',name:'quantity_after'},
            {data:'reference',name:'reference',searchable:false,orderable:false}, {data:'remarks',name:'remarks',defaultContent:'—'}
        ]});
        $('#coreHistoryModal').modal('show');
    });
    $('#coreForm').submit(function(e){e.preventDefault(); const id=$('#coreId').val(), form=$(this), data=form.serialize()+(id?'&_method=PUT':''); $('.field-error').text(''); $.post(id?urls.update.replace('__ID__',id):urls.store,data).done(r=>{ $('#coreModal').modal('hide'); toast(r.message); table.ajax.reload(null,false); }).fail(x=>{Object.entries(x.responseJSON?.errors||{}).forEach(([k,v])=>$(`.field-error[data-field="${k}"]`).text(v[0]));});});
    $('#adjustCoreForm').submit(function(e){e.preventDefault(); const id=$('#adjustCoreId').val(); $.post(urls.adjust.replace('__ID__',id),$(this).serialize()).done(r=>{$('#adjustCoreModal').modal('hide');toast(r.message);table.ajax.reload(null,false);}).fail(x=>Swal.fire('Error',x.responseJSON?.message||'Unable to adjust quantity.','error'));});
});
</script>
@endsection
