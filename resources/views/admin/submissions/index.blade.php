@extends('admin.layout')

@section('admin-title')
    {{ $isClaims ? 'Claim' : 'Prompt' }} Queue
@endsection

@section('admin-content')
    @if ($isClaims)
        {!! breadcrumbs(['Admin Panel' => 'admin', 'Claim Queue' => 'admin/claims/pending']) !!}
    @else
        {!! breadcrumbs(['Admin Panel' => 'admin', 'Prompt Queue' => 'admin/submissions/pending']) !!}
    @endif

    <h1>
        {{ $isClaims ? 'Claim' : 'Prompt' }} Queue
    </h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/' . ($isClaims ? 'claims' : 'submissions') . '/pending*') }} {{ set_active('admin/' . ($isClaims ? 'claims' : 'submissions')) }}"
                href="{{ url('admin/' . ($isClaims ? 'claims' : 'submissions') . '/pending') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/' . ($isClaims ? 'claims' : 'submissions') . '/approved*') }}" href="{{ url('admin/' . ($isClaims ? 'claims' : 'submissions') . '/approved') }}">Approved</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/' . ($isClaims ? 'claims' : 'submissions') . '/rejected*') }}" href="{{ url('admin/' . ($isClaims ? 'claims' : 'submissions') . '/rejected') }}">Rejected</a>
        </li>
    </ul>

    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
    <div class="d-flex align-items-center justify-content-end">
        @if (!$isClaims)
            <div class="mb-3 ms-3 mb-3">
                {!! Form::select('prompt_category_id', $categories, Request::get('prompt_category_id'), ['class' => 'form-control']) !!}
            </div>
        @endif
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

    {!! $submissions->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                @if (!$isClaims)
                    <div class="col-12 col-md-2">
                        <div class="logs-table-cell">Prompt</div>
                    </div>
                @endif
                <div class="col-6 {{ !$isClaims ? 'col-md-2' : 'col-md-3' }}">
                    <div class="logs-table-cell">User</div>
                </div>
                <div class="col-6 {{ !$isClaims ? 'col-md-3' : 'col-md-4' }}">
                    <div class="logs-table-cell">Link</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell">Submitted</div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="logs-table-cell">Status</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($submissions as $submission)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        @if (!$isClaims)
                            <div class="col-12 col-md-2">
                                <div class="logs-table-cell">{!! $submission->prompt->displayName !!}</div>
                            </div>
                        @endif
                        <div class="col-6 {{ !$isClaims ? 'col-md-2' : 'col-md-3' }}">
                            <div class="logs-table-cell">{!! $submission->user->displayName !!}</div>
                        </div>
                        <div class="col-6 {{ !$isClaims ? 'col-md-3' : 'col-md-4' }}">
                            <div class="logs-table-cell">
                                <span class="ubt-texthide"><a href="{{ $submission->url }}">{{ $submission->url }}</a></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">{!! pretty_date($submission->created_at) !!}</div>
                        </div>
                        <div class="col-3 col-md-1">
                            <div class="logs-table-cell">
                                <span class="btn btn-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $submission->status }}</span>
                            </div>
                        </div>
                        <div class="col-3 col-md-1">
                            <div class="logs-table-cell"><a href="{{ $submission->adminUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $submissions->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $submissions->total() }} result{{ $submissions->total() == 1 ? '' : 's' }} found.</div>
@endsection
