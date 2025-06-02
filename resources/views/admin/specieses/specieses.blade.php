@extends('admin.layout')

@section('admin-title')
    Species
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Species' => 'admin/data/species']) !!}

    <h1>Species</h1>

    <div class="text-end mb-3"><a class="btn btn-primary" href="{{ url('admin/data/species/create') }}"><i class="fas fa-plus"></i> Create New Species</a></div>
    @if (!count($specieses))
        <p>No species found.</p>
    @else
        <table class="table table-sm species-table">
            <thead>
                <tr>
                    <th>Species</th>
                    <th>Sub Masterlist</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="sortable" class="sortable">
                @foreach ($specieses as $species)
                    <tr class="sort-item" data-id="{{ $species->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle me-3" href="#"></a>
                            @if (!$species->is_visible)
                                <i class="fas fa-eye-slash me-1"></i>
                            @endif
                            {!! $species->displayName !!}
                        </td>
                        <td>
                            @if (isset($species->sublist->name))
                                {{ $species->sublist->name }}
                            @else
                                --
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ url('admin/data/species/edit/' . $species->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/species/sort']) !!}
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
