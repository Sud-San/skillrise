
$(document).ready(function () {

    // ══════════════════════════════════════
    // DATATABLE — no scrollX, auto layout
    // ══════════════════════════════════════
    let currentFilter = '';

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        return true;
    });

    if ($.fn.DataTable.isDataTable('#datatable')) {
        $('#datatable').DataTable().destroy();
    }

    let table = $('#datatable').DataTable({
        // ── KEY FIXES ──
        scrollX: true,      // no horizontal scroll
        responsive: false,
        autoWidth: true,       // let DataTables calculate column widths naturally
        lengthChange: false,
        pageLength: 10,

        // Column definitions — control which columns are orderable/searchable
        columnDefs: [
            { targets: [2, 8], orderable: false },    // file icon, actions
            { targets: [0], width: '50px' },      // Sr No
            { targets: [1], width: '45px' },      // ID
            { targets: [2], width: '120px' },      // File URL
            { targets: [5], width: '60px' },      // Type
            { targets: [6], width: '70px' },      // Size
            { targets: [7], width: '90px' },      // Uploaded
            { targets: [8], width: '120px' }       // Actions
        ],

        dom:
            "<'row mb-3'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

        // ── Export buttons — using btn-outline-default which your theme provides ──
        buttons: [
            { extend: 'copy', text: 'Copy', className: 'btn btn-sm btn-outline-success' },
            { extend: 'csv', text: 'CSV', className: 'btn btn-sm btn-outline-success' },
            { extend: 'excel', text: 'Excel', className: 'btn btn-sm btn-outline-success' },
            { extend: 'pdf', text: 'PDF', className: 'btn btn-sm btn-outline-success' },
            { extend: 'print', text: 'Print', className: 'btn btn-sm btn-outline-success' },
            { extend: 'colvis', text: 'Column visibility', className: 'btn btn-sm btn-outline-success' }
        ],

        language: {
            paginate: {
                previous: "<i class='fa-solid fa-angle-left'></i>",
                next: "<i class='fa-solid fa-angle-right'></i>"
            },
            zeroRecords: `<div class="text-center py-4 text-muted">
                            <i class="fa-solid fa-file-circle-xmark fa-2x mb-2 d-block" style="color:#adb5bd;"></i>
                            No notes match this filter
                          </div>`,
            emptyTable: `<div class="text-center py-4 text-muted">
                            <i class="fa-solid fa-file-circle-plus fa-2x mb-2 d-block" style="color:#adb5bd;"></i>
                            No notes uploaded yet
                         </div>`
        }
    });

    // Filter dropdown simplified
    $('.filter-option').on('click', function (e) {
        e.preventDefault();
        $('.filter-option').removeClass('active');
        $(this).addClass('active');
        table.draw();
    });


    // ══════════════════════════════════════
    // STATUS TOGGLE
    // ══════════════════════════════════════
    // STATUS TOGGLE REMOVED


    // ══════════════════════════════════════
    // OPEN EDIT MODAL
    // ══════════════════════════════════════
    $(document).on('click', '.edit-note', function () {
        let $b = $(this);
        $('#editNoteId').val($b.data('note-id'));
        $('#editCourseId').val($b.data('course-id'));
        $('#editDescription').val($b.data('description'));
        $('#editCurrentFile').text($b.data('file-url'));
        $('#editNoteFile').val('');
        $('#editProgress').hide();
        $('#editProgressBar').css('width', '0%');
        new bootstrap.Modal(document.getElementById('editNoteModal')).show();
    });


    // ══════════════════════════════════════
    // SUBMIT EDIT
    // ══════════════════════════════════════
    $('#submitEdit').on('click', function () {
        let $btn = $(this);
        if (!$('#editNoteForm')[0].reportValidity()) return;

        let fd = new FormData($('#editNoteForm')[0]);
        $('#editProgress').show();
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>Saving…');

        $.ajax({
            url: 'notesdetail.php',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            xhr: function () {
                let xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable)
                        $('#editProgressBar').css('width', Math.round(e.loaded / e.total * 100) + '%');
                });
                return xhr;
            },
            success: function (res) {
                $('#editProgress').hide();
                $btn.prop('disabled', false).html('<i class="fa-solid fa-save me-1"></i>Save Changes');
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editNoteModal')).hide();
                    Swal.fire({
                        icon: 'success', title: 'Updated!', text: 'Note updated successfully.',
                        toast: true, position: 'bottom-end',
                        showConfirmButton: false, timer: 2500, timerProgressBar: true
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Update Failed!', text: res.message || 'Something went wrong.', confirmButtonColor: '#dc3545' });
                }
            },
            error: function () {
                $('#editProgress').hide();
                $btn.prop('disabled', false).html('<i class="fa-solid fa-save me-1"></i>Save Changes');
                Swal.fire({ icon: 'error', title: 'Server Error!', text: 'Unable to save. Please try again.', confirmButtonColor: '#dc3545' });
            }
        });
    });


    // ══════════════════════════════════════
    // DELETE NOTE
    // ══════════════════════════════════════
    $(document).on('click', '.delete-note', function () {
        let noteId = $(this).data('note-id');
        let fileName = $(this).data('file');

        Swal.fire({
            title: 'Delete Note?',
            html: `This will permanently delete <strong>${fileName}</strong>.<br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            reverseButtons: true
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Deleting…', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: 'notesdetail.php',
                type: 'POST',
                data: { action: 'delete_note', note_id: noteId },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        table.row($(`tr[data-note-id="${noteId}"]`)).remove().draw();
                        Swal.fire({
                            icon: 'success', title: 'Deleted!', text: 'The note has been removed.',
                            toast: true, position: 'bottom-end',
                            showConfirmButton: false, timer: 2500, timerProgressBar: true
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Delete Failed!', text: res.message || 'Something went wrong.', confirmButtonColor: '#dc3545' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Server Error!', text: 'Unable to delete. Please try again.', confirmButtonColor: '#dc3545' });
                }
            });
        });
    });


    // VIEW / DOWNLOAD COUNT HANDLERS REMOVED

});