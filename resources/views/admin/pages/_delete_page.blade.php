@if ($page)
    {!! Form::open(['url' => 'admin/pages/delete/' . $page->id]) !!}

    <p>You are about to delete the page <strong>{{ $page->name }}</strong>. This is not reversible. If you would like to preserve the content while preventing users from accessing the page, you can use the viewable setting instead to hide the page.
    </p>
    <p>Are you sure you want to delete <strong>{{ $page->name }}</strong>?</p>

    <div class="text-end">
        {!! Form::submit('Delete Page', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid page selected.
@endif
