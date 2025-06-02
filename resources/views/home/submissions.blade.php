@extends('home.layout')

@section('home-title')
    {{ $isClaims ? 'Claims' : 'Prompt Submissions' }}
@endsection

@section('home-content')
    @if ($isClaims)
        {!! breadcrumbs(['Claims' => 'claims']) !!}
    @else
        {!! breadcrumbs(['Prompt Submissions' => 'submissions']) !!}
    @endif

    <h1>
        {{ $isClaims ? 'Claims' : 'Prompt Submissions' }}
    </h1>

    <div class="text-end">
        @if (!$isClaims)
            <a href="{{ url('submissions/new') }}" class="btn btn-success">New Submission</a>
        @else
            <a href="{{ url('claims/new') }}" class="btn btn-success">New Claim</a>
        @endif
    </div>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'draft' ? 'active' : '' }}" href="{{ url($isClaims ? 'claims' : 'submissions') . '?type=draft' }}">Drafts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ !Request::get('type') || Request::get('type') == 'pending' ? 'active' : '' }}" href="{{ url($isClaims ? 'claims' : 'submissions') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'approved' ? 'active' : '' }}" href="{{ url($isClaims ? 'claims' : 'submissions') . '?type=approved' }}">Approved</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'rejected' ? 'active' : '' }}" href="{{ url($isClaims ? 'claims' : 'submissions') . '?type=rejected' }}">Rejected</a>
        </li>
    </ul>

    @if (count($submissions))
        {!! $submissions->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    @if (!$isClaims)
                        <div class="col-12 col-md-2 fw-bold">
                            <div class="logs-table-cell">Prompt</div>
                        </div>
                    @endif
                    <div class="col-6 {{ !$isClaims ? 'col-md-3' : 'col-md-4' }} fw-bold">
                        <div class="logs-table-cell">Link</div>
                    </div>
                    <div class="col-6 {{ !$isClaims ? 'col-md-5' : 'col-md-6' }} fw-bold">
                        <div class="logs-table-cell">Last Action</div>
                    </div>
                    <div class="col-12 col-md-1 fw-bold">
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
                            <div class="col-6 {{ !$isClaims ? 'col-md-3' : 'col-md-4' }}">
                                <div class="logs-table-cell">
                                    <span class="ubt-texthide"><a href="{{ $submission->url }}">{{ $submission->url }}</a></span>
                                </div>
                            </div>
                            <div class="col-6 {{ !$isClaims ? 'col-md-5' : 'col-md-6' }}">
                                <div class="logs-table-cell">{!! pretty_date($submission->updated_at) !!}</div>
                            </div>
                            <div class="col-6 col-md-1 text-end">
                                <div class="logs-table-cell">
                                    <span class="btn btn-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $submission->status }}</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-1">
                                <div class="logs-table-cell"><a href="{{ $submission->viewUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $submissions->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $submissions->total() }} result{{ $submissions->total() == 1 ? '' : 's' }} found.</div>
    @else
        <p>No {{ $isClaims ? 'claims' : 'submissions' }} found.</p>
    @endif

@endsection
