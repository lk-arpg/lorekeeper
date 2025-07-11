@extends('admin.layout')

@section('admin-title')
    Rarities
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Rarities' => 'admin/data/rarities']) !!}

    <h1>Rarities</h1>

    <p>This is a list of rarities that will be used across the site, primarily assigned to characters and traits. At least one rarity is required to create characters and traits.</p>
    <p>The sorting order reflects the order in which rarities will be displayed on the world pages (e.g. rarity-sorted traits will appear in this order), as well as in select dropdown fields. <strong>Please note that the highest rarity should be at the
            <u>top</u> of the list.</strong></p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/rarities/create') }}"><i class="fas fa-plus"></i> Create New Rarity</a></div>
    @if (!count($rarities))
        <p>No rarities found.</p>
    @else
        <table class="table table-sm rarity-table">
            <tbody id="sortable" class="sortable">
                @foreach ($rarities as $rarity)
                    <tr class="sort-item" data-id="{{ $rarity->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            {!! $rarity->displayName !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/rarities/edit/' . $rarity->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields') && config('lorekeeper.extensions.item_entry_expansion.loot_tables.enable'))
            <div class="alert alert-info mb-2">
                Note that changing the order of rarities may have unintended consequences on criteria loot tables.
            </div>
        @endif
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/rarities/sort']) !!}
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
