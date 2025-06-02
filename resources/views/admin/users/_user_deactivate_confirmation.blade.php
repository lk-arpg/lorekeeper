@if (!$user->is_deactivated)
    <p>Are you sure you want to deactivate {!! $user->displayName !!}?</p>
    <div class="text-end"><a href="#" class="btn btn-danger deactivate-confirm-button">Deactivate</a></div>

    <script>
        $('.deactivate-confirm-button').on('click', function(e) {
            e.preventDefault();
            $('#deactivateForm').submit();
        });
    </script>
@else
    <p>This user is already deactivated.</p>
@endif
