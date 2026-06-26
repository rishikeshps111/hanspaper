
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
        var exportColumns = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10,11];
        
            
        
        table = tableId.DataTable({
            scrollX: true,
            scrollY: 315,
            pageLength:100,
             searching: false,
            processing: true,
            serverSide: true,
            method: 'get',
            ajax: {
                url: baseURL + '/production/datatable-filter-list',
                data: getDatatableFilterData(),
            },
            columns: getColumnDefinitions(),
            dom: "<'row'<'col-sm-12'<'float-start' l><'float-end' fr><'float-end ms-2'<'card-body ' B>>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
           lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
              language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",

                },
                pagingType: "full_numbers",
            buttons: getTableButtons(exportColumns),
            order: [[0, 'desc']],
            initComplete: function() {

           // Add filter for customer_id column
this.api().columns([1]).every(function() {
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
                product_name: $('select[data-column="3"]').val() || currentParams.get('product_name'),
                 brand_name: $('select[data-column="4"]').val() || currentParams.get('brand_name'),
                                category_name: $('select[data-column="5"]').val() || currentParams.get('category_name'),
                duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                product_order_status: $('select[data-column="11"]').val() || currentParams.get('product_order_status')

              
            };
            
            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            // Reload with combined filters
            table.ajax.url(baseURL + '/production/datatable-filter-list?' + $.param(filters)).load();
        })
        .attr('data-column', '1'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('customer_id');
    if (initialCustomer) {
        select.val(initialCustomer);
    }
    
    $.get(baseURL + '/production/uniquecustomerswithstatus/'+urlstatus+'', function(data) {
        data.forEach(function(customer) {
            select.append('<option value="'+customer.id+'">'+customer.name+'</option>');
        });
    });
});


  $('.select2').select2();



// Add filter for product column
this.api().columns([3]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select1 = $('<br/><select class="form-select form-select-sm select3" style="width:200px !important"><option value=""> All Products</option></select>')
        .appendTo(header)
        .on('change', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);
            
            // Get all active filters
            var filters = {
                product_name: $(this).val(),
                 brand_name: $('select[data-column="4"]').val() || currentParams.get('brand_name'),
                                category_name: $('select[data-column="5"]').val() || currentParams.get('category_name'),
                duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                product_order_status: $('select[data-column="11"]').val() || currentParams.get('product_order_status'),
              customer_id: $('select[data-column="1"]').val() || currentParams.get('customer_id'),

              
            };
            
            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            // Reload with combined filters
            table.ajax.url(baseURL + '/production/datatable-filter-list?' + $.param(filters)).load();
        })
        .attr('data-column', '3'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('product_name');
    if (initialCustomer) {
        select1.val(initialCustomer);
    }
    
     $.get(baseURL + '/production/uniqueproductswithstatus/'+urlstatus+'', function(data) {

        data.forEach(function(customer) {
            select1.append('<option value="'+customer.id+'">'+customer.name+'</option>');
        });
    });
});


  $('.select3').select2();

this.api().columns([4]).every(function () {
                    var column = this;
                    var header = $(column.header());

                    var select = $('<br/><select class="form-select form-select-sm select-brand"><option value="">All Brands</option></select>')
                        .appendTo(header)
                        .on('change', function () {

                            var currentParams = new URLSearchParams(window.location.search);

                            var filters = {
                                brand_name: $(this).val(),
                                customer_id: $('select[data-column="1"]').val() || currentParams.get('customer_id'),
                                product_name: $('select[data-column="3"]').val() || currentParams.get('product_name'),
                                category_name: $('select[data-column="5"]').val() || currentParams.get('category_name'),
                                duedate: $('input[name="duedates"]').val(),
                                gapdate: $('input[name="gapdates"]').val(),
                                product_order_status: $('select[data-column="11"]').val() || currentParams.get('product_order_status')
                            };

                            Object.keys(filters).forEach(key => {
                                if (!filters[key]) delete filters[key];
                            });

                            table.ajax.url(baseURL + '/production/datatable-filter-list?' + $.param(filters)).load();
                        })
                        .attr('data-column', '4');

                    // Load brand list
                    $.get(baseURL + '/production/uniquebrands', function (data) {
                        data.forEach(function (brand) {
                            select.append('<option value="' + brand.name + '">' + brand.name + '</option>');
                        });
                    });
                });

                $('.select-brand').select2();


                //category

                this.api().columns([5]).every(function () {
                    var column = this;
                    var header = $(column.header());

                    var select = $('<br/><select class="form-select form-select-sm select-category"><option value="">All Categories</option></select>')
                        .appendTo(header)
                        .on('change', function () {

                            var currentParams = new URLSearchParams(window.location.search);

                            var filters = {
                                category_name: $(this).val(),
                                brand_name: $('select[data-column="4"]').val() || currentParams.get('brand_name'),
                                customer_id: $('select[data-column="1"]').val() || currentParams.get('customer_id'),
                                product_name: $('select[data-column="3"]').val() || currentParams.get('product_name'),
                                duedate: $('input[name="duedates"]').val(),
                                gapdate: $('input[name="gapdates"]').val(),
                                product_order_status: $('select[data-column="11"]').val() || currentParams.get('product_order_status')
                            };

                            Object.keys(filters).forEach(key => {
                                if (!filters[key]) delete filters[key];
                            });

                            table.ajax.url(baseURL + '/production/datatable-filter-list?' + $.param(filters)).load();
                        })
                        .attr('data-column', '5');

                    // Load category list
                    $.get(baseURL + '/production/uniquecategories', function (data) {
                        data.forEach(function (category) {
                            select.append('<option value="' + category.name + '">' + category.name + '</option>');
                        });
                    });
                });

                $('.select-category').select2();






//due date sec



                this.api().columns([9]).every(function () {
                    var column = this;
                    var header = $(column.header());

                    // Create input for date range
                    var input = $('<div><input name="duedates" placeholder="DD-MM-YYYY"></div>')
                        .appendTo(header)
                        .find('input')
                        .daterangepicker({
                            opens: 'left',
                            autoUpdateInput: false,
                            locale: {
                                format: 'DD-MM-YYYY',
                                cancelLabel: 'Clear'
                            }
                        })
                        .on('apply.daterangepicker', function (ev, picker) {
                            // Show selected date range in input
                            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));

                            // Collect filters
                            var currentParams = new URLSearchParams(window.location.search);
                            var filters = {
                                duedate: $(this).val(),
                                 brand_name: $('select[data-column="4"]').val() || currentParams.get('brand_name'),
                                category_name: $('select[data-column="5"]').val() || currentParams.get('category_name'),
                                customer_id: $('select[data-column="1"]').val() || currentParams.get('customer_id'),
                                product_name: $('select[data-column="3"]').val() || currentParams.get('product_name'),
                                gapdate: $('input[name="gapdates"]').val(),
                                product_order_status: $('select[data-column="11"]').val() || currentParams.get('product_order_status')
                            };

                            // Remove empty filters
                            Object.keys(filters).forEach(key => {
                                if (!filters[key]) delete filters[key];
                            });

                            // Reload table with filters
                            table.ajax.url(baseURL + '/production/datatable-list?' + $.param(filters)).load();
                        })
                        .on('cancel.daterangepicker', function (ev, picker) {
                            // Clear input
                            $(this).val('');

                            // Reset filter
                            var currentParams = new URLSearchParams(window.location.search);
                            var filters = {
                                 brand_name: $('select[data-column="4"]').val() || currentParams.get('brand_name'),
                                category_name: $('select[data-column="5"]').val() || currentParams.get('category_name'),
                                customer_id: $('select[data-column="1"]').val() || currentParams.get('customer_id'),
                                product_name: $('select[data-column="3"]').val() || currentParams.get('product_name'),
                                gapdate: $('input[name="gapdates"]').val(),
                                product_order_status: $('select[data-column="11"]').val() || currentParams.get('product_order_status')
                            };

                            Object.keys(filters).forEach(key => {
                                if (!filters[key]) delete filters[key];
                            });

                            table.ajax.url(baseURL + '/production/datatable-list?' + $.param(filters)).load();
                        });

                    // Optional: set initial value if exists in URL
                    var initialDuedate = new URLSearchParams(window.location.search).get('duedate');
                    if (initialDuedate) {
                        input.val(initialDuedate);
                    }
                });
 
//created gap
 
 this.api().columns([10]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<div><input name="gapdates" placeholder="Enter the Gap" ></div>')
        .appendTo(header)
        .on('change', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);

            // Get all active filters
            var filters = {
               
                // Add other filters here if needed
                 duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                   brand_name: $('select[data-column="4"]').val() || currentParams.get('brand_name'),
                                category_name: $('select[data-column="5"]').val() || currentParams.get('category_name'),
                  customer_id: $('select[data-column="1"]').val() || currentParams.get('customer_id'),
                product_name: $('select[data-column="3"]').val() || currentParams.get('product_name'),
                product_order_status: $('select[data-column="11"]').val() || currentParams.get('product_order_status')

            };

            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            console.log($.param(filters));
            // Reload with combined filters
            table.ajax.url(baseURL + '/production/datatable-filter-list?' + $.param(filters)).load();
        })
        .attr('data-column', '10'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialCustomer = new URLSearchParams(window.location.search).get('customer_id');
    if (initialCustomer) {
       // select.val(initialCustomer);
    }
    
   
});

 
// Add filter for status column
this.api().columns([11]).every(function() {
    var column = this;
    var header = $(column.header());
    
    var select = $('<select class="form-select form-select-sm"><option value="">All Statuses</option></select>')
        .appendTo(header)
        .on('change', function() {
            // Get current URL parameters
            var currentParams = new URLSearchParams(window.location.search);
            
            // Get all active filters
            var filters = {
               
                product_order_status: $(this).val(),
                product_name: $('select[data-column="3"]').val() || currentParams.get('product_name'),
              customer_id: $('select[data-column="1"]').val() || currentParams.get('customer_id'),
                brand_name: $('select[data-column="4"]').val() || currentParams.get('brand_name'),
                                category_name: $('select[data-column="5"]').val() || currentParams.get('category_name'),
              duedate:  $('input[name="duedates"]').val(),
                gapdate:  $('input[name="gapdates"]').val(),
                // Add other filters here if needed
            };
            
            // Remove empty filters
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });
            
            // Reload with combined filters
 table.ajax.url(baseURL + '/production/datatable-filter-list?' + $.param(filters)).load();        })
        .attr('data-column', '11'); // Add data attribute to identify this select
    
    // Set initial value if exists in URL
    var initialStatus = new URLSearchParams(window.location.search).get('product_order_status');
    if (initialStatus) {
        select.val(initialStatus);
    }
    
    var statuses = ['Pending', 'Packing Pending','Assigning Pending', 'Completed','Partial','Progress','Cancelled'];
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
           /* party_id: $('#party_id').val(),
            user_id: $('#user_id').val(),
            from_date: $('input[name="from_date"]').val(),
            to_date: $('input[name="to_date"]').val(),*/
            cstatus:  $('input[name="cstatus"]').val(),

        };
    }

    function getColumnDefinitions() {
        return [
            {
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false,
                                render: function (data, type, row) {
                   const url = baseURL + '/production/edit/'+ row.id;
                    return `<a href="${url}" >${data}</a>`;
                }

            },
            {
                data: 'customer',
                name: 'customer',
                 orderable: false,
               
            },
            {
                data: 'work_order',
                name: 'work_order',
                orderable: false,
                render: function (data, type, row) {
                   const url = baseURL + '/purchaseorder/'+ row.id+'/edit/';
                    return `<a href="${url}" class="text-dark">${data}</a>`;
                }
            },
            {
                data: 'product_name',
                name: 'product_name',
                orderable: false,
                render: function (data, type, row) {
                                     return `${data}`;
                }
            },
            {
                data: 'brand',
                name: 'brand',
                orderable: false,
                render: function (data, type, row) {
                                      return `${data}`;

                }
            },
            {
                data: 'category',
                name: 'category',
                  orderable: false,
                render: function (data, type, row) {
                                      return `${data}`;

                }
            },
  {
                data: 'requested_qty',
                name: 'requested_qty',
                  orderable: false,
                render: function (data, type, row) {
                                    return `${data}`;

                }
            },
              {
                data: 'production_remaining_qty',
                name: 'production_remaining_qty',
                  orderable: false,
                render: function (data, type, row) {
                                     return `${data}`;
                }
            },
              {
                data: 'packing_remaining_qty',
                name: 'packing_remaining_qty',
                  orderable: false,
                render: function (data, type, row) {
                                      return `${data}`;

                }
            },
            {
                data: 'due_date',
                name: 'due_date',
                  orderable: false,
                render: function (data, type, row) {
                                                        return `${data}`;
                }
            },
            { 
                data: 'ageing', 
                name: 'ageing', 
                orderable: false, 
                searchable: false 
            },
            {
                data: 'status',
                name: 'status',
                render: function (data, type, row) {
                      return `${data}`;
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

   
  let exportFormatter = {
        format: {
            stripHtml: true,
            header: function (data, column, node) {
                // Get the original header text without the filter elements
                // This extracts just the text content before any <br/> or filter elements
                var $node = $(node);
                var headerText = $node.contents().filter(function () {
                    return this.nodeType === 3; // Text nodes only
                }).text().trim();

                // If no text found, try getting from the original column definition
                if (!headerText) {
                    headerText = data;
                }

                // Custom header names (optional)
                const customHeaders = {
                    0: 'ID',
                    1: 'Customer Name',
                    2: 'Work Order No.',
                    3: 'Product Name',
                    4: 'Brand',
                    5: 'Category',
                    6: 'Requested Quantity',
                    7: 'Production Remaining',
                    8: 'Packing Remaining',
                    9: 'Due Date',
                    10: 'Ageing (Days)',
                    11: 'Status',
                    12: '',
                };

                return customHeaders[column] || headerText;
            },
            body: function (data, row, column, node) {
                if (column === 12) {
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
});