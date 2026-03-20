
$(document).ready(function () {

    // ================= USER INFO POPUP =================
    $(document).on('click', '.view-user-info', function (e) {
        e.preventDefault();

        let userData = $(this).data('user');

        // Update profile image with fallback
        let profilePic = userData.user_profile_pic || 'default-profile.png';
        $('#modalProfilePic')
            .off('error')
            .attr('src', '../admin/assets/images/users/' + profilePic)
            .on('error', function () {
                $(this).attr('src', 'assets/images/Student_Profile_Images/default-profile.png');
            });

        // Populate header section
        $('#modalUserName').text(userData.user_name || 'N/A');
        $('#modalUserEmail span').text(userData.user_email || 'N/A');

        // Status badge
        if (userData.user_status == 1) {
            $('#modalStatusBadge').removeClass('bg-danger').addClass('bg-success')
                .html('<i class="fa-solid fa-circle-check me-1"></i> Active');
        } else {
            $('#modalStatusBadge').removeClass('bg-success').addClass('bg-danger')
                .html('<i class="fa-solid fa-circle-xmark me-1"></i> Inactive');
        }

        // Personal information
        $('#infoContact').text(userData.detail_mobile || 'Not Provided');

        if (userData.dob) {
            let dob = new Date(userData.dob);
            $('#infoDOB').text(dob.toLocaleDateString('en-GB', {
                day: '2-digit', month: 'long', year: 'numeric'
            }));
        } else {
            $('#infoDOB').text('Not Provided');
        }

        $('#infoGender').text(userData.detail_gender || 'Not Specified');
        $('#infoLocation').text(userData.address || 'Not Provided');

        // ── Skills as badge pills ──
        let skills = (userData.skills || '').split(',').filter(Boolean);
        $('#infoSkills').html(
            skills.length
                ? skills.map(s =>
                    `<span class="badge rounded-pill bg-light border text-dark me-1 mb-1 px-3 py-2">${s.trim()}</span>`
                ).join('')
                : '<span class="text-muted">Not Provided</span>'
        );

        // ── Languages as badge pills ──
        let langs = (userData.lang_known || '').split(',').filter(Boolean);
        $('#infoLanguages').html(
            langs.length
                ? langs.map(l =>
                    `<span class="badge rounded-pill bg-light border text-dark me-1 mb-1 px-3 py-2">${l.trim()}</span>`
                ).join('')
                : '<span class="text-muted">Not Provided</span>'
        );

        // ── Enrollment status badge ──
        let enrollStatus = (userData.status || '').toLowerCase();
        let enrollBadgeMap = {
            enrolled: { color: 'bg-success', icon: 'fa-book-open', label: 'Enrolled' },
            completed: { color: 'bg-success', icon: 'fa-circle-check', label: 'Completed' },
            dropped: { color: 'bg-danger', icon: 'fa-circle-xmark', label: 'Dropped' },
            pending: { color: 'bg-warning text-dark', icon: 'fa-clock', label: 'Pending' }
        };
        let eb = enrollBadgeMap[enrollStatus] || { color: 'bg-secondary', icon: 'fa-question-circle', label: 'Unknown' };
        $('#infoEnrollStatus').html(
            `<span class="badge ${eb.color} px-3 py-2 rounded-pill">
                <i class="fa-solid ${eb.icon} me-1"></i>${eb.label}
             </span>`
        );

        // About me
        $('#infoAbout').text(userData.about_me || 'No information provided');

        // Progress bar with animation
        let progress = parseInt(userData.progress) || 0;
        let progressColor = progress >= 75 ? 'bg-success' : progress >= 40 ? 'bg-warning' : 'bg-danger';
        $('#infoProgress').text(progress + '%');
        $('#progressBar')
            .removeClass('bg-success bg-warning bg-danger')
            .addClass(progressColor)
            .css('width', '0%');
        setTimeout(() => $('#progressBar').css('width', progress + '%'), 300);

        $('#userProfileModal').modal('show');
    });


    // ============== DATATABLE WITH FILTER ==============

    let currentFilter = '';

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!currentFilter) return true;
        let api = new $.fn.dataTable.Api(settings);
        let node = api.row(dataIndex).node();
        if (!node) return true;
        let isChecked = $(node).find('.status-switch').prop('checked');
        if (currentFilter === 'active') return isChecked === true;
        if (currentFilter === 'inactive') return isChecked === false;
        return true;
    });

    if ($.fn.DataTable.isDataTable('#datatable')) {
        $('#datatable').DataTable().destroy();
    }

    let table = $('#datatable').DataTable({
        scrollX: true,
        scrollCollapse: true,
        responsive: false,
        lengthChange: false,
        autoWidth: false,
        pageLength: 10,
        dom:
            "<'row mb-3'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
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
            },
            // ── Empty / zero-results states ──
            zeroRecords: `
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-user-slash fa-2x mb-3 d-block" style="color:#adb5bd;"></i>
                    <p class="mb-0 fw-semibold">No students match this filter</p>
                    <small>Try selecting a different filter option</small>
                </div>`,
            emptyTable: `
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-users fa-2x mb-3 d-block" style="color:#adb5bd;"></i>
                    <p class="mb-0 fw-semibold">No students enrolled yet</p>
                    <small>Students will appear here once they enrol in your courses</small>
                </div>`
        }
    });

    // Filter dropdown
    $('.filter-option').on('click', function (e) {
        e.preventDefault();
        $('.filter-option').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        table.draw();
    });


    // ============== STATUS TOGGLE WITH SWEETALERT ==============

    $(document).on('change', '.status-switch', function () {
        let $switch = $(this);
        let userId = $switch.data('user-id');
        let newStatus = $switch.prop('checked') ? 1 : 0;
        let statusVerb = newStatus === 1 ? 'activate' : 'deactivate';
        let statusLabel = newStatus === 1 ? 'Activated' : 'Deactivated';
        let statusIcon = newStatus === 1 ? 'success' : 'warning';

        // Guard: missing user ID
        if (!userId) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'User ID not found.',
                confirmButtonColor: '#dc3545'
            });
            $switch.prop('checked', !$switch.prop('checked'));
            return;
        }

        // Revert switch visually until confirmed
        $switch.prop('checked', !$switch.prop('checked'));

        // ── Confirmation dialog ──
        Swal.fire({
            title: `${statusVerb.charAt(0).toUpperCase() + statusVerb.slice(1)} User?`,
            text: `Are you sure you want to ${statusVerb} this user?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: newStatus === 1 ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${statusVerb} it!`,
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return; // cancelled → leave switch reverted

            // Apply switch + show loading
            $switch.prop('checked', newStatus === 1).prop('disabled', true);

            Swal.fire({
                title: 'Updating...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: 'studentdetail.php',
                type: 'POST',
                data: {
                    action: 'toggle_status',
                    user_id: userId,
                    status: newStatus
                },
                dataType: 'json',
                success: function (response) {
                    $switch.prop('disabled', false);

                    if (response.success) {
                        // ── Toast (bottom-right, non-intrusive) ──
                        Swal.fire({
                            icon: statusIcon,
                            title: `User ${statusLabel}!`,
                            text: `Status updated successfully.`,
                            toast: true,
                            position: 'bottom-end',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                        table.draw();
                    } else {
                        $switch.prop('checked', !$switch.prop('checked'));
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed!',
                            text: response.message || 'Something went wrong.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                },
                error: function () {
                    $switch.prop('disabled', false).prop('checked', !$switch.prop('checked'));
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error!',
                        text: 'Unable to update status. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        });
    });

});

