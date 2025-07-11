@if ($page)
    {!! Form::open(['url' => 'admin/pages/regen/' . $page->id]) !!}

    <p>You are about to regenerate the page <strong>{{ $page->name }}</strong>. This will recreate the page contents based on the currently saved data, which is useful for regenerating mention links. If you've made changes on the page since
        clicking the edit button last, these changes will be lost.
    </p>
    <p>Are you sure you want to regenerate <strong>{{ $page->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Regenerate Page', ['class' => 'btn btn-secondary']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid page selected.
@endif
