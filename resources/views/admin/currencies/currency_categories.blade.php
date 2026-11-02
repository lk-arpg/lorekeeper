@extends('admin.layout')

@section('admin-title')
    Currency Categories
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Currency Categories' => 'admin/data/currency-categories']) !!}

    <h1>Currency Categories</h1>

    <p>This is a list of currency categories that will be used to sort currencies. Creating currency categories is entirely optional, but recommended if you have a lot of currencies in the game.</p>
    <p>The sorting order reflects the order in which currency categories will be displayed in the bank, as well as on the world pages.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/currency-categories/create') }}"><i class="fas fa-plus"></i> Create New Currency Category</a></div>

    @if (!count($categories))
        <p>No currency categories found.</p>
    @else
        <table class="table table-sm category-table">
            <tbody id="sortable" class="sortable">
                @foreach ($categories as $category)
                    <tr class="sort-item" data-id="{{ $category->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            @if (!$category->is_visible)
                                <i class="fas fa-eye-slash mr-1"></i>
                            @endif
                            {!! $category->displayName !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/currency-categories/edit/' . $category->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/currency-categories/sort']) !!}
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
