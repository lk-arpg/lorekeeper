@extends('admin.layout')

@section('admin-title')
    Sub Masterlists
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Sub Masterlists' => 'admin/data/sublists']) !!}

    <h1>Sub Masterlists</h1>

    <p>Sub masterlists are additional masterlists which can be separate or alternative to the main masterlist. This can be used to divide a masterlist up between species, player versus non-player-character, characters vs pets/mounts, etc.</p>
    <p>Both categories and species can be assigned to sublists, but each can only be assigned to ONE sublist.</p>

    <div class="text-end mb-3"><a class="btn btn-primary" href="{{ url('admin/data/sublists/create') }}"><i class="fas fa-plus"></i> Create New Sub Masterlist</a></div>

    @if (!count($sublists))
        <p>No sub masterlists found.</p>
    @else
        <table class="table table-sm category-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Show on Main</th>
                    <th>Key</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="sortable" class="sortable">
                @foreach ($sublists as $sublist)
                    <tr class="sort-item" data-id="{{ $sublist->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle me-3" href="#"></a>
                            {{ $sublist->name }}
                        </td>
                        <td>{!! $sublist->show_main ? '<i class="text-success fas fa-check"></i>' : '' !!}</td>
                        <td>{!! $sublist->key !!}</td>
                        <td class="text-end">
                            <a href="{{ url('admin/data/sublists/edit/' . $sublist->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/sublists/sort']) !!}
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
