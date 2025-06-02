@extends('admin.layout')

@section('admin-title')
    Gallery Queue
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Gallery Submissions Queue' => 'admin/gallery/submissions/pending']) !!}

    <h1>
        Gallery Submission Queue
    </h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/gallery/submissions/pending*') }} {{ set_active('admin/gallery/submissions') }}" href="{{ url('admin/gallery/submissions/pending') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/gallery/submissions/accepted*') }}" href="{{ url('admin/gallery/submissions/accepted') }}">Accepted</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/gallery/submissions/rejected*') }}" href="{{ url('admin/gallery/submissions/rejected') }}">Rejected</a>
        </li>
    </ul>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="mb-3 me-sm-3 mb-3">
            {!! Form::select('gallery_id', $galleries, Request::get('gallery_id'), ['class' => 'form-control']) !!}
        </div>
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

    {!! $submissions->render() !!}

    @foreach ($submissions as $key => $submission)
        @include('galleries._queue_submission', ['queue' => true])
    @endforeach

    {!! $submissions->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $submissions->total() }} result{{ $submissions->total() == 1 ? '' : 's' }} found.</div>
@endsection
