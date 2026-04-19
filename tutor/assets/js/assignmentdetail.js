
$(document).ready(function () {

    // ================= USER INFO POPUP =================
    $(document).on('click', '.user-profile', function () {

        $('#profileName').text($(this).data('name'));
        $('#profileRole').text($(this).data('role'));
        $('#profileLocation').text($(this).data('location'));

        $('#infoFullName').text($(this).data('name'));
        $('#infoEmail').text($(this).data('email'));
        $('#infoPhone').text($(this).data('phone'));
        $('#infoLanguage').text($(this).data('language'));
        $('#infoAddress').text($(this).data('address'));
        $('#infoDegree').text($(this).data('degree'));

        $('#infoCourses').html('');
        $(this).data('courses').forEach(c =>
            $('#infoCourses').append(`<li>${c}</li>`)
        );

        $('#infoCertificates').html('');
        $(this).data('certificates').forEach(c =>
            $('#infoCertificates').append(`<li>${c}</li>`)
        );


        $('#userProfileModal').modal('show');
    });




    // ============== DYNAMIC VALIDATION SYSTEM ==============

    const ValidationRules = {
        Name: {
            rules: [
                {
                    test: (val) => val.trim().length >= 2,
                    message: 'Name must be at least 2 characters long'
                },
                {
                    test: (val) => /^[a-zA-Z\s]+$/.test(val),
                    message: 'Name should contain only letters'
                }
            ]
        },
        Email: {
            rules: [
                {
                    test: (val) => val.trim().length > 0,
                    message: 'Email is required'
                },
                {
                    test: (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val),
                    message: 'Please enter a valid email address'
                }
            ]
        },
        Phone: {
            rules: [
                {
                    test: (val) => val.trim().length > 0,
                    message: 'Phone number is required'
                },
                {
                    test: (val) => /^\d{10}$/.test(val.replace(/\s|-/g, '')),
                    message: 'Phone must be exactly 10 digits'
                }
            ]
        },
        Course: {
            rules: [
                {
                    test: (val) => val.trim().length >= 2,
                    message: 'Course name must be at least 2 characters'
                }
            ]
        },
        Enrolled: {
            rules: [
                {
                    test: (val) => val.trim().length > 0,
                    message: 'Enrolled courses is required'
                },
                {
                    test: (val) => !isNaN(val) && parseInt(val) >= 0,
                    message: 'Must be a number greater than or equal to 0'
                }
            ]
        },
        Progress: {
            rules: [
                {
                    test: (val) => val.trim().length > 0,
                    message: 'Progress is required'
                },
                {
                    test: (val) => !isNaN(val) && parseInt(val) >= 0 && parseInt(val) <= 100,
                    message: 'Progress must be between 0 and 100'
                }
            ]
        },
        Tutor: {
            rules: [
                {
                    test: (val) => val.trim().length >= 2,
                    message: 'Tutor name must be at least 2 characters'
                }
            ]
        }
    };

    // Validate single field
    function validateField(fieldId) {
        const field = $('#' + fieldId);
        const value = field.val();
        const fieldType = fieldId.replace('add', '').replace('edit', '');
        const rules = ValidationRules[fieldType];
        const errorDiv = $('#' + fieldId + 'Error');

        if (!rules) return true;

        for (let rule of rules.rules) {
            if (!rule.test(value)) {
                field.removeClass('success-border').addClass('error-border');
                errorDiv.text(rule.message).addClass('show');
                return false;
            }
        }

        field.removeClass('error-border').addClass('success-border');
        errorDiv.removeClass('show');
        return true;
    }

    // Clear validation on field
    function clearValidation(fieldId) {
        $('#' + fieldId).removeClass('error-border success-border');
        $('#' + fieldId + 'Error').removeClass('show');
    }

    // Validate all fields in a form
    function validateForm(prefix) {
        let isValid = true;
        const fields = ['Name', 'Email', 'Phone', 'Course', 'Enrolled', 'Progress', 'Tutor'];

        fields.forEach(field => {
            if (!validateField(prefix + field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    // Attach real-time validation for both forms
    const prefixes = ['add', 'edit'];
    const fields = ['Name', 'Email', 'Phone', 'Course', 'Enrolled', 'Progress', 'Tutor'];

    prefixes.forEach(prefix => {
        fields.forEach(field => {
            const fieldId = prefix + field;
            $('#' + fieldId).on('input blur', function () {
                validateField(fieldId);
            });

            $('#' + fieldId).on('focus', function () {
                clearValidation(fieldId);
            });
        });
    });

    // ============== DATATABLE WITH FILTER ==============

    let currentFilter = '';

    // Custom FILTER for Active/Inactive
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!currentFilter) return true;

        let row = $("#datatable tbody tr").eq(dataIndex);
        let switchElem = row.find(".status-switch");
        let isChecked = switchElem.prop("checked");

        if (currentFilter === "active" && isChecked) return true;
        if (currentFilter === "inactive" && !isChecked) return true;

        return false;
    });

    // DataTable init
    if ($.fn.DataTable.isDataTable('#datatable')) {
        $('#datatable').DataTable().destroy();
    }

    let table = $('#datatable').DataTable({
        responsive: true,
        lengthChange: true,
        autoWidth: false,
        pageLength: 10,
        dom: "<'row mb-3'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            { extend: 'copy', className: 'btn btn-sm btn-outline-default me-1' },
            { extend: 'csv', className: 'btn btn-sm btn-outline-default me-1' },
            { extend: 'excel', className: 'btn btn-sm btn-outline-default me-1' },
            { extend: 'pdf', className: 'btn btn-sm btn-outline-default me-1' },
            { extend: 'print', className: 'btn btn-sm btn-outline-default me-1' },
            { extend: 'colvis', className: 'btn btn-sm btn-outline-default' }
        ],
        language: {
            paginate: {
                previous: "<i class='fa-solid fa-angle-left'></i>",
                next: "<i class='fa-solid fa-angle-right'></i>"
            }
        }
    });

    // Filter dropdown functionality
    $('.filter-option').on('click', function (e) {
        e.preventDefault();

        // Remove active class from all options
        $('.filter-option').removeClass('active');

        // Add active class to clicked option
        $(this).addClass('active');

        // Get filter value
        currentFilter = $(this).data('filter');

        // Redraw table
        table.draw();
    });

    // Redraw table when status switch is toggled + Send AJAX
    $(document).on('change', '.status-switch', function () {
        let $switch = $(this);
        let id = $switch.data('id');
        let isChecked = $switch.prop('checked') ? 1 : 0;

        $.ajax({
            url: 'ajax/update_assignment_status.php',
            type: 'POST',
            data: { id: id, status: isChecked },
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Updated',
                        text: 'Assignment visibility has been updated.',
                        timer: 1500,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    table.draw();
                } else {
                    $switch.prop('checked', !isChecked); // Revert
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: res.message || 'Something went wrong.'
                    });
                }
            },
            error: function () {
                $switch.prop('checked', !isChecked); // Revert
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Unable to connect to the server.'
                });
            }
        });
    });

    // ============== ADD ORDER FUNCTIONALITY ==============

    $('#addOrder').on('click', function () {
        if (!validateForm('add')) {
            alert('⚠️ Please fix all validation errors before adding.');
            return;
        }

        // Get values
        let name = $('#addName').val();
        let email = $('#addEmail').val();
        let phone = $('#addPhone').val();
        let course = $('#addCourse').val();
        let enrolled = $('#addEnrolled').val();
        let progress = $('#addProgress').val();
        let tutor = $('#addTutor').val();
        let isActive = $('#addStatus').prop('checked');

        // Generate random ID
        let newId = '#' + Math.floor(Math.random() * 90000 + 10000);

        // Add new row to table
        let newRow = table.row.add([
            newId,
            'IMAGE',
            name,
            email,
            phone,
            course,
            enrolled,
            progress + '%',
            tutor,
            `<div class="d-flex justify-content-center">
                <div class="form-check form-switch custom-switch">
                    <input class="form-check-input status-switch" type="checkbox" ${isActive ? 'checked' : ''}>
                    <label class="form-check-label"></label>
                </div>
            </div>`,
            `<a class="text-primary edit-btn" href="#" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fa-solid fa-pen me-2"></i></a>
             <a class="text-danger delete-btn" href="#"><i class="fa-solid fa-trash me-2"></i></a>`
        ]).draw().node();

        // Close modal
        $('#addModal').modal('hide');

        // Clear form
        $('#addForm')[0].reset();
        fields.forEach(field => clearValidation('add' + field));

        alert('✅ Order added successfully!');

        // AJAX call would go here
        // $.ajax({ ... });
    });

    // Clear add form when modal is closed
    $('#addModal').on('hidden.bs.modal', function () {
        $('#addForm')[0].reset();
        fields.forEach(field => clearValidation('add' + field));
    });

    // ============== EDIT FUNCTIONALITY ==============

    $(document).on('show.bs.modal', '#editModal', function (event) {
        let button = $(event.relatedTarget);
        $('#editAssignmentId').val(button.attr('data-id'));
        $('#editCourseId').val(button.attr('data-course'));
        $('#editAssignmentTitle').val(button.attr('data-title'));
        $('#editAssignmentDescription').val(button.attr('data-desc'));
    });


    // Delete button click
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        let row = $(this).closest('tr');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this! The file will also be deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'ajax/delete_assignment.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire(
                                'Deleted!',
                                'Assignment has been deleted.',
                                'success'
                            );
                            table.row(row).remove().draw();
                        } else {
                            Swal.fire('Error', res.message || 'Deletion failed', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Server connection failed', 'error');
                    }
                });
            }
        });
    });

    $('#saveAssignmentChanges').click(function () {
        let formData = new FormData($('#editAssignmentForm')[0]);

        $.ajax({
            url: 'ajax/update_assignment.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Assignment updated successfully!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: res.message || 'Update failed'
                    });
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Update failed: ' + xhr.responseText
                });
            }
        });
    });

});
