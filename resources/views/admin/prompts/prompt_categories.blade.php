@extends('admin.layout')

@section('admin-title')
    Prompt Categories
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Prompt Categories' => 'admin/data/prompt-categories']) !!}

    <h1>Prompt Categories</h1>

    <p>This is a list of prompt categories that will be used to classify prompts on the prompts page. Creating prompt categories is entirely optional, but recommended if you need to sort prompts for mod work division, for example. The submission approval
        queue page can be sorted by prompt category.</p>
    <p>The sorting order reflects the order in which the prompt categories will be displayed on the prompts page.</p>

    <div class="text-end mb-3"><a class="btn btn-primary" href="{{ url('admin/data/prompt-categories/create') }}"><i class="fas fa-plus"></i> Create New Prompt Category</a></div>
    @if (!count($categories))
        <p>No prompt categories found.</p>
    @else
        <table class="table table-sm category-table">
            <tbody id="sortable" class="sortable">
                @foreach ($categories as $category)
                    <tr class="sort-prompt" data-id="{{ $category->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle me-3" href="#"></a>
                            {!! $category->displayName !!}
                        </td>
                        <td class="text-end">
                            <a href="{{ url('admin/data/prompt-categories/edit/' . $category->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/prompt-categories/sort']) !!}
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
                prompts: '.sort-prompt',
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
