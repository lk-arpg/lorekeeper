{!! Form::open(['url' => 'admin/character/image/' . $image->id . '/delete']) !!}
<p>This will delete the image from the character's profile. <strong>The image and its thumbnail will not be retrievable.</strong> </p>
<p>If you're looking for a less permanent option, you can set the image to not viewable and it will be hidden from public view.</p>
<p>Are you sure you want to do this?</p>

<div class="text-end">
    {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
</div>
{!! Form::close() !!}
