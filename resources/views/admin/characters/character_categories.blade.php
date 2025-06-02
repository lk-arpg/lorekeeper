@extends('admin.layout')

@section('admin-title')
    Character Categories
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Character Categories' => 'admin/data/character-categories']) !!}

    <h1>Character Categories</h1>

    <p>This is a list of character categories that will be used to classify characters. Creating character categories is entirely optional, but recommended for organisational purposes.</p>
    <p>The sorting order reflects the order in which the character categories will be displayed on the world pages.</p>

    <div class="text-end mb-3"><a class="btn btn-primary" href="{{ url('admin/data/character-categories/create') }}"><i class="fas fa-plus"></i> Create New Character Category</a></div>
    @if (!count($categories))
        <p>No character categories found.</p>
    @else
        <table class="table table-sm category-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Code</th>
                    <th>Sub Masterlist</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="sortable" class="sortable">
                @foreach ($categories as $category)
                    <tr class="sort-item" data-id="{{ $category->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle me-3" href="#"></a>
                            @if (!$category->is_visible)
                                <i class="fas fa-eye-slash me-1"></i>
                            @endif
                            {!! $category->displayName !!}
                        </td>
                        <td>
                            {{ $category->code }}
                        </td>
                        <td>
                            @if (isset($category->sublist->name))
                                {{ $category->sublist->name }}
                            @else
                                --
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ url('admin/data/character-categories/edit/' . $category->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/character-categories/sort']) !!}
            {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortable").sortable({
                characters: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
