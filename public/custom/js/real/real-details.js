$(function () {
    "use strict";

    const tableId = $('#realListTable');

    function loadDatatables() {
        // Destroy previous instance
        tableId.DataTable().destroy();

        var table = tableId.DataTable({
            processing: true,
            serverSide: true,
            method: 'get',
            ajax: {
                url: baseURL + '/reals'
            },
            columns: [
                { targets: 0, data: 'id', orderable: true, visible: false },
                {
                    data: 'id',
                    orderable: false,
                    render: function (data, type, full, meta) {
                        let code = full.real_id || '';
                        let status = full.is_active;

                        return `
                        <div class="item-select" data-id="${data}" style="cursor:pointer;">
                            <input type="checkbox" class="form-check-input row-select me-2" 
                                name="record_ids[]" value="${data}">
                            <span class="fw-bold">${full.real_no}</span>
                            <br>
                            <span class="badge bg-light-info text-primary mt-1 ms-4">${code}</span>
                            ${status}
                        </div>
                        `;
                    }
                }
            ],
            select: {
                style: 'os',
                selector: 'td:first-child'
            },
            order: [[0, 'desc']],
            dom: '<"top"f>rt<"bottom"p>',
            pagingType: 'simple',
            pageLength: 10,
            drawCallback: function () {
                setTooltip();
            }
        });

        $('.dataTables_filter').addClass('text-start').removeClass('text-end');
        $('.dataTables_filter input').attr('placeholder', 'Search Reals...');
        $('.dataTables_filter label').contents().filter(function () {
            return this.nodeType === 3; // text node
        }).remove();

        // ✅ Helper: select row
        function selectRowById(rowId) {
            table.rows().every(function () {
                let rowData = this.data();
                if (rowData && rowData.id == rowId) {
                    table.$('tr.selected').removeClass('selected');
                    $(this.node()).addClass('selected');
                    $(this.node()).find('.row-select').prop('checked', true);

                    // Scroll into view
                    let container = $('.fixed-height'); // your scrollable wrapper
                    let row = $(this.node());

                    if (container.length && row.length) {
                        container.animate({
                            scrollTop: row.position().top + container.scrollTop()
                        }, 400); // smooth scroll (400ms)
                    }
                }
            });

            let currentUrl = window.location.href;
            let basePath = currentUrl.split('/reals/details/')[0]; // keep base
            let newUrl = basePath + '/reals/details/' + rowId;
            history.replaceState(null, '', newUrl);
            renderDetailsPanel(rowId);

        }

        function renderDetailsPanel(rowId) {
            $.ajax({
                url: '/reals/show/' + rowId,  // route that returns the blade partial
                type: 'GET',
                beforeSend: function () {
                    $(".details-panel").html(`
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-light">
                <h5 class="mb-0 placeholder-glow">
                    <span class="placeholder col-6 rounded"></span>
                </h5>
            </div>
            <div class="card-body">
                <!-- Nav tabs skeleton -->
                <ul class="nav nav-tabs">
                    <li class="nav-item placeholder-glow">
                        <span class="placeholder-glow col-6 rounded"></span>
                    </li>
                    <li class="nav-item placeholder-glow">
                        <span class="placeholder-glow col-6 rounded"></span>
                    </li>
                </ul>

                <!-- Overview skeleton -->
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active">
                        <p class="placeholder-glow"><span class="placeholder col-4 rounded"></span></p>
                        <p class="placeholder-glow"><span class="placeholder col-6 rounded"></span></p>
                        <p class="placeholder-glow"><span class="placeholder col-5 rounded"></span></p>
                        <p class="placeholder-glow"><span class="placeholder col-4 rounded"></span></p>
                        <p class="placeholder-glow"><span class="placeholder col-4 rounded"></span></p>
                        <p class="placeholder-glow"><span class="placeholder col-7 rounded"></span></p>
                    </div>

                    <div class="tab-pane fade">
                        <table class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th class="placeholder col-2 rounded"></th>
                                    <th class="placeholder col-3 rounded"></th>
                                    <th class="placeholder col-2 rounded"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="placeholder-glow"><span class="placeholder col-6 rounded"></span></td>
                                    <td class="placeholder-glow"><span class="placeholder col-8 rounded"></span></td>
                                    <td class="placeholder-glow"><span class="placeholder col-4 rounded"></span></td>
                                </tr>
                                <tr>
                                    <td class="placeholder-glow"><span class="placeholder col-5 rounded"></span></td>
                                    <td class="placeholder-glow"><span class="placeholder col-7 rounded"></span></td>
                                    <td class="placeholder-glow"><span class="placeholder col-3 rounded"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `);
                },

                success: function (response) {
                    $(".details-panel").html(response.html);
                    renderProductionList(response.id);
                    selectInt();
                },
                error: function () {
                    $(".details-panel").html(
                        `<div class="alert alert-secondary text-center">Failed to load item details</div>`
                    );
                }
            });
        }

        // ✅ Handle row click
        table.on('click', '.item-select', function (e) {
            let rowId = $(this).data('id');
            $('.row-select').prop('checked', false);
            selectRowById(rowId);
        });

        // ✅ Preselect from URL
        let url = window.location.href;
        let selectedId = url.split('/').pop();

        if (selectedId) {
            // Ask server for page index
            $.get(baseURL + '/reals/get-page/' + selectedId, function (res) {
                if (res && res.page !== undefined) {
                    // Jump to correct page
                    table.page(res.page).draw(false);

                    // After that draw, highlight row
                    table.one('draw', function () {
                        selectRowById(selectedId);
                    });
                }
            });
        }

        // Add wrappers
        $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate')
            .wrap("<div class='card-body py-3'>");
    }

    $(document).ready(function () {
        loadDatatables();
    });

    $(document).on("change", ".transactionType", function () {
        const dropdown = $(this);
        const selected = dropdown.val();

        // Find the nearest container (tab-pane or fallback to document)
        const container = dropdown.closest(".tab-pane").length ? dropdown.closest(".tab-pane") : $(document);

        // Hide all tables inside that container
        container.find(".transaction-table").addClass("d-none");

        // Show only the selected table
        container.find(".table-" + selected).removeClass("d-none");
    });

    function renderProductionList(id) {
        let tableProduction = $(".table-production table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: `/reals/production-list/${id}`,
                data: function (d) {
                    d.work_order = $('#workOrderFilter').val();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'work_order', name: 'work_order' },
                { data: 'customer', name: 'customer' },
                { data: 'item', name: 'item' },
                { data: 'quantity', name: 'quantity' },
                { data: 'created_at', name: 'created_at' }
            ],
            dom: 'Bfrtip', // keep buttons
            searching: false, // remove default search box
            pageLength: 10, // initial rows
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'Export CSV',
                    className: 'btn btn-primary btn-sm',
                    filename: 'production_list',
                    exportOptions: { columns: ':visible' }
                }
            ],
            order: [[0, 'desc']]
        });

        // Status filter
        $('#workOrderFilter').on('change', function () {
            tableProduction.ajax.reload();
        });

        $('#rowsPerPage').on('change', function () {
            let val = parseInt($(this).val(), 10);
            tableProduction.page.len(val).draw();
        });

    }

    function selectInt() {
        $('#workOrderFilter').select2({
            placeholder: 'Select Work Order',
            allowClear: true,
            width: 'resolve'
        });
    }

});
