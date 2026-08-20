@extends('layouts.app')

@section('title', 'Reel Settings')

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumb :langArray="['Reels', 'Reel Settings']" />

        <div class="card">
            <div class="card-header px-4 py-3">
                <h5 class="mb-0 text-uppercase">Reel Settings</h5>
            </div>
            <div class="card-body">
                @php
                    $tabs = [
                        'brands' => ['title' => 'Reel Brands', 'field' => 'name'],
                        'gsm' => ['title' => 'Reel GSM', 'field' => 'gsm'],
                        'providers' => ['title' => 'Reel Providers', 'field' => 'name'],
                        'types' => ['title' => 'Reel Types', 'field' => 'name'],
                        'warehouses' => ['title' => 'Reel Warehouses', 'field' => 'name'],
                    ];
                @endphp

                <ul class="nav nav-tabs nav-success" id="reelSettingsTabs" role="tablist">
                    @foreach($tabs as $key => $tab)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $key }}-tab"
                                data-bs-toggle="tab" data-bs-target="#{{ $key }}" type="button" role="tab">
                                {{ $tab['title'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content py-3">
                    @foreach($tabs as $key => $tab)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $key }}" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">{{ $tab['title'] }}</h6>
                                <button class="btn btn-primary px-4 open-setting-modal"
                                    data-type="{{ $key }}" data-title="{{ $tab['title'] }}" data-field="{{ $tab['field'] }}">
                                    <i class="bx bx-plus me-1"></i>Add
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle reel-settings-table w-100"
                                    id="{{ $key }}Table" data-type="{{ $key }}" data-field="{{ $tab['field'] }}">
                                    <thead>
                                        <tr>
                                            <th style="width:70px">Sl No.</th>
                                            <th>{{ $tab['field'] === 'gsm' ? 'GSM' : 'Name' }}</th>
                                            @if($tab['field'] !== 'gsm')<th>Short Name</th>@endif
                                            @if($key === 'warehouses')<th>Warehouse Type</th>@endif
                                            <th>Created By</th>
                                            <th>Updated By</th>
                                            <th>Status</th>
                                            <th style="width:120px">Action</th>
                                            <th class="d-none">Created At Sort</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="settingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="settingForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="settingModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="formErrors"></div>
                <input type="hidden" id="settingType">
                <input type="hidden" id="settingId">
                <div class="mb-3">
                    <label class="form-label" id="valueLabel"></label>
                    <input class="form-control" id="settingValue" required>
                </div>
                <div class="mb-3" id="shortNameGroup">
                    <label class="form-label">Short Name</label>
                    <input type="text" class="form-control" id="shortName" maxlength="100">
                </div>
                <div class="mb-3 d-none" id="warehouseTypeGroup">
                    <label class="form-label">Warehouse Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="warehouseType">
                        <option value="factory">Factory</option>
                        <option value="godown">Godown</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select class="form-select" id="isActive" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveSetting">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteSettingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="deleteSettingForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Delete Reel Setting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are you sure you want to delete <strong id="deleteLabel"></strong>?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseUrl = @json(route('reels.settings.index', [], false));
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const settingModal = new bootstrap.Modal(document.getElementById('settingModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteSettingModal'));
    let deleteTarget = {};
    const tables = {};
    const escapeAttribute = value => String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[character]);

    document.querySelectorAll('.reel-settings-table').forEach(table => {
        const type = table.dataset.type;
        const gsm = table.dataset.field === 'gsm';
        const warehouse = type === 'warehouses';
        const sortColumn = gsm ? 6 : (warehouse ? 8 : 7);
        const columns = [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: gsm ? 'gsm' : 'name', name: gsm ? 'gsm' : 'name'}
        ];
        if (!gsm) columns.push({data: 'short_name', name: 'short_name', defaultContent: '—'});
        if (warehouse) columns.push({
            data: 'warehouse_type',
            name: 'warehouse_type',
            render: value => value === 'factory'
                ? '<span class="badge bg-primary">Factory</span>'
                : '<span class="badge bg-info text-dark">Godown</span>'
        });
        columns.push(
            {data: 'created_by_name', orderable: false, searchable: false},
            {data: 'updated_by_name', orderable: false, searchable: false},
            {
                data: 'is_active',
                name: 'is_active',
                render: data => data
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>'
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (_data, _renderType, record) => {
                    const value = gsm ? record.gsm : record.name;
                    const title = document.querySelector(`#${type}-tab`).textContent.trim();
                    return `<button class="btn btn-sm btn-outline-primary open-setting-modal"
                                data-type="${type}" data-title="${escapeAttribute(title)}"
                                data-field="${gsm ? 'gsm' : 'name'}" data-id="${record.id}"
                                data-value="${escapeAttribute(value)}" data-short-name="${escapeAttribute(record.short_name || '')}"
                                data-warehouse-type="${escapeAttribute(record.warehouse_type || 'godown')}"
                                data-active="${record.is_active ? 1 : 0}" title="Edit"><i class="bx bx-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger open-delete-modal"
                                data-type="${type}" data-id="${record.id}" data-label="${escapeAttribute(value)}"
                                title="Delete"><i class="bx bx-trash"></i></button>`;
                }
            },
            {data: 'created_at', name: 'created_at', visible: false, searchable: false}
        );
        tables[type] = $(table).DataTable({
            processing: true,
            serverSide: true,
            ajax: `${baseUrl}/${type}/data`,
            columns: columns,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [[sortColumn, 'desc']],
            createdRow: (row, data) => row.dataset.recordId = data.id
        });
    });

    const activeTab = window.location.hash;
    if (activeTab && document.querySelector(`[data-bs-target="${activeTab}"]`)) {
        bootstrap.Tab.getOrCreateInstance(document.querySelector(`[data-bs-target="${activeTab}"]`)).show();
    }
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', event => {
            history.replaceState(null, '', event.target.dataset.bsTarget);
            const type = event.target.dataset.bsTarget.substring(1);
            tables[type]?.columns.adjust();
        });
    });

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.open-setting-modal');
        if (!button) return;
        const editing = Boolean(button.dataset.id);
        const gsm = button.dataset.field === 'gsm';
        document.getElementById('settingType').value = button.dataset.type;
        document.getElementById('settingId').value = button.dataset.id || '';
        document.getElementById('settingValue').value = button.dataset.value || '';
        document.getElementById('settingValue').type = gsm ? 'number' : 'text';
        document.getElementById('settingValue').min = gsm ? '1' : '';
        document.getElementById('shortName').value = button.dataset.shortName || '';
        document.getElementById('warehouseType').value = button.dataset.warehouseType || 'godown';
        document.getElementById('isActive').value = button.dataset.active ?? '1';
        document.getElementById('shortNameGroup').classList.toggle('d-none', gsm);
        document.getElementById('warehouseTypeGroup').classList.toggle('d-none', button.dataset.type !== 'warehouses');
        document.getElementById('valueLabel').textContent = gsm ? 'GSM' : 'Name';
        document.getElementById('settingModalTitle').textContent = `${editing ? 'Edit' : 'Add'} ${button.dataset.title}`;
        document.getElementById('formErrors').classList.add('d-none');
        settingModal.show();
    });

    document.getElementById('settingForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        const type = document.getElementById('settingType').value;
        const id = document.getElementById('settingId').value;
        const value = document.getElementById('settingValue').value;
        const payload = { is_active: document.getElementById('isActive').value };
        if (type === 'gsm') payload.gsm = value;
        else {
            payload.name = value;
            payload.short_name = document.getElementById('shortName').value;
            if (type === 'warehouses') payload.warehouse_type = document.getElementById('warehouseType').value;
        }
        if (id) payload._method = 'PUT';

        const response = await fetch(`${baseUrl}/${type}${id ? '/' + id : ''}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token},
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (!response.ok) {
            const messages = result.errors ? Object.values(result.errors).flat() : [result.message || 'Unable to save record.'];
            const box = document.getElementById('formErrors');
            box.innerHTML = messages.map(message => `<div>${message}</div>`).join('');
            box.classList.remove('d-none');
            return;
        }
        tables[type].order([type === 'gsm' ? 6 : (type === 'warehouses' ? 8 : 7), 'desc']).ajax.reload(null, Boolean(!id));
        settingModal.hide();
    });

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.open-delete-modal');
        if (!button) return;
        deleteTarget = {type: button.dataset.type, id: button.dataset.id};
        document.getElementById('deleteLabel').textContent = button.dataset.label;
        deleteModal.show();
    });

    document.getElementById('deleteSettingForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        const response = await fetch(`${baseUrl}/${deleteTarget.type}/${deleteTarget.id}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token},
            body: JSON.stringify({_method: 'DELETE'})
        });
        if (response.ok) {
            tables[deleteTarget.type].ajax.reload(null, false);
            deleteModal.hide();
        }
        else {
            const result = await response.json();
            alert(result.message || 'Unable to delete record.');
        }
    });
});
</script>
@endsection
