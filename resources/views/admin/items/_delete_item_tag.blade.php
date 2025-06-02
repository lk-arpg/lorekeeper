@if ($tag)
    {!! Form::open(['url' => 'admin/data/items/delete-tag/' . $item->id . '/' . $tag->tag]) !!}

    <p>You are about to delete the tag <strong>{{ $tag->getName() }}</strong> from {{ $item->name }}. This is not reversible. If you would like to preserve the tag data without deleting the tag, you may want to set the Active toggle to Off
        instead.</p>
    <p>Are you sure you want to delete <strong>{{ $tag->getName() }}</strong>?</p>

    <div class="text-end">
        {!! Form::submit('Delete Tag', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid tag selected.
@endif
