@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Currency Logs
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Bank' => $user->url . '/bank', 'Logs' => $user->url . '/currency-logs']) !!}

    <h1>
        {!! $user->displayName !!}'s Currency Logs
    </h1>

    {!! Form::open(['method' => 'GET']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-2">
            {!! Form::select('currency_category_ids[]', $currencyCategories, Request::get('currency_category_ids'), ['class' => 'form-control selectize', 'multiple' => 'multiple', 'placeholder' => 'Any Category']) !!}
        </div>
        <div class="form-group ml-3 mb-2">
            {!! Form::select('currency_ids[]', $currencies, Request::get('currency_ids'), ['class' => 'form-control selectize', 'multiple' => 'multiple', 'placeholder' => 'Any Currency']) !!}
        </div>
    </div>
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'newest' => 'Newest First',
                    'oldest' => 'Oldest First',
                ],
                Request::get('sort') ?: 'oldest',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::select('user_id', $users, Request::get('user_id'), ['class' => 'form-control', 'placeholder' => 'Any User']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}

    {!! $logs->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Sender</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Recipient</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Currency</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="logs-table-cell">Log</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Date</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($logs as $log)
                <div class="logs-table-row">
                    @include('user._currency_log_row', ['log' => $log, 'owner' => $user])
                </div>
            @endforeach
        </div>
    </div>
    {!! $logs->render() !!}
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();
        });
    </script>
@endsection
