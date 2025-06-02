@extends('admin.layout')

@section('admin-title')
    Item Categories
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Item Categories' => 'admin/data/item-categories']) !!}

    <h1>Item Categories</h1>

    <p>This is a list of item categories that will be used to sort items in the inventory. Creating item categories is entirely optional, but recommended if you have a lot of items in the game.</p>
    <p>The sorting order reflects the order in which the item categories will be displayed in the inventory, as well as on the world pages.</p>

    <div class="text-end mb-3"><a class="btn btn-primary" href="{{ url('admin/data/item-categories/create') }}"><i class="fas fa-plus"></i> Create New Item Category</a></div>
    @if (!count($categories))
        <p>No item categories found.</p>
    @else
        <table class="table table-sm category-table">
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
                        <td class="text-end">
                            <a href="{{ url('admin/data/item-categories/edit/' . $category->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/item-categories/sort']) !!}
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
                items: '.sort-item',
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
