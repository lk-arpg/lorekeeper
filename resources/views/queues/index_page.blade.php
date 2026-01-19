@extends('queues.layout')

@section('queues-title')
    {{ $category->name }}
@endsection

@section('content')
    {!! breadcrumbs(['Queues' => 'queues', $category->name => $category->url]) !!}

    <div class="card mb-3">
        <div class="card-body">
            @include('queues._category_entry')
        </div>
    </div>

    <hr class="w-75">

    @foreach ($queues as $queue)
        <div class="card mb-3">
            <div class="card-body">
                @include('queues._queue_entry', ['queue' => $queue])
            </div>
        </div>
    @endforeach
@endsection
