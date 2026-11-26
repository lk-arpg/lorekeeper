@extends('admin.layout')

@section('admin-title')
    Item Search
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Item Search' => 'admin']) !!}

    <h1>Item Search</h1>

    <p>Select an item to search for all occurrences of it in user and character inventories. It will only display currently extant stacks (where the count is more than zero). If a stack is currently "held" in a trade, design update, or submission, this
        will be stated and all held locations will be linked.</p>

    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select('item_id', $items, Request::get('item_id'), ['class' => 'form-control selectize', 'placeholder' => 'Select an Item', 'style' => 'width: 25em; max-width: 100%;']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}

    @if ($item)
        <h3>{{ $item->name }}</h3>

        <p>There are currently {{ $userItems->pluck('count')->sum() + $characterItems->pluck('count')->sum() }} of this item owned by users and characters.</p>

        <ul class="nav nav-pills nav-fill">
            <li class="nav-item">
                <a class="nav-link active" id="userTab" data-toggle="tab" href="#users" role="tab">Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="characterTab" data-toggle="tab" href="#characters" role="tab">Characters</a>
            </li>
        </ul>
        <div class="tab-content my-2">
            <div class="tab-pane fade show active" id="users">
                @include('admin.grants._user_items', ['users' => $users])
            </div>
            <div class="tab-pane fade" id="characters">
                @include('admin.grants._character_items', ['characters' => $characters])
            </div>
        </div>
    @endif

    <script>
        $(document).ready(function() {
            $('.selectize').selectize();
        });
    </script>
@endsection
