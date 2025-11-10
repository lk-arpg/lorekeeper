@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Submissions
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Submissions' => $user->url . '/submissions']) !!}

    <h1>
        {!! $user->displayName !!}'s Submissions
    </h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-2 col-3">
                {!! Form::select('prompt_ids[]', $prompts, Request::get('prompt_ids'), ['class' => 'form-control selectize col-12', 'multiple', 'placeholder' => 'Any Prompt']) !!}
            </div>
            <div class="form-group ml-1 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'newest',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $logs->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-12 col-md-2">
                    <div class="logs-table-cell">Prompt</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="logs-table-cell">Link</div>
                </div>
                <div class="col-6 col-md-5">
                    <div class="logs-table-cell">Date</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($logs as $log)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-12 col-md-2">
                            <div class="logs-table-cell">
                                {!! $log->prompt_id ? $log->prompt->displayName : '---' !!}
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="logs-table-cell">
                                <span class="ubt-texthide"><a href="{{ $log->url }}">{{ $log->url }}</a></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-5">
                            <div class="logs-table-cell">
                                {!! pretty_date($log->created_at) !!}
                            </div>
                        </div>
                        <div class="col-6 col-md-1">
                            <div class="logs-table-cell">
                                <a href="{{ $log->viewUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a>
                            </div>
                        </div>
                    </div>
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
