@extends('admin.layout')

@section('admin-title')
    Character Transfers
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Character Transfer Queue' => 'admin/masterlist/transfers/incoming']) !!}

    <h1>
        Character Transfers
    </h1>

    @include('admin.masterlist._header', ['tradeCount' => $tradeCount, 'transferCount' => $transferCount])

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="d-flex align-items-center justify-content-end">
            <div class="mb-3 ms-3 mb-3">
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
            <div class="mb-3 ms-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $transfers->render() !!}
    @foreach ($transfers as $transfer)
        @include('admin.masterlist._transfer', ['transfer' => $transfer])
    @endforeach
    {!! $transfers->render() !!}
@endsection


@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.transfer-action-button').on('click', function(e) {
                e.preventDefault();
                console.log("{{ url('admin/masterlist/transfer/act') }}/" + $(this).data('id') + "/" + $(this).data('action'));
                loadModal("{{ url('admin/masterlist/transfer/act') }}/" + $(this).data('id') + "/" + $(this).data('action'), 'Process Transfer');
            });
        });
    </script>
@endsection
