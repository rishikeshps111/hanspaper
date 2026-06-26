<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">{{ $record->first_name }} {{ $record->last_name }}</h5>
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
                <p><strong>Email :</strong> {{ $record->email ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $record->phone ?? 'N/A' }}</p>
                <p><strong>Whats App:</strong> {{ $record->whatsapp ?? 'N/A' }}</p>
                <p><strong>Billing Address:</strong> {{ $record->billing_address ?? 'N/A' }}
                </p>
                <p><strong>Shipping Address:</strong> {{ $record->shipping_address ?? 'N/A' }}
                </p>
                <p><strong>GST Number:</strong> {{ $record->tax_number ?? 'N/A' }}
                </p>
                <p><strong>Created By:</strong> {{ $record->user->username ?? 'N/A' }}</p>
                <p><strong>Created At:</strong> {{ $record->created_at ? $record->created_at->format('d M Y') : 'N/A' }}
                </p>
                <p><strong>Status:</strong>
                    @if($record->status == 1)
                        <span class="badge rounded-pill text-success bg-light-success px-3">Active</span>
                    @else
                        <span class="badge rounded-pill text-danger bg-light-danger px-3">Inactive</span>
                    @endif
                </p>
            </div>

            <div class="tab-pane fade" id="transactions" role="tabpanel">

                <!-- Dropdown -->
                <div class="mb-3 d-flex justify-content-between align-items-start">
                    <select class="form-select w-auto modern-select transactionType">
                        <option value="production">Production</option>
                        <option value="dispatch">Dispatch</option>
                    </select>
                </div>

                <!-- Production Table -->
                <div class="transaction-table table-production">
                    <div class="row mb-1">
                        <div class="col-8">
                            <label for="statusFilter" class="form-label mb-1">Filter By Status</label>
                            <select class="form-control form-control-sm" id="statusFilter">
                                <option value="">All</option>
                                <option value="Pending">Pending</option>
                                <option value="Assigning Pending">Assigning Pending</option>
                                <option value="Packing Pending">Packing Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="Partial">Partial</option>
                                <option value="Progress">Progress</option>
                                <option value="Cancelled">Cancelled</option>
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
                                <th>Item</th>
                                <th>Request Qty</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <!-- Dispatch Table -->
                <div class="transaction-table table-dispatch d-none">
                    <div class="row mb-1">
                        <div class="col-8">
                            <label for="statusFilterDispatch" class="form-label mb-1">Filter By Status</label>
                            <select class="form-control form-control-sm" id="statusFilterDispatch">
                                <option value="">All</option>
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="Dispatched">Dispatched</option>
                                <option value="Dispatch Pending">Dispatch Pending</option>
                                <option value="Partial Dispatch">Partial Dispatch</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label for="rowsPerPageDispatch" class="form-label mb-1">No of rows</label>
                            <select id="rowsPerPageDispatch" class="form-select form-select-sm">
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
                                <th>Dispatch</th>
                                <th>Item</th>
                                <th>Total Quantity</th>
                                <th>Quantity From Production</th>
                                <th>Quantity From Stock</th>
                                <th>Status</th>
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