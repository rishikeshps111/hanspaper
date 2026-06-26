<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">{{ $item->name }}</h5>
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
                <p><strong>Item Code:</strong> {{ $item->item_code ?? 'N/A' }}</p>
                <p><strong>Category:</strong> {{ $item->category->name ?? 'N/A' }}</p>
                <p><strong>Brand:</strong> {{ $item->brand->name ?? 'N/A' }}</p>
                <p><strong>Created By:</strong> {{ $item->user->first_name ?? 'N/A' }}
                    {{ $item->user->last_name ?? '' }}
                </p>
                <p><strong>Created Date:</strong> {{ $item->created_at ? $item->created_at->format('d M Y') : 'N/A' }}
                </p>
                <p><strong>Description:</strong> {{ $item->description ?? 'N/A' }}</p>
            </div>

            <div class="tab-pane fade" id="transactions" role="tabpanel">

                <!-- Dropdown -->
                <div class="mb-3 d-flex justify-content-between align-items-start">
                    <select class="form-select w-auto modern-select transactionType">
                        <option value="stock" selected>Stock</option>
                        <option value="production">Production</option>
                        <option value="dispatch">Dispatch</option>
                    </select>
                </div>

                <!-- Stock Table -->
                <div class="transaction-table table-stock">
                    @if ($item->itemTransaction->count())
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Quantity</th>
                                    <th>Available Quantity</th>
                                    <th>Committed Stock</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item->itemTransaction as $index => $transaction)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $transaction->quantity }}</td>
                                        <td>{{ $transaction->avaquantity }}</td>
                                        <td>{{ $item->committed_stock }}</td>
                                        <td>{{ $transaction->created_at->format('d M Y') }}</td>
                                        <td>{{ $transaction->updated_at->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No stock details found.</p>
                    @endif
                </div>

                <!-- Production Table -->
                <div class="transaction-table table-production d-none">
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
                                <th>Customer</th>
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
                                <th>Customer</th>
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