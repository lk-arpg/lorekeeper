<p>You are about to delete your bookmark for {!! $bookmark->character->displayName !!}. Are you sure?</p>
<div class="text-end">
    {!! Form::open(['url' => 'account/bookmarks/delete/' . $bookmark->id]) !!}
    {!! Form::submit('Delete Bookmark', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
</div>
