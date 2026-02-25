@extends('queues.layout')

@section('title')
    {{ $queue->name }}
@endsection

@section('content')
    {!! breadcrumbs(['Queues' => 'queues', 'All Queues' => 'queues/queues', $queue->name => 'queues/' . $queue->id]) !!}
    @include('queues._queue_entry', ['queue' => $queue, 'isPage' => true])
@endsection
