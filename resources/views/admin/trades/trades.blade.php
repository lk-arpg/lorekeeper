@extends('admin.layout')

@section('admin-title')
    Trades
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Trade Queue' => 'admin/trades/incoming']) !!}

    <h1>
        Trades
    </h1>

    @include('admin.trades._header', ['tradeCount' => $tradeCount])

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
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
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $trades->render() !!}
    @foreach ($trades as $trade)
        @include('home.trades._trade', ['trade' => $trade, 'queueView' => true])
    @endforeach
    {!! $trades->render() !!}
@endsection


@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.trade-action-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/trades/act') }}/" + $(this).data('id') + "/" + $(this).data('action'), 'Process Trade');
            });
        });
    </script>
@endsection
