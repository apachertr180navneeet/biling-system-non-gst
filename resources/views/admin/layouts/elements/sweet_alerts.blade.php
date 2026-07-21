
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })
    function setFlash(status, message = '') {
        Toast.fire({
            icon: status,
            title: message
        })
    }
    
    $(document).on('change', '.toggle-status', function() {
        var checkbox = $(this);
        var url = checkbox.data('url');
        var isChecked = checkbox.prop('checked');
        
        // Revert UI first to wait for user confirmation
        checkbox.prop('checked', !isChecked);
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to change the status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Yes, change it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Set UI state to the new checked state
                checkbox.prop('checked', isChecked);
                
                // Perform the status change request
                $.post(url, { _token: '{{ csrf_token() }}' })
                 .done(function(resp) {
                     if (resp.success) {
                         setFlash('success', resp.message || 'Status updated successfully.');
                     } else {
                         setFlash('error', resp.message || 'Failed to update status.');
                         checkbox.prop('checked', !isChecked);
                     }
                 })
                 .fail(function() {
                     setFlash('error', 'Error updating status.');
                     checkbox.prop('checked', !isChecked);
                 });
            }
        });
    });
</script>
@if(Session::has('success'))
<script>
    Toast.fire({
        icon: 'success',
        title: @json(!empty(Session::get('message')) ? Session::get('message') : Session::get('success'))
    })
</script>
@endif
@if(Session::has('error'))
<script>
    Toast.fire({
        icon: 'error',
        title: @json(!empty(Session::get('message')) ? Session::get('message') : Session::get('error'))
    })
</script>
@endif
@if(Session::has('warning'))
<script>
    Toast.fire({
        icon: 'warning',
        title: @json(!empty(Session::get('message')) ? Session::get('message') : Session::get('warning'))
    })
</script>
@endif