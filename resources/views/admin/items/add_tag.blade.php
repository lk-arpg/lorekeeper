@extends('admin.layout')

@section('admin-title')
    Add Item Tag
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Items' => 'admin/data/items', 'Edit Item' => 'admin/data/items/edit/' . $item->id, 'Add Item Tag' => 'admin/data/items/tag/' . $item->id]) !!}

    <h1>Add Item Tag</h1>

    <p>Select an item tag to add to the item. You cannot add duplicate tags to the same item (they are removed from the selection). You will be taken to the parameter editing page after adding the tag. </p>

    {!! Form::open(['url' => 'admin/data/items/tag/' . $item->id]) !!}

    <div class="mb-3">
        {!! Form::label('tag', 'Tag') !!}
        {!! Form::select('tag', [0 => 'Select a Tag'] + $tags, null, ['class' => 'form-control']) !!}
    </div>

    <div class="text-end">
        {!! Form::submit('Add Tag', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection
