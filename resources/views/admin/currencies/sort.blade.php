@extends('admin.layout')

@section('admin-title')
    Sort Currencies
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Currencies' => 'admin/data/currencies', 'Sort Currencies' => 'admin/data/currencies/sort']) !!}

    <h1>Sort Currencies</h1>

    <p>This is the order in which currencies will appear on a user or character's page. Both types are saved separately, so remember to hit save before editing the other one.</p>

    <h3>User Currencies</h3>

    @if (!count($userCurrencies))
        <p>No user-attached currencies found.</p>
    @else
        <table class="table table-sm currency-table">
            <tbody id="userSortable" class="sortable">
                @foreach ($userCurrencies as $currency)
                    <tr class="sort-item" data-id="{{ $currency->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle me-3" href="#"></a>
                            {{ $currency->name }} @if ($currency->abbreviation)
                                ({{ $currency->abbreviation }})
                            @endif {!! $currency->displayIcon !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/currencies/sort/user']) !!}
            {!! Form::hidden('sort', '', ['id' => 'userSortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
    @endif

    <h3>Character Currencies</h3>

    @if (!count($characterCurrencies))
        <p>No character-attached currencies found.</p>
    @else
        <table class="table table-sm currency-table">
            <tbody id="characterSortable" class="sortable">
                @foreach ($characterCurrencies as $currency)
                    <tr class="sort-item" data-id="{{ $currency->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle me-3" href="#"></a>
                            {{ $currency->name }} @if ($currency->abbreviation)
                                ({{ $currency->abbreviation }})
                            @endif {!! $currency->displayIcon !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div>
            {!! Form::open(['url' => 'admin/data/currencies/sort/character']) !!}
            {!! Form::hidden('sort', '', ['id' => 'characterSortableOrder']) !!}
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
            $("#userSortable").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#userSortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#userSortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#userSortable").disableSelection();
            $("#characterSortable").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#characterSortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#characterSortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#characterSortable").disableSelection();
        });
    </script>
@endsection
