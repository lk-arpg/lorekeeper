{!! Form::open(['url' => $isMyo ? 'admin/myo/' . $character->id . '/description' : 'admin/character/' . $character->slug . '/description']) !!}
<div class="mb-3">
    {!! Form::label('Character Description') !!}
    {!! Form::textarea('description', $character->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="text-end">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

@include('js._tinymce_wysiwyg', ['tinymceSelector' => '.descriptioneditingparse .wysiwyg'])
