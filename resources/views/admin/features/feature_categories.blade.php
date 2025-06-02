@extends('admin.layout')

@section('admin-title')
    Trait Categories
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Trait Categories' => 'admin/data/trait-categories']) !!}

    <h1>Trait Categories</h1>

    <p>This is a list of trait categories that will be used to sort traits in the inventory. Creating trait categories is entirely optional, but recommended if you have a lot of traits in the game.</p>
    <p>The sorting order reflects the order in which the trait categories will be displayed in the inventory, as well as on the world pages.</p>

    <div class="text-end mb-3"><a class="btn btn-primary" href="{{ url('admin/data/trait-categories/create') }}"><i class="fas fa-plus"></i> Create New Trait Category</a></div>
    @if (!count($categories))
        <p>No trait categories found.</p>
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
                            <a href="{{ url('admin/data/trait-categories/edit/' . $category->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/trait-categories/sort']) !!}
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
