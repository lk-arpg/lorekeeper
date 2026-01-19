@extends('admin.layout')

@section('admin-title')
    Queue Categories
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Queue Categories' => 'admin/data/queue-categories']) !!}

    <h1>Queue Categories</h1>

    <p>This is a list of queue categories that will be used to classify queues on the queues page. Creating queue categories is entirely optional, but recommended if you need to sort queues for mod work division, for example. The submission approval
        queue page can be sorted by queue category.</p>
    <p>The sorting order reflects the order in which the queue categories will be displayed on the queues page.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/queue-categories/create') }}"><i class="fas fa-plus"></i> Create New Queue Category</a></div>
    @if (!count($categories))
        <p>No queue categories found.</p>
    @else
        <table class="table table-sm category-table">
            <tbody id="sortable" class="sortable">
                @foreach ($categories as $category)
                    <tr class="sort-queue" data-id="{{ $category->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            {!! $category->displayName !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/queue-categories/edit/' . $category->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/queue-categories/sort']) !!}
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
                queues: '.sort-queue',
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
