@if ($news)
    {!! Form::open(['url' => 'admin/news/regen/' . $news->id]) !!}

    <p>You are about to regenerate the news post <strong>{{ $news->title }}</strong>. This will recreate the post contents based on the currently saved data, which is useful for regenerating mention links. If you've made changes on the news post
        since clicking the edit button last, these changes will be lost.
    </p>
    <p>Are you sure you want to regenerate <strong>{{ $news->title }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Regenerate Post', ['class' => 'btn btn-secondary']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid post selected.
@endif
