@if (!$user->is_banned)
    <p>Are you sure you want to ban {!! $user->displayName !!}?</p>
    <div class="text-end"><a href="#" class="btn btn-danger ban-confirm-button">Ban</a></div>

    <script>
        $('.ban-confirm-button').on('click', function(e) {
            e.preventDefault();
            $('#banForm').submit();
        });
    </script>
@else
    <p>This user is already banned.</p>
@endif
