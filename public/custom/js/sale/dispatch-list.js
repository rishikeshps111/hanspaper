$(function () {
    "use strict";

    const tableId = $('#datatable');
    const datatableForm = $("#datatableForm");

    /**
     * Server Side Datatable Records
     */
    function loadDatatables() {
        if ($.fn.dataTable.isDataTable(tableId)) {
            tableId.DataTable().clear().destroy();
        }

         var exportColumns = [0, 1, 2, 3, 4, 5, 6, 7]; 

        var table = tableId.DataTable({
            scrollX: true,
            scrollY: 315,
            pageLength:100,
            processing: true,
             searching: false,
            serverSide: true,
            method: 'get',
            ajax: {
                url: baseURL + '/dispatch/datatable-list',
                data: getDatatableFilterData(),
            },
            columns: getColumnDefinitions(),
            dom: "<'row'<'col-sm-12'<'float-start' l><'float-end' fr><'float-end ms-2'<'card-body ' B>>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            lengthMenu: [[10, 25, 100,500], [10, 25, 100,500]],
            buttons: getTableButtons(exportColumns),
            order: [[0, 'desc']],
             initComplete: function() {





           // Add filter for customer_id column
this.api().columns([3]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<br/><select class="form-select form-select-sm select2"><option value="">All Customers</option></select>')
        .appendTo(header)
        .on('change', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);
            
            // Get all active filters
            var filters = {
                customer_id: $(this).val(),
                 mode_name:  $('input[name="mode_name"]').val(),
                duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                dispatch_order_status: $('select[data-column="7"]').val() || currentParams.get('dispatch_order_status'),


              
            };
            
            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            // Reload with combined filters
            table.ajax.url(baseURL + '/dispatch/datatable-list?' + $.param(filters)).load();
        })
        .attr('data-column', '3'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('customer_id');
    if (initialCustomer) {
        select.val(initialCustomer);
    }
    
    $.get(baseURL + '/dispatch/uniquecustomers', function(data) {
        data.forEach(function(customer) {
            select.append('<option value="'+customer.id+'">'+customer.name+'</option>');
        });
    });
});


  $('.select2').select2();





//mode name
 
 /*this.api().columns([5]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<div><input name="mode_name" placeholder="Enter the Mode Name" ></div>')
        .appendTo(header)
        .on('change', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);

            // Get all active filters
            var filters = {
                mode_name:  $('input[name="mode_name"]').val(),
              customer_id: $('select[data-column="3"]').val() || currentParams.get('customer_id'),
                  duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                dispatch_order_status: $('select[data-column="8"]').val() || currentParams.get('dispatch_order_status'),

            
            };

            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            console.log($.param(filters));
            // Reload with combined filters
            table.ajax.url(baseURL + '/dispatch/datatable-list?' + $.param(filters)).load();
        })
        .attr('data-column', '5'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('customer_id');
    if (initialCustomer) {
       // select.val(initialCustomer);
    }
    
   
});*/







//due date sec


this.api().columns([5]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<div><input name="duedates" placeholder="DD-MM-YYYY" ></div>')
        .appendTo(header)
        .on('apply.daterangepicker', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);

            // Get all active filters
            var filters = {
  
                   mode_name:  $('input[name="mode_name"]').val(),
              customer_id: $('select[data-column="3"]').val() || currentParams.get('customer_id'),
                  duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                dispatch_order_status: $('select[data-column="7"]').val() || currentParams.get('dispatch_order_status'),
                // Add other filters here if needed
            };

            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            console.log($.param(filters));
            // Reload with combined filters
            table.ajax.url(baseURL + '/dispatch/datatable-list?' + $.param(filters)).load();
        })
        .attr('data-column', '5'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('customer_id');
    if (initialCustomer) {
       // select.val(initialCustomer);
    }
    
   
});


    $('input[name="duedates"]').daterangepicker({
    singleDatePicker: true,
    showDropdowns: false,
    minYear: 1901,
    maxYear: parseInt(moment().format('YYYY'),10),
    locale: {
      format: 'DD-MM-YYYY'
    }
  });

 $('input[name="duedates"]').val('');




//created gap
 
 this.api().columns([6]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<div><input name="gapdates" placeholder="Enter the Gap" ></div>')
        .appendTo(header)
        .on('change', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);

            // Get all active filters
            var filters = {
               
                
                 
                mode_name:  $('input[name="mode_name"]').val(),
              customer_id: $('select[data-column="3"]').val() || currentParams.get('customer_id'),
                  duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                dispatch_order_status: $('select[data-column="7"]').val() || currentParams.get('dispatch_order_status'),
            };

            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            console.log($.param(filters));
            // Reload with combined filters
            table.ajax.url(baseURL + '/dispatch/datatable-list?' + $.param(filters)).load();
        })
        .attr('data-column', '6'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('customer_id');
    if (initialCustomer) {
       // select.val(initialCustomer);
    }
    
   
});


// Add filter for status column
this.api().columns([7]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<select class="form-select form-select-sm"><option value="">All Statuses</option></select>')
        .appendTo(header)
        .on('change', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);
            
            // Get all active filters
            var filters = {
               
                dispatch_order_status: $(this).val(),
                 mode_name:  $('input[name="mode_name"]').val(),
              customer_id: $('select[data-column="3"]').val() || currentParams.get('customer_id'),
                  duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                // Add other filters here if needed
            };
            
            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            // Reload with combined filters
 table.ajax.url(baseURL + '/dispatch/datatable-list?' + $.param(filters)).load();        })
        .attr('data-column', '7'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialStatus = new URLSearchParams(window.location.search).get('dispatch_order_status');
    if (initialStatus) {
        select.val(initialStatus);
    }
    
    var statuses = ['Dispatch Pending', 'Dispatched', 'Completed'];
    statuses.forEach(function(status) {
        select.append('<option value="'+status+'">'+status+'</option>');
    });
});

this.api().columns([8]).every(function () {
                    var column = this;
                    var header = $(column.header());

                    var select = $('<select class="form-select form-select-sm"><option value="">All Return Status</option></select>')
                        .appendTo(header)
                        .on('change', function () {
                            // Get current URL parameters
                            var currentParams = new URLSearchParams(window.location.search);

                            // Get all active filters
                            var filters = {
                                dispatch_order_status: $('select[data-column="7"]').val() || currentParams.get('dispatch_order_status'),
                                return_status: $(this).val(),
                                mode_name: $('input[name="mode_name"]').val(),
                                customer_id: $('select[data-column="3"]').val() || currentParams.get('customer_id'),
                                duedate: $('input[name="duedates"]').val(),
                                gapdate: $('input[name="gapdates"]').val(),
                                // Add other filters here if needed
                            };

                            // Remove empty filters
                            Object.keys(filters).forEach(key => {
                                if (!filters[key]) delete filters[key];
                            });

                            // Reload with combined filters
                            table.ajax.url(baseURL + '/dispatch/datatable-list?' + $.param(filters)).load();
                        })
                        .attr('data-column', '8'); // Identify this select

                    // Set initial value if exists in URL
                    var initialReturnStatus = new URLSearchParams(window.location.search).get('return_status');
                    if (initialReturnStatus) {
                        select.val(initialReturnStatus);
                    }

                    // Define return statuses
                    var returnStatuses = ['Returned', 'Not Returned'];
                    returnStatuses.forEach(function (status) {
                        select.append('<option value="' + status + '">' + status + '</option>');
                    });
                });



      table.columns.adjust().draw();

                  },
            drawCallback: function () {
                setTooltip();
            }
        });

        $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate')
            .wrap("<div class='card-body py-3'>");
    }

    function getDatatableFilterData() {
        return {
            party_id: $('#party_id').val(),
            user_id: $('#user_id').val()
        };
    }

    function getColumnDefinitions() {
        return [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'dispatch_order',
                name: 'dispatch_order',
                render: function (data, type, row) {
                    const url = baseURL + '/dispatch/' + row.dispatch_id + '/edit';
                    return `<a class="dropdown-item1 text-dark" href="${url}">${data}
                            </a>`;
                }
            },
            {
                data: 'purchase_order_identifier',
                name: 'purchase_order_identifier',
                render: function (data, type, row) {
                    const url = baseURL + '/dispatch/' + row.dispatch_id + '/edit';
                    return `<a class="dropdown-item1 text-dark" href="${url}">${data || ''}</a>`;
                }
            },
            {
                data: 'customer_id',
                name: 'customer_id',
                render: function (data, type, row) {
                    const url = baseURL + '/dispatch/' + row.dispatch_id + '/edit';
                    return `<a class="dropdown-item1 text-dark" href="${url}">${data}
                            </a>`;
                }
            },
            {
                data: 'product_info',
                name: 'product_info',
                render: function (data, type, row) {
                    return `${data}`;

                }
            },
            /*
              {
                 data: 'mode_of_delivery',
                 name: 'mode_of_delivery',
                 render: function (data, type, row) {
                     const url = baseURL + '/dispatch/' + row.id + '/edit';
                     return `<a class="dropdown-item1 text-dark" href="${url}">${data}
                             </a>`;
                 }
             },*/

            {
                data: 'created_at',
                name: 'created_at', // must match filterColumn name in Laravel
                render: function (data, type, row) {
                    const url = baseURL + '/dispatch/' + row.dispatch_id + '/edit';

                    // Ensure the date is not null or empty
                    const displayDate = data ? data : 'N/A';

                    return `<a class="dropdown-item1 text-dark" href="${url}">${displayDate}</a>`;
                }
            },

            //  {
            //     data: 'remarks',
            //     name: 'remarks',
            //     render: function (data, type, row) {
            //         const url = baseURL + '/dispatch/' + row.id + '/edit';
            //         return `<a class="dropdown-item1 text-dark" href="${url}">${data}
            //                 </a>`;
            //     }
            // },
            {
                data: 'CreatedGap',
                name: 'CreatedGap',
                searchable: true,
                orderable: false,
                title: 'Ageing'
            },



            {
                data: 'status',
                name: 'status',
                render: function (data, type, row) {
                    const url = baseURL + '/dispatch/' + row.dispatch_id + '/edit';
                    return `<a class="dropdown-item1 text-dark" href="${url}">${data}
                                </a>`;
                }
            },
             {
                data: 'return_status',
                name: 'return_status'
            },
            // { data: 'purchase_order_identifier', name: 'purchase_order_id' },
            // { data: 'customer_id', name: 'customer_id' },
            // { data: 'mode_of_delivery', name: 'mode_of_delivery' },
            // { data: 'created_at', name: 'created_at' },
            // { data: 'remarks', name: 'remarks' },
            //  { data: 'status', name: 'status' },  
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ];
    }


    
    let exportFormatter = {

    format: {
                stripHtml: true ,

        body: function (data, row, column, node) {
           
             if (column === 9) {

                    data = '';
                        }
            
                 var cmd = String(data).replace(/<.*?>/ig, "");
                return cmd;
        }
    }
};
    function getTableButtons(exportColumns) {
        return [

            {
                extend: 'copyHtml5',columns: exportColumns, exportOptions: exportFormatter,  "action": newexportaction
            },

            {
            extend: 'excelHtml5',columns: exportColumns,  exportOptions: exportFormatter,  "action": newexportaction,
            },
            {extend: 'csvHtml5', columns: exportColumns, exportOptions: exportFormatter,  "action": newexportaction},
            /*{
                extend: 'pdfHtml5',
                orientation: 'portrait',
               columns: exportColumns,   "action": newexportaction,
            }*/

        ];
    }
    function newexportaction(e, dt, button, config) {
        var self = this;
        var oldStart = dt.settings()[0]._iDisplayStart;
        dt.one('preXhr', function (e, s, data) {
            data.start = 0;
            data.length = 21474836;
            dt.one('preDraw', function (e, settings) {
                if (button[0].className.indexOf('buttons-copy') >= 0) {
                    $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
                } else if (button[0].className.indexOf('buttons-excel') >= 0) {
                    $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
                        $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
                } else if (button[0].className.indexOf('buttons-csv') >= 0) {
                    $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
                        $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
                        $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
                } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
                    $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
                        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
                        $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
                } else if (button[0].className.indexOf('buttons-print') >= 0) {
                    $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
                }
                dt.one('preXhr', function (e, s, data) {
                    settings._iDisplayStart = oldStart;  // Restaura o índice de início
                    data.start = oldStart;  // Restaura o valor de 'start' da consulta
                });
                setTimeout(dt.ajax.reload, 0);
                return false;
            });
        });
        dt.ajax.reload();
    }

    function ajaxRequest(formArray) {
        var jqxhr = $.ajax({
            type: formArray._method,
            url: formArray.url,
            data: formArray.formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': formArray.csrf },
        });

        jqxhr.done(function (data) {
            iziToast.success({ title: 'Success', layout: 2, message: data.message });
        });

        jqxhr.fail(function (response) {
            var message = response.responseJSON.message;
            iziToast.error({ title: 'Error', layout: 2, message: message });
        });

        jqxhr.always(function () {
            if (typeof afterCallAjaxResponse === 'function') {
                afterCallAjaxResponse(formArray.formObject);
            }
        });
    }

    function afterCallAjaxResponse(formObject) {
        loadDatatables();
    }

    $(document).ready(function () {
        loadDatatables();
    });
    function setTooltip() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }
    
      $(document).on('click', '.btn-return', function () {
        const itemId = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "Do you want to return this item?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, return it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $('#item_id').val(itemId);
                $('#returnModal').modal('show');
            }
        });
    });

    $('input[name="is_damaged"]').on('change', function () {
        if ($(this).val() == '1') {
            $('.reason-box').removeClass('d-none');
        } else {
            $('.reason-box').addClass('d-none');
        }
    });

    $('#returnForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/dispatch/store-return',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.status) {
                    Swal.fire('Success', 'Item return recorded!', 'success');
                    $('#returnModal').modal('hide');
                    $('#datatable').DataTable().ajax.reload();
                }
            }, error: function (xhr) {
                if (xhr.status === 422) { // Laravel validation error
                    const errors = xhr.responseJSON.errors;
                    let errorMessages = '';

                    $.each(errors, function (key, value) {
                        errorMessages += value[0] + '<br>';
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMessages
                    });
                } else {
                    Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                }
            }
        });
    });

    // Reset modal when hidden
    $('#returnModal').on('hidden.bs.modal', function () {
        const form = $(this).find('form')[0];
        form.reset();               // Reset all form fields
        $('.reason-box').addClass('d-none');  // Hide the reason textarea
    });

    $(document).on('click', '.btn-view-return', function () {
        const returnId = $(this).data('id');
        $.ajax({
            url: '/dispatch/return-details/' + returnId,
            type: 'GET',
            success: function (response) {
                $('#returnDamaged').text(response.is_damaged ? 'Yes' : 'No');

                if (response.is_damaged) {
                    $('#returnReasonContainer').removeClass('d-none');
                    $('#returnReason').text(response.reason);
                } else {
                    $('#returnReasonContainer').addClass('d-none');
                    $('#returnReason').text('');
                }

                $('#returnDate').text(response.created_at);
                var modal = new bootstrap.Modal(document.getElementById('returnDetailsModal'));
                modal.show();
            },
            error: function () {
                alert('Failed to fetch return details.');
            }
        });
    });

});
