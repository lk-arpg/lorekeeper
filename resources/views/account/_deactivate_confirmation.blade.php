@if (!Auth::user()->is_deactivated)
    <p>
        Are you sure you want to deactivate your account? All trades and submissions will be canceled and you will not be able to access the website when logged in.
    </p>
    <div class="text-end"><a href="#" class="btn btn-danger deactivate-confirm-button">Deactivate</a></div>

    <script>
        $('.deactivate-confirm-button').on('click', function(e) {
            e.preventDefault();
            $('#deactivateForm').submit();
        });
    </script>
@else
    <p>Your account is already deactivated.</p>
@endif
