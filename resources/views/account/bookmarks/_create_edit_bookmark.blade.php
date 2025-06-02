<p>
    All information entered into a bookmark is strictly private - the owner of the bookmarked character will not be notified, and will not know who/how many users have bookmarked their character.
</p>
{!! Form::open(['url' => $bookmark->id ? 'account/bookmarks/edit/' . $bookmark->id : 'account/bookmarks/create']) !!}
{!! Form::hidden('character_id', Request::get('character_id')) !!}
<div class="mb-3">
    {!! Form::label('notify', 'Notify me when...') !!} {!! add_help('This will notify you whenever the respective change occurs, and is entirely optional.') !!}
    <div class="form-check">
        {!! Form::checkbox('notify_on_trade_status', 1, $bookmark->notify_on_trade_status, ['class' => 'form-check-input', 'id' => 'notifyTrade']) !!}
        {!! Form::label('notifyTrade', 'Open For Trades status changes', ['class' => 'form-check-label']) !!}
    </div>
    <div class="form-check">
        {!! Form::checkbox('notify_on_gift_art_status', 1, $bookmark->notify_on_gift_art_status, ['class' => 'form-check-input', 'id' => 'notifyGiftArt']) !!}
        {!! Form::label('notifyGiftArt', 'Gift Art Allowed status changes', ['class' => 'form-check-label']) !!}
    </div>
    <div class="form-check">
        {!! Form::checkbox('notify_on_gift_writing_status', 1, $bookmark->notify_on_gift_writing_status, ['class' => 'form-check-input', 'id' => 'notifyGiftArt']) !!}
        {!! Form::label('notifyGiftWriting', 'Gift Writing Allowed status changes', ['class' => 'form-check-label']) !!}
    </div>
    <div class="form-check">
        {!! Form::checkbox('notify_on_transfer', 1, $bookmark->notify_on_transfer, ['class' => 'form-check-input', 'id' => 'notifyTransfer']) !!}
        {!! Form::label('notifyTransfer', 'Character\'s owner changes', ['class' => 'form-check-label']) !!}
    </div>
    <div class="form-check">
        {!! Form::checkbox('notify_on_image', 1, $bookmark->notify_on_image, ['class' => 'form-check-input', 'id' => 'notifyImage']) !!}
        {!! Form::label('notifyImage', 'A new image is uploaded', ['class' => 'form-check-label']) !!}
    </div>
</div>
<div class="mb-3">
    {!! Form::label('comment', 'Comment (Optional)') !!} {!! add_help('HTML will not be rendered. Newlines will be honoured.') !!}
    {!! Form::textarea('comment', $bookmark->comment, ['class' => 'form-control', 'maxLength' => 500]) !!}
</div>
<div class="text-end">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}
