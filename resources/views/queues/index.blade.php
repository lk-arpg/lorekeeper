@extends('queues.layout')

@section('queues-title')
    Home
@endsection

@section('content')
    {!! breadcrumbs(['Queues' => 'queues']) !!}

    <h1>Queues</h1>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ asset('images/inventory.png') }}" alt="Queues" />
                    <h5 class="card-title">Queues</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="{{ url('queues/queue-categories') }}">Queues Categories</a></li>
                    <li class="list-group-item"><a href="{{ url('queues/queues') }}">All Queues</a></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
