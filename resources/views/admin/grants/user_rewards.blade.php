@extends('admin.layout')

@section('admin-title')
    Grant Rewards
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Rewards' => 'admin/grants/rewards']) !!}

    <h1>Grant User Rewards</h1>

    {!! Form::open(['url' => 'admin/grants/rewards/user']) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('ids[]', 'Username(s)') !!} {!! add_help('You can select up to 10 users at once.') !!}
        {!! Form::select('ids[]', $users, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
    </div>

    @include('widgets._loot_select', [
        'loots' => [],
        'showRaffles' => true,
        'showLootTables' => true,
        'useCustomSelectize' => true,
    ])

    <hr />

    <div class="form-group">
        {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs and in the inventory description.') !!}
        {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
    </div>

    <h3>Additional Data</h3>

    <div class="form-group">
        {!! Form::label('notes', 'Notes (Optional)') !!} {!! add_help('Additional notes for the item. This will appear in the item\'s description, but not in the logs.') !!}
        {!! Form::text('notes', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('disallow_transfer', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('disallow_transfer', 'Account-bound', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is on, the recipient(s) will not be able to transfer this item to other users. Items that disallow transfers by default will still not be transferrable.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._loot_select_row', [
        'loots' => [],
        'showRaffles' => true,
        'showLootTables' => true,
        'useCustomSelectize' => true,
    ])

@endsection
@section('scripts')
    @include('js._loot_js', [
        'showRaffles' => true,
        'showLootTables' => true,
        'useCustomSelectize' => true,
    ])
    @parent
    <script>
        $(document).ready(function() {
            $('#usernameList').selectize({
                maxItems: 10
            });
        });
    </script>
@endsection
