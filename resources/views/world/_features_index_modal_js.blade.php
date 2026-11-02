<script>
    $(document).ready(function() {
        $('.modal-image').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('world/traits/modal') }}/" + $(this).data('id'), 'Trait Detail');
        });
    })
</script>
