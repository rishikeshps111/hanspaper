
"use strict";

let originalButtonText;
const operation = $('#operation').val();
// let baseURL = $('#base_url').val();

$(document).ready(function () {

    // Initialize item search autocomplete
    // initializeItemSearch();

    // Form submission handler
    $("#itemForm1").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            url: form.closest('form').attr('action'),
            formObject: form
        };
        ajaxRequest(formArray);
    });

     $("#productionForm").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        if (!$('select[name="real_number"]').val()) {
            Swal.fire('Error', 'Please select a Real Number', 'error');
            return;
        }
        if (!$('input[name="is_finisher"]').val()) {
            Swal.fire('Error', 'Please answer the Finisher question', 'error');
            return;
        }
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            url: form.attr('action'),
            formObject: form
        };
        ajaxRequest(formArray);
    });

    $('select[name="real_number"]').on('change', function () {
        const realSelected = $(this).val();
         var tot_length='0';
           var bal_length='0';
        if (!realSelected) return;

         if (realSelected) {
      $.ajax({
            url: baseURL + '/api/getreal',
             dataType: "json",
          data: {
                real_id: realSelected
            },
          success: function (data) {
               if (data && data.length > 0) {

                tot_length=data['stocks_relation']['total_length'];
                bal_length=data['stocks_relation']['bal_length'];
              } else {
               
              }
             },
         error: function () {
                         }
        });
        }
        Swal.fire({
            title: 'Is this Real finished?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            let finisherValue;
            let stock_status;
            if (result.isConfirmed) {
                finisherValue = 'yes';

            Swal.fire({
  title: "Choose your option",
    html: `
    <div><label for="option1">Total Real Length : <b style='color:red'>${tot_length} m </b></label>
    &nbsp;&nbsp;<label for="option1">Balance Length : <b style='color:red'>${bal_length} m </b></label></div><br>
  `,
  input: "radio",
  inputOptions: {
    "excess": "Excess",
    "waste": "Waste"
  },
  inputValidator: (value) => {
    if (!value) {
      return "You need to choose something!";
    }
  }
}).then((result) => {
  if (result.value) {
 //   Swal.fire({ html: `You selected: ${result.value}` });
stock_status=result.value;
$('input[name="stock_status"]').val(stock_status);

  }
});
//return false;

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                finisherValue = 'no';
            } else {
                finisherValue = null;
            }
            if (finisherValue) {
                if ($('input[name="is_finisher"]').length) {
                    $('input[name="is_finisher"]').val(finisherValue);
                } else {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'is_finisher',
                        value: finisherValue
                    }).appendTo('#productionForm');
                                        $('<input>').attr({
                        type: 'hidden',
                        name: 'stock_status',
                        value: stock_status
                    }).appendTo('#productionForm');
                }
            }
            
        });
    });

    $("#packingForm").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            url: form.attr('action'),
            formObject: form
        };
        ajaxRequest(formArray);
    });
    
    $("#productEditForm").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            url: form.attr('action'),
            formObject: form
        };
        ajaxRequest(formArray);
    });

    $("#assignForm").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            url: form.attr('action'),
            formObject: form
        };
        ajaxRequest(formArray);
    });
    
    $(document).on('click', '.print-btn', function () {
        let id = $(this).data('id');

        $.ajax({
            url: '/production/print-view/' + id,
            type: 'GET',
            success: function (html) {
                let width = 700;
                let height = 900;

                // Calculate center position
                let left = (screen.width - width) / 2;
                let top = (screen.height - height) / 2;
                let printWindow = window.open('', '', `width=${width},height=${height},top=${top},left=${left}`);
                printWindow.document.write('<html><head><title>Print</title>');
                printWindow.document.write('<style>@media print { @page { size: A5 portrait; } body { font-family: Arial; } }</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write(html);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close(); // optional
            },
            error: function () {
                alert('Failed to load print content.');
            }
        });
    });
    
    document.querySelectorAll('#real_number option').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    // Handle item search and add to table
    // $('#search_item').on('keypress', function (e) {
    //     if (e.which === 13) { // Enter key
    //         e.preventDefault();
    //         addItemToForm();
    //     }
    // });

    // Disable submit button while the form is being processed
    function disableSubmitButton(form) {
        originalButtonText = form.find('button[type="submit"]').text();
        form.find('button[type="submit"]')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
    }

    // Enable submit button after form submission
    function enableSubmitButton(form) {
        form.find('button[type="submit"]')
            .prop('disabled', false)
            .html(originalButtonText);
    }

    // Before sending AJAX request, disable the button
    function beforeCallAjaxRequest(formObject) {
        disableSubmitButton(formObject);
    }

    // After receiving the response, enable the submit button
    function afterCallAjaxResponse(formObject) {
        enableSubmitButton(formObject);
    }

    // After successful AJAX request, handle UI updates
    function afterSuccessOfAjaxRequest(formObject, url = null) {
        // Adjust form after successful save (reset form, show message, etc.)
        formAdjustIfSaveOperation(formObject);
        pageRedirect(url);
    }

    // Redirect the page after successful form submission
    function pageRedirect(url) {
        let redirectTo = '/production';
        if (url) {
            redirectTo = url;
            setTimeout(function () {
                location.href = redirectTo;
            }, 1000);
        } else {
            setTimeout(function () {
                location.href = baseURL + redirectTo;
            }, 1000);
        } // Change this to wherever you want to redirect
        // Redirect after 1 second delay
    }

    // AJAX request function
    function ajaxRequest(formArray) {
        const formData = new FormData(document.getElementById(formArray.formId));
        const jqxhr = $.ajax({
            type: 'POST',
            url: formArray.url,
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': formArray.csrf
            },
            beforeSend: function () {
                if (typeof beforeCallAjaxRequest === 'function') {
                    beforeCallAjaxRequest(formArray.formObject);
                }
            },
        });

        jqxhr.done(function (data) {
            iziToast.success({ title: 'Success', layout: 2, message: data.message });
            if (typeof afterSuccessOfAjaxRequest === 'function') {
                afterSuccessOfAjaxRequest(formArray.formObject, data.redirect);
            }
        });

        jqxhr.fail(function (response) {
            if (response.status === 422) {
                const errors = response.responseJSON.errors;

                // Get the first error message
                let firstError = '';
                for (let field in errors) {
                    if (errors.hasOwnProperty(field) && errors[field].length > 0) {
                        firstError = errors[field][0];
                        break;
                    }
                }

                iziToast.error({
                    title: 'Validation Error',
                    layout: 2,
                    message: firstError,
                    timeout: 5000
                });
            } else {
                // fallback for other errors (like server error)
                const message = response.responseJSON?.message || 'Something went wrong!';
                iziToast.error({ title: 'Error', layout: 2, message: message });
            }
        });

        jqxhr.always(function () {
            if (typeof afterCallAjaxResponse === 'function') {
                afterCallAjaxResponse(formArray.formObject);
            }
        });
    }

    // Reset form if it's a save operation (POST)
    function formAdjustIfSaveOperation(formObject) {
        const _method = formObject.find('input[name="_method"]').val();
        if (_method && _method.toUpperCase() === 'POST') {
            const formId = formObject.attr("id");
            $("#" + formId)[0].reset();
        }
    }

    // Select All Checkboxes functionality (if needed)
    $("#select_all").on("click", function () {
        const checkBox = $(this).prop("checked");
        $(".row-select").prop("checked", checkBox);
        $(".row-select").each(function () {
            const permissionClass = $(this).attr("id");
            $("." + permissionClass + "_p").prop("checked", checkBox);
        });
    });

    // Group checkbox operation (if needed)
    $(".row-select").on("click", function () {
        const checkBox = $(this).prop("checked");
        const groupClassName = $(this).attr("id");
        $("." + groupClassName + "_p").each(function () {
            $(this).prop("checked", checkBox);
        });
    });

    if (operation == 'update') {
        // updateOperation(productionItemsData);
    }
});

// Initialize item search autocomplete
// function initializeItemSearch() {
//     $('#search_item').autocomplete({
//         source: function (request, response) {
//             $.ajax({
//                 url: baseURL + '/api/items/search',
//                 dataType: "json",
//                 data: {
//                     term: request.term
//                 },
//                 success: function (data) {
//                     // Transform the data to match autocomplete format
//                     const transformedData = data.map(function (item) {
//                         return {
//                             label: item.name + ' (' + item.item_code + ')',
//                             value: item.name,
//                             item: item
//                         };
//                     });
//                     response(transformedData);
//                 }
//             });
//         },
//         minLength: 2,
//         select: function (event, ui) {
//             addItemToForm(ui.item.item);
//             return false;
//         }
//     });
// }

// Add item to form
// function addItemToForm(selectedItem) {
//     const searchInput = $('#search_item');
//     const itemName = searchInput.val();

//     if (!itemName.trim()) {
//         iziToast.warning({ title: 'Warning', layout: 2, message: 'Please enter an item name' });
//         return;
//     }

//     // If no selectedItem provided, search for the item
//     if (!selectedItem) {
//         $.ajax({
//             url: baseURL + '/api/items/search',
//             dataType: "json",
//             data: {
//                 term: itemName
//             },
//             success: function (data) {
//                 if (data && data.length > 0) {
//                     setSelectedItem(data[0]);
//                     searchInput.val(data[0].name);
//                 } else {
//                     iziToast.warning({ title: 'Warning', layout: 2, message: 'Item not found. Please add it first.' });
//                 }
//             },
//             error: function () {
//                 iziToast.error({ title: 'Error', layout: 2, message: 'Error searching for item' });
//             }
//         });
//     } else {
//         setSelectedItem(selectedItem);
//         searchInput.val(selectedItem.name);
//     }
// }

// Set selected item in form
function setSelectedItem(item) {
    $('#selected_item_id').val(item.id);
    iziToast.success({ title: 'Success', layout: 2, message: 'Item selected: ' + item.name });
}

function updateOperation(productionRecord) {
    // Check if production_items exists and is an array
    if (productionRecord.production_items && Array.isArray(productionRecord.production_items)) {
        productionRecord.production_items.forEach((item) => {
            var dataObject = {
                production_item: item,  // The production item record
                item: item.item        // The nested item relation
            };
            addRowToInvoiceItemsTable(dataObject, true);
        });
    } else {
        console.error("production_items is not an array or doesn't exist", productionRecord);
    }
}

function addRowToInvoiceItemsTable(recordObject) {
    const { production_item, item } = recordObject;
    var currentRowId = getRowCount();
    var tableBody = tableId.find('tbody');
    var hiddenItemId = '<input type="hidden" name="item_id[' + currentRowId + ']" class="form-control" value="' + item.id + '">';
    var requested_qty = '<input type="hidden" name="requested_qty[' + currentRowId + ']" id="requested_qty-' + currentRowId + '" class="form-control" value="' + production_item.requested_qty + '">';
    var remaining_qty = '<input type="hidden" name="remaining_qty[' + currentRowId + ']" id="remaining_qty-' + currentRowId + '" class="form-control" value="' + production_item.remaining_qty + '">';
    var entered_qty = (production_item.status == 'Completed') ? production_item.entered_qty : '<input type="number" name="entered_qty[' + currentRowId + ']" id="entered_qty-' + currentRowId + '" value="' + production_item.remaining_qty + '" class="form-control" oninput="validateEnteredQty(' + currentRowId + ')">';
    var approved_qty = production_item.requested_qty - production_item.remaining_qty;
    var newRow = $('<tr id="' + currentRowId + '" class="highlight">');
    // newRow.append('<td>' + currentRowId + '</td>');
    newRow.append('<td>' + item.name + hiddenItemId + '</td>');
    newRow.append('<td>' + production_item.requested_qty + requested_qty + '</td>');
    newRow.append('<td>' + production_item.remaining_qty + remaining_qty + '</td>');
    newRow.append('<td>' + approved_qty + '</td>');
    newRow.append('<td>' + entered_qty + '</td>');
    newRow.append('<td>' + production_item.status + '</td>');
    // newRow.append('<td>--</td>');
    tableBody.prepend(newRow);
    afterAddRowFunctionality(currentRowId);
}

/**
 * HTML : After Add Row Functionality
 * */
function afterAddRowFunctionality(currentRowId) {
    //Remove Default existing row if exist
    removeDefaultRowFromTable();

    //Set Row Count
    setRowCount();


    //Reinitiate Tooltip
    // setTooltip();
}

/**
 * Remove Default Row from table
 * */
function removeDefaultRowFromTable() {
    if ($('.default-row').length) {
        $('.default-row').closest('tr').remove();
    }
}
/**
 * return Table row count
 * */
function getRowCount() {
    var rowCount = returnDecimalValueByName('row_count');
    return rowCount;
}
/**
* return Decimal input value
* */
function returnDecimalValueByName(inputBoxName) {
    var _inpuBoxId = $("input[name ='" + inputBoxName + "']");
    var inputBoxValue = _inpuBoxId.val();

    if (inputBoxValue == '' || isNaN(inputBoxValue)) {
        return parseFloat(0);
    }
    return parseFloat(inputBoxValue);
}


/**
 * set table row count
 * */
function setRowCount() {
    var increamentRowCount = getRowCount();
    increamentRowCount++;
    $('input[name="row_count"]').val(increamentRowCount);
}
function validateEnteredQty(rowId) {
    const enteredInput = document.getElementById('entered_qty-' + rowId);
    const remainingQty = parseFloat(document.getElementById('remaining_qty-' + rowId).value) || 0;
    let enteredValue = parseFloat(enteredInput.value) || 0;

    if (enteredValue > remainingQty) {
        // Show error message
        alert('Entered quantity cannot exceed remaining quantity of ' + remainingQty);

        // Reset to max allowed value
        enteredValue = remainingQty;
        enteredInput.value = enteredValue;
    }

    return enteredValue;
}

function validateEnteredQty1() {
    const enteredInput = document.getElementById('entered_qty');
    const remainingQty = parseFloat(document.getElementById('remaining_qty').value) || 0;
    let enteredValue = parseFloat(enteredInput.value) || 0;

    if (enteredValue > remainingQty) {
        // Show error message
        alert('Entered quantity cannot exceed remaining quantity of ' + remainingQty);

        // Reset to max allowed value
        enteredValue = remainingQty;
        enteredInput.value = enteredValue;
    }

    return enteredValue;
}
