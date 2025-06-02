{!! Form::open(['url' => 'admin/character/image/' . $image->id . '/active']) !!}
<p>This will set this image to be the character's thumbnail image and the first image a user sees when they view the character.</p>
<p>A non-visible image cannot be set as a character's active image. A non-valid image can, but this is not recommended. A character's active image cannot be deleted.</p>

<div class="text-end">
    {!! Form::submit('Set Active', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}
