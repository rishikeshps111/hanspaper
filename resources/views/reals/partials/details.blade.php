<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">{{ $record->real_no }} - {{ 'REAL' . str_pad($record->id, 3, '0', STR_PAD_LEFT) }}</h5>
    </div>
    <div class="card-body">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" id="itemTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview"
                    type="button" role="tab">
                    <i class="bi bi-info-circle me-1"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions"
                    type="button" role="tab">
                    <i class="bi bi-list-check me-1"></i>Transactions
                </button>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content mt-3">
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <p><strong>Real ID :</strong> {{ 'REAL' . str_pad($record->id, 3, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Brand:</strong> {{ $record->brandRelation->name ?? 'N/A' }}</p>
                <p><strong>Category:</strong> {{ $record->categoryRelation->name ?? 'N/A' }}</p>
                <p><strong>GSM:</strong> {{ $record->gsm ?? 'N/A' }}
                </p>
                <p><strong>SubCode:</strong> {{ $record->subcode ?? 'N/A' }}
                </p>
                <p><strong>Width:</strong> {{ $record->width ?? 'N/A' }}
                </p>
                <p><strong>Length:</strong> {{ $record->length ?? 'N/A' }}</p>
                <p><strong>Weight:</strong> {{ $record->weight ?? 'N/A' }}
                </p>
                <p><strong>Status:</strong>
                    @if($record->is_active == 1)
                        <span class="badge rounded-pill text-success bg-light-success px-3">Active</span>
                    @else
                        <span class="badge rounded-pill text-danger bg-light-danger px-3">Inactive</span>
                    @endif
                </p>
                @php
                if(isset($record->stocksRelation))
                {
                    @endphp
                 <p><strong>Total Length :</strong> {{ $record->stocksRelation->total_length ?? '0' }} m

                  @if($record->stocksRelation->status=="bit")
                 <p><strong>Available Bit:</strong> {{ $record->stocksRelation->bal_length ?? '0' }} m
                     @endif
                  @if($record->stocksRelation->status=="full")
                <p><strong>Available Stock:</strong> {{ $record->stocksRelation->bal_length ?? '0' }} m
                     @endif
                     @php
                 }
                 @endphp



            </div>

            <div class="tab-pane fade" id="transactions" role="tabpanel">
                <!-- Production Table -->
                <div class="transaction-table table-production">
                    <div class="row mb-1">
                        <div class="col-8">
                            <label for="workOrderFilter" class="form-label mb-1">Filter By Work Order</label>
                            <select class="form-control form-control-sm" id="workOrderFilter">
                                <option value="">All</option>
                                @foreach ($workorder as $record)
                                    <option value="{{$record->purchase_order_id}}">{{$record->purchase_order_id}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label for="rowsPerPage" class="form-label mb-1">No of rows</label>
                            <select id="rowsPerPage" class="form-select form-select-sm">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Work Order</th>
                                <th>Customer</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>