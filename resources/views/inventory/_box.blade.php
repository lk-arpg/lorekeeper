<li class="list-group-item">
    <a class="card-title h5 collapse-title" data-bs-toggle="collapse" href="#openBoxForm"> Open Box</a>
    <div id="openBoxForm" class="collapse">
        {!! Form::hidden('tag', $tag->tag) !!}
        <p>This action is not reversible. Are you sure you want to open this box?</p>
        <div class="text-end">
            {!! Form::button('Open', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'act', 'type' => 'submit']) !!}
        </div>
    </div>
</li>

<script>
    $('.btn').click(function() {
        if ($(this).hasClass('disabled')) {
            return false;
        }
        $(this).addClass('disabled');
        $(this).html('<i class="fas fa-spinner fa-spin"></i>');
    });
</script>
