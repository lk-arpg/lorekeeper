@if ($category)
    {!! Form::open(['url' => 'admin/data/character-categories/delete/' . $category->id]) !!}

    <p>You are about to delete the category <strong>{{ $category->name }}</strong>. This is not reversible. If characters in this category exist, you will not be able to delete this category.</p>
    <p>Are you sure you want to delete <strong>{{ $category->name }}</strong>?</p>

    <div class="text-end">
        {!! Form::submit('Delete Category', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid category selected.
@endif
