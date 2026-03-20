$(document).ready(function () {
    // ============== SWEETALERT2 HELPER FUNCTIONS ==============

    // Success toast — fresh green icon
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#fff',
            iconColor: '#2ecc71',   /* ← fresh bright green */
            customClass: {
                popup: 'shadow-lg'
            }
        });
    }

    // Error toast — fresh red icon
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: '#fff',
            iconColor: '#e74c3c',   /* ← fresh vivid red */
            customClass: {
                popup: 'shadow-lg'
            }
        });
    }

    // Warning toast — fresh amber icon
    function showWarning(message) {
        Swal.fire({
            icon: 'warning',
            title: 'Warning!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: '#fff',
            iconColor: '#f39c12',   /* ← fresh amber */
            customClass: {
                popup: 'shadow-lg'
            }
        });
    }

    // Confirm dialog — fresh green confirm, fresh red cancel, fresh grey dismiss
    function showConfirm(title, text, confirmBtnText = 'Yes, proceed!', icon = 'question') {
        return Swal.fire({
            icon: icon,
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: confirmBtnText,
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#2ecc71',   /* ← fresh bright green */
            cancelButtonColor: '#e74c3c',   /* ← fresh vivid red   */
            reverseButtons: true,
            focusCancel: true
        });
    }

    // ================= USER INFO POPUP =================
    $(document).on('click', '.user-profile', function () {

        let userData = $(this).data();
        let profilePic = userData.user_profile_pic || 'default-profile.png';

        $('#modalProfilePic')
            .attr('src', 'assets/images/Tutor_Images/' + profilePic)
            .off('error')
            .on('error', function () {
                this.src = 'assets/images/Tutor_Images/default-profile.png';
            });

        $('#profileName').text(userData.name);
        $('#profileLocation').text(userData.location);
        $('#infoEmail').text(userData.email);
        $('#infoPhone').text(userData.phone);
        $('#infoLanguage').text(userData.language);
        $('#infoDegree').text(userData.degree);

        // Courses
        $('#infoCourses').empty();
        let courses = userData.courses || [];
        if (courses.length === 0 || (courses.length === 1 && courses[0] === '')) {
            $('#infoCourses').html('<span class="text-muted fst-italic">No expertise listed</span>');
        } else {
            courses.forEach(course => {
                if (course && course.trim() !== '') {
                    $('#infoCourses').append(`<li>${course}</li>`);
                }
            });
        }

        // Achievements
        $('#infoCertificates').empty();
        let achievements = userData.achievements || [];
        if (achievements.length === 0 || (achievements.length === 1 && achievements[0] === '')) {
            $('#infoCertificates').html('<span class="text-muted fst-italic">No achievements listed</span>');
        } else {
            achievements.forEach(achievement => {
                if (achievement && achievement.trim() !== '') {
                    $('#infoCertificates').append(`<li>${achievement}</li>`);
                }
            });
        }

        // Certifications
        $('#certificateList').empty();
        let certifications = userData.certifications || [];

        if (certifications.length === 0) {
            $('#certificateList').html(`
				<div class="col-12 text-center py-4">
					<i class="fa-solid fa-certificate fa-3x text-muted mb-3 opacity-25"></i>
					<p class="text-muted fst-italic mb-0">No certifications available</p>
				</div>
			`);
        } else {
            certifications.forEach(cert => {
                $('#certificateList').append(`
					<div class="col-md-6">
						<div class="info-item soft">
							<div class="d-flex align-items-start gap-2">
								<i class="fa-solid fa-award text-success mt-1"></i>
								<div class="flex-grow-1">
									<div class="fw-bold text-dark mb-1">${cert[0]}</div>
									<small class="text-muted">
										<i class="fa-solid fa-building me-1"></i>${cert[1]}
									</small>
								</div>
							</div>
						</div>
					</div>
				`);
            });
        }

        $('#userProfileModal').modal('show');
    });

    // ============== DYNAMIC VALIDATION SYSTEM ==============

    const ValidationRules = {
        Name: {
            rules: [
                { test: (val) => val.trim().length >= 2, message: 'Name must be at least 2 characters long' },
                { test: (val) => /^[a-zA-Z\s]+$/.test(val), message: 'Name should contain only letters' }
            ]
        },
        Email: {
            rules: [
                { test: (val) => val.trim().length > 0, message: 'Email is required' },
                { test: (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val), message: 'Please enter a valid email address' }
            ]
        },
        Phone: {
            rules: [
                { test: (val) => val.trim().length > 0, message: 'Phone number is required' },
                { test: (val) => /^\d{10}$/.test(val.replace(/\s|-/g, '')), message: 'Phone must be exactly 10 digits' }
            ]
        },
        Course: {
            rules: [
                { test: (val) => val.trim().length >= 2, message: 'Course name must be at least 2 characters' }
            ]
        },
        Enrolled: {
            rules: [
                { test: (val) => val.trim().length > 0, message: 'Enrolled courses is required' },
                { test: (val) => !isNaN(val) && parseInt(val) >= 0, message: 'Must be a number greater than or equal to 0' }
            ]
        },
        Progress: {
            rules: [
                { test: (val) => val.trim().length > 0, message: 'Progress is required' },
                { test: (val) => !isNaN(val) && parseInt(val) >= 0 && parseInt(val) <= 100, message: 'Progress must be between 0 and 100' }
            ]
        },
        Tutor: {
            rules: [
                { test: (val) => val.trim().length >= 2, message: 'Tutor name must be at least 2 characters' }
            ]
        }
    };

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

    function clearValidation(fieldId) {
        $('#' + fieldId).removeClass('error-border success-border');
        $('#' + fieldId + 'Error').removeClass('show');
    }

    function validateForm(prefix) {
        let isValid = true;
        const fields = ['Name', 'Email', 'Phone', 'Course', 'Enrolled', 'Progress', 'Tutor'];
        fields.forEach(field => {
            if (!validateField(prefix + field)) isValid = false;
        });
        return isValid;
    }

    const prefixes = ['add', 'edit'];
    const fields = ['Name', 'Email', 'Phone', 'Course', 'Enrolled', 'Progress', 'Tutor'];

    prefixes.forEach(prefix => {
        fields.forEach(field => {
            const fieldId = prefix + field;
            $('#' + fieldId).on('input blur', function () { validateField(fieldId); });
            $('#' + fieldId).on('focus', function () { clearValidation(fieldId); });
        });
    });

    // ============== DATATABLE WITH FILTER ==============

    let currentFilter = '';

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, rowData, counter) {
        if (!currentFilter) return true;
        let api = new $.fn.dataTable.Api(settings);
        let node = api.row(dataIndex).node();
        if (!node) return true;
        let $switch = $(node).find('.status-switch');
        let isChecked = $switch.prop('checked');
        if (currentFilter === "active") return isChecked === true;
        if (currentFilter === "inactive") return isChecked === false;
        return true;
    });

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
        },
        columnDefs: [
            { orderable: false, targets: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] } // Disable ordering on all columns except Sr No
        ],
        order: [[0, 'asc']] // Order by Sr No ascending
    });

    $('.filter-option').on('click', function (e) {
        e.preventDefault();
        let filterValue = $(this).data('filter');
        $('.filter-option').removeClass('active');
        $(this).addClass('active');
        currentFilter = filterValue;
        table.draw();
    });

    // ============== STATUS TOGGLE — WITH SWEETALERT2 ==============

    $(document).on('change', '.status-switch', function () {
        let $switch = $(this);
        let courseId = $switch.data('course-id');
        let newStatus = $switch.prop('checked') ? 1 : 0;
        let statusLabel = newStatus === 1 ? 'activate' : 'deactivate';
        let statusDone = newStatus === 1 ? 'activated' : 'deactivated';

        if (!courseId) {
            showError('Course ID not found. Please refresh and try again.');
            $switch.prop('checked', !$switch.prop('checked'));
            return;
        }

        // Immediately revert toggle while user confirms
        $switch.prop('checked', !$switch.prop('checked'));

        showConfirm(
            `${newStatus === 1 ? 'Activate' : 'Deactivate'} Course?`,
            `Are you sure you want to ${statusLabel} this course?`,
            `Yes, ${statusLabel} it!`,
            newStatus === 1 ? 'question' : 'warning'
        ).then((result) => {
            if (result.isConfirmed) {
                // Restore the intended value and disable while saving
                $switch.prop('checked', newStatus === 1).prop('disabled', true);

                $.ajax({
                    url: 'coursedetail.php',
                    type: 'POST',
                    data: {
                        action: 'toggle_status',
                        course_id: courseId,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function (response) {
                        $switch.prop('disabled', false);
                        if (response.success) {
                            showSuccess(`Course ${statusDone} successfully!`);
                            table.draw();
                        } else {
                            // Revert on failure
                            $switch.prop('checked', !$switch.prop('checked'));
                            showError('Failed to update status: ' + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        $switch.prop('checked', !$switch.prop('checked')).prop('disabled', false);
                        showError('Server error: Unable to update status. Please try again.');
                    }
                });
            }
            // If cancelled, switch stays reverted (already done above)
        });
    });


    // ============== DELETE COURSE — WITH SWEETALERT2 ==============

    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        let courseId = $(this).data('course-id');
        let $row = $(this).closest('tr');

        showConfirm(
            'Delete Course?',
            'This action cannot be undone. The course and all related data will be permanently deleted.',
            'Yes, delete it!',
            'warning'
        ).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while the course is being deleted.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'coursedetail.php',
                    type: 'GET',
                    data: {
                        id: courseId,
                        confirm_delete: 1
                    },
                    success: function (response) {
                        // Remove row from DataTable
                        table.row($row).remove().draw();
                        showSuccess('Course deleted successfully!');
                    },
                    error: function () {
                        showError('Failed to delete course. Please try again.');
                    }
                });
            }
        });
    });


    // ============== EDIT COURSE MODAL — POPULATE ==============

    $(document).on('click', '.edit-btn', function (e) {
        e.preventDefault();
        $('#editCourseId').val($(this).data('id'));
        $('#editCourseTitle').val($(this).data('title'));
        $('#editCourseDescription').val($(this).data('desc'));
        $('#editCourseLevel').val($(this).data('level'));
        $('#editTotalLesson').val($(this).data('lesson'));
        $('#editPrice').val($(this).data('price'));
    });

    // ============== SAVE COURSE UPDATE — WITH SWEETALERT2 ==============

    $('#saveChanges').on('click', function (e) {
        e.preventDefault();

        const courseData = {
            course_id: $('#editCourseId').val(),
            title: $('#editCourseTitle').val().trim(),
            description: $('#editCourseDescription').val().trim(),
            level: $('#editCourseLevel').val(),
            lesson: $('#editTotalLesson').val(),
            price: $('#editPrice').val()
        };

        // Basic front-end guard
        if (!courseData.title || !courseData.description) {
            showWarning('Please fill in all required fields before saving.');
            return;
        }

        // Show loading inside modal button
        let $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');

        $.ajax({
            url: 'coursedetail.php',
            type: 'POST',
            data: courseData,
            success: function (res) {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-save me-2"></i>Save Changes');

                if (res.trim() === 'success') {
                    $('#editModal').modal('hide');
                    showSuccess('Course updated successfully!');
                    // Delay reload so toast is visible
                    setTimeout(() => { location.reload(); }, 1800);
                } else {
                    showError('Update failed: ' + res);
                }
            },
            error: function (xhr, status, error) {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-save me-2"></i>Save Changes');
                showError('Server error: ' + (xhr.responseText || 'Please try again.'));
            }
        });
    });

    // ============== ADD ORDER FUNCTIONALITY ==============

    $('#addOrder').on('click', function () {
        if (!validateForm('add')) {
            showWarning('Please fix all validation errors before adding.');
            return;
        }

        let name = $('#addName').val();
        let email = $('#addEmail').val();
        let phone = $('#addPhone').val();
        let course = $('#addCourse').val();
        let enrolled = $('#addEnrolled').val();
        let progress = $('#addProgress').val();
        let tutor = $('#addTutor').val();
        let isActive = $('#addStatus').prop('checked');

        let newId = '#' + Math.floor(Math.random() * 90000 + 10000);

        let newRow = table.row.add([
            newId, 'IMAGE', name, email, phone, course, enrolled,
            progress + '%', tutor,
            `<div class="d-flex justify-content-center">
				<div class="form-check form-switch custom-switch">
					<input class="form-check-input status-switch" type="checkbox" ${isActive ? 'checked' : ''}>
					<label class="form-check-label"></label>
				</div>
			</div>`,
            `<a class="text-primary edit-btn" href="#" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fa-solid fa-pen me-2"></i></a>
			 <a class="text-danger delete-btn" href="#"><i class="fa-solid fa-trash me-2"></i></a>`
        ]).draw().node();

        $('#addModal').modal('hide');
        $('#addForm')[0].reset();
        fields.forEach(field => clearValidation('add' + field));

        showSuccess('Course added successfully!');
    });

    $('#addModal').on('hidden.bs.modal', function () {
        $('#addForm')[0].reset();
        fields.forEach(field => clearValidation('add' + field));
    });
});