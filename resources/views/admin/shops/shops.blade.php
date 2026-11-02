@extends('admin.layout')

@section('admin-title')
    Shops
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Shops' => 'admin/data/shops']) !!}

    <h1>Shops</h1>

    <p>This is a list of shops that users can use currency to purchase from.</p>
    <p>The sorting order reflects the order in which the shops will be listed on the shop index.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/shops/create') }}"><i class="fas fa-plus"></i> Create New Shop</a></div>
    @if (!count($shops))
        <p>No item shops found.</p>
    @else
        <table class="table table-sm shop-table">
            <tbody id="sortable" class="sortable">
                @foreach ($shops as $shop)
                    <tr class="sort-item" data-id="{{ $shop->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            @if ($shop->is_staff)
                                <i class="fas fa-crown mr-1"></i>
                            @endif
                            {!! $shop->displayName !!}
                            @if ($shop->is_timed_shop)
                                <i class="fas fa-clock"></i>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/shops/edit/' . $shop->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/shops/sort']) !!}
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
