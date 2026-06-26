
$(function () {
    "use strict";
            
         
    const tableId = $('#datatable');
    const datatableForm = $("#datatableForm");
    let table; // Declare table variable at a higher scope


    let currentURL = window.location.href;
    const character = "/view/";
     var urlstatus=currentURL.split('/view/').pop();

    /**
     * Initialize DataTable with server-side processing
     */
    function initializeDataTable() {
        var exportColumns = [0, 1, 2, 3, 4, 5, 6];
        
            
        
        table = tableId.DataTable({
            scrollX: true,
            scrollY: 315,
            pageLength:100,
            processing: true,
            serverSide: true,
            method: 'get',
            ajax: {
                url: baseURL + '/purchaseorder/datatable-filter-list',
                data: getDatatableFilterData(),
            },
            columns: getColumnDefinitions(),
            dom: "<'row'<'col-sm-12'<'float-start' l><'float-end' fr><'float-end ms-2'<'card-body ' B>>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            lengthMenu: [[10, 25, 100, 500], [10, 25, 100, 500]],
            buttons: getTableButtons(exportColumns),
            order: [[0, 'desc']],
            initComplete: function() {
             // Add filter for customer_id column
this.api().columns([2]).every(function() {
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
                purchase_order_status: $('select[data-column="6"]').val() || currentParams.get('purchase_order_status'),
                podate:  $('input[name="podates"]').val(),
                  gapdate:  $('input[name="gapdates"]').val(),
                duedate:  $('input[name="duedates"]').val(),

                // Add other filters here if needed
            };
            
            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            // Reload with combined filters
            table.ajax.url(baseURL + '/purchaseorder/datatable-filter-list?' + $.param(filters)).load();
        })
        .attr('data-column', '2'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('customer_id');
    if (initialCustomer) {
        select.val(initialCustomer);
    }
    
    $.get(baseURL + '/purchaseorder/uniquecustomerswithstatus/'+urlstatus+'', function(data) {
        data.forEach(function(customer) {
            select.append('<option value="'+customer.id+'">'+customer.name+'</option>');
        });
    });
});


  $('.select2').select2();

//po date sec

this.api().columns([3]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<div><input name="podates" placeholder="DD-MM-YYYY" ></div>')
        .appendTo(header)
        .on('apply.daterangepicker', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);

            // Get all active filters
            var filters = {
                customer_id: $('select[data-column="2"]').val() || currentParams.get('customer_id'),
                podate:  $('input[name="podates"]').val(),
                duedate:  $('input[name="duedates"]').val(),
                  gapdate:  $('input[name="gapdates"]').val(),
                purchase_order_status: $('select[data-column="6"]').val() || currentParams.get('purchase_order_status')
                // Add other filters here if needed
            };

            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            // Reload with combined filters
            table.ajax.url(baseURL + '/purchaseorder/datatable-filter-list?' + $.param(filters)).load();
        })
        .attr('data-column', '3'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('customer_id');
    if (initialCustomer) {
       // select.val(initialCustomer);
    }
    
    $.get(baseURL + '/purchaseorder/uniquecustomerswithstatus/'+urlstatus+'', function(data) {
        data.forEach(function(customer) {
           // select.append('<option value="'+customer.id+'">'+customer.name+'</option>');
        });
    });
});


    $('input[name="podates"]').daterangepicker({
    singleDatePicker: true,
    showDropdowns: false,
    minYear: 1901,
    maxYear: parseInt(moment().format('YYYY'),10),
    locale: {
      format: 'DD-MM-YYYY'
    }
  });

 $('input[name="podates"]').val('');
 
 
 
 //due date sec


this.api().columns([4]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<div><input name="duedates" placeholder="DD-MM-YYYY" ></div>')
        .appendTo(header)
        .on('apply.daterangepicker', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);

            // Get all active filters
            var filters = {
                customer_id: $('select[data-column="2"]').val() || currentParams.get('customer_id'),
                podate:  $('input[name="podates"]').val(),
                duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                purchase_order_status: $('select[data-column="6"]').val() || currentParams.get('purchase_order_status')
                // Add other filters here if needed
            };

            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            console.log($.param(filters));
            // Reload with combined filters
            table.ajax.url(baseURL + '/purchaseorder/datatable-filter-list?' + $.param(filters)).load();
        })
        .attr('data-column', '4'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('customer_id');
    if (initialCustomer) {
       // select.val(initialCustomer);
    }
    
    $.get(baseURL + '/purchaseorder/uniquecustomerswithstatus/'+urlstatus+'', function(data) {
        data.forEach(function(customer) {
           // select.append('<option value="'+customer.id+'">'+customer.name+'</option>');
        });
    });
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
 
 this.api().columns([5]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<div><input name="gapdates" placeholder="Enter the Gap" ></div>')
        .appendTo(header)
        .on('change', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);

            // Get all active filters
            var filters = {
                customer_id: $('select[data-column="2"]').val() || currentParams.get('customer_id'),
                podate:  $('input[name="podates"]').val(),
                duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                purchase_order_status: $('select[data-column="6"]').val() || currentParams.get('purchase_order_status')
                // Add other filters here if needed
            };

            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            console.log($.param(filters));
            // Reload with combined filters
            table.ajax.url(baseURL + '/purchaseorder/datatable-filter-list?' + $.param(filters)).load();
        })
        .attr('data-column', '5'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('customer_id');
    if (initialCustomer) {
       // select.val(initialCustomer);
    }
    
    $.get(baseURL + '/purchaseorder/uniquecustomerswithstatus/'+urlstatus+'', function(data) {
        data.forEach(function(customer) {
           // select.append('<option value="'+customer.id+'">'+customer.name+'</option>');
        });
    });
});

 














// Add filter for status column
this.api().columns([6]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<select class="form-select form-select-sm"><option value="">All Statuses</option></select>')
        .appendTo(header)
        .on('change', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);
            
            // Get all active filters
            var filters = {
                customer_id: $('select[data-column="2"]').val() || currentParams.get('customer_id'),
                podate:  $('input[name="podates"]').val(),
                duedate:  $('input[name="duedates"]').val(),
                 gapdate:  $('input[name="gapdates"]').val(),
                purchase_order_status: $(this).val()
                // Add other filters here if needed
            };
            
            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            // Reload with combined filters
            table.ajax.url(baseURL + '/purchaseorder/datatable-filter-list?' + $.param(filters)).load();
        })
        .attr('data-column', '6'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialStatus = new URLSearchParams(window.location.search).get('purchase_order_status');
    if (initialStatus) {
        select.val(initialStatus);
    }
    
    var statuses = ['Production', 'Dispatch Pending', 'Dispatched','Completed'];
    statuses.forEach(function(status) {
        select.append('<option value="'+status+'">'+status+'</option>');
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
            user_id: $('#user_id').val(),
            from_date: $('input[name="from_date"]').val(),
            to_date: $('input[name="to_date"]').val(),
            cstatus:  $('input[name="cstatus"]').val(),


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
                data: 'purchase_order_id',
                name: 'purchase_order_id',
                render: function (data, type, row) {
                    const url = baseURL + '/purchaseorder/' + row.id + '/details';
                    return `<a href="${url}" class="text-dark">${data}</a>`;
                }
            },
            {
                data: 'customer_id',
                name: 'customer_id',
                render: function (data, type, row) {
                   const url = baseURL + '/purchaseorder/'+ row.id+'/edit/';
                    return `<a href="${url}" class="text-dark">${data}</a>`;
                }
            },
            {
                data: 'po_date',
                name: 'po_date',
                render: function (data, type, row) {
                   const url = baseURL + '/purchaseorder/'+ row.id+'/edit/';
                    return `<a href="${url}" class="text-dark">${data}</a>`;
                }
            },
            {
                data: 'due_date',
                name: 'due_date',
                render: function (data, type, row) {
                    const url = baseURL + '/purchaseorder/'+ row.id+'/edit/';
                    return `<a href="${url}" class="text-dark">${data}</a>`;
                }
            },
            { 
                data: 'CreatedGap', 
                name: 'CreatedGap', 
                orderable: false, 
                searchable: false 
            },
            {
                data: 'purchase_order_status',
                name: 'purchase_order_status',
                render: function (data, type, row) {
                    let badgeClass = 'secondary';

                    switch (data) {
                        case 'Pending':
                            badgeClass = 'warning';
                            break;
                        case 'Processing':
                            badgeClass = 'info';
                            break;
                        case 'Cancelled':
                            badgeClass = 'danger';
                            break;
                        case 'Completed':
                            badgeClass = 'success';
                            break;
                        case 'Production':
                            badgeClass = 'primary';
                            break;
                        case 'Dispatched':
                            badgeClass = 'dark';
                            break;
                        case 'Ready to Dispatch':
                            badgeClass = 'success';
                            break;
                        case 'Dispatch Pending':
                            badgeClass = 'secondary';
                            break;
                    }

                    return `<span class="badge bg-${badgeClass}">${data}</span>`;
                }
            },
            { 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false 
            }
        ];
    }

  /*  function getTableButtons(exportColumns) {
        return [
            {
                extend: 'copyHtml5',
                exportOptions: { columns: exportColumns }
            },
            {
                extend: 'excelHtml5',
                exportOptions: { columns: exportColumns }
            },
            {
                extend: 'csvHtml5',
                exportOptions: { columns: exportColumns }
            },
            {
                extend: 'pdfHtml5',
                orientation: 'portrait',
                exportOptions: { columns: exportColumns },
            }
        ];
    }*/
     let exportFormatter = {

    format: {
                stripHtml: true ,

        body: function (data, row, column, node) {
           
             if (column === 7) {

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
        table.ajax.reload();
    }

    function setTooltip() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    // Initialize the DataTable when document is ready
    $(document).ready(function () {
        initializeDataTable();
    });

    // Reload table when filter values change
    $(document).on("change", '#party_id, #user_id, input[name="from_date"], input[name="to_date"],input[name="cstatus"]', function () {
        table.ajax.reload();
    });
    
    $(document).on('click', '.cancel-btn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "You are about to cancel this purchase order.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, cancel it!",
            cancelButtonText: "No, keep it"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/purchaseorder/cancel',
                    method: 'POST',
                    data: {
                        "_token": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        "id": id
                    },
                    success: function (response) {
                        console.log(response)
                        Swal.fire("Cancelled!", response.message, "success");
                        // Optionally reload table or remove row from DOM
                        table.ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.fire("Error!", xhr.responseJSON?.message || "Something went wrong", "error");
                    }
                });
            }
        });
    });
});