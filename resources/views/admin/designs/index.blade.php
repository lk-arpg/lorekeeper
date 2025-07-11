@extends('admin.layout')

@section('admin-title')
    {{ $isMyo ? 'MYO Approval' : 'Design Update' }} Queue
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', ($isMyo ? 'MYO Approval' : 'Design Update') . ' Queue' => 'admin/designs/pending']) !!}

    <h1>
        {{ $isMyo ? 'MYO Approval' : 'Design Update' }} Queue
    </h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/' . ($isMyo ? 'myo-approvals' : 'design-approvals') . '/pending*') }}" href="{{ url('admin/' . ($isMyo ? 'myo-approvals' : 'design-approvals') . '/pending') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/' . ($isMyo ? 'myo-approvals' : 'design-approvals') . '/approved*') }}" href="{{ url('admin/' . ($isMyo ? 'myo-approvals' : 'design-approvals') . '/approved') }}">Approved</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/' . ($isMyo ? 'myo-approvals' : 'design-approvals') . '/rejected*') }}" href="{{ url('admin/' . ($isMyo ? 'myo-approvals' : 'design-approvals') . '/rejected') }}">Rejected</a>
        </li>
    </ul>

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

    {!! $requests->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-md-3">
                    <div class="logs-table-cell">{{ $isMyo ? 'MYO Slot' : 'Character' }}</div>
                </div>
                <div class="col-3 col-md-3">
                    <div class="logs-table-cell">User</div>
                </div>
                <div class="col-2 col-md-2">
                    <div class="logs-table-cell">Submitted</div>
                </div>
                @if (config('lorekeeper.extensions.design_update_voting'))
                    <div class="col-2 col-md-2">
                        <div class="logs-table-cell">Votes</div>
                    </div>
                @endif
                <div class="col-4 col-md-2">
                    <div class="logs-table-cell">Status</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($requests as $r)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-md-3">
                            <div class="logs-table-cell">{!! $r->character ? $r->character->displayName : 'Deleted Character [#' . $r->character_id . ']' !!}</div>
                        </div>
                        <div class="col-3 col-md-3">
                            <div class="logs-table-cell">{!! $r->user->displayName !!}</div>
                        </div>
                        <div class="col-2 col-md-2">
                            <div class="logs-table-cell">{!! $r->submitted_at ? pretty_date($r->submitted_at) : '---' !!}</div>
                        </div>
                        @if (config('lorekeeper.extensions.design_update_voting'))
                            <div class="col-2 col-md-2">
                                <div class="logs-table-cell">
                                    <strong>
                                        <span class="text-danger">{{ $r->getVoteData()['reject'] }}/{{ Settings::get('design_votes_needed') }}</span> :
                                        <span class="text-success">{{ $r->getVoteData()['approve'] }}/{{ Settings::get('design_votes_needed') }}</span>
                                    </strong>
                                </div>
                            </div>
                        @endif
                        <div class="col-4 col-md-1">
                            <div class="logs-table-cell"><span class="btn btn-{{ $r->status == 'Pending' ? 'secondary' : ($r->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $r->status }}</span></div>
                        </div>
                        <div class="col-4 col-md-1">
                            <div class="logs-table-cell"><a href="{{ $r->url }}" class="btn btn-primary btn-sm">Details</a></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $requests->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $requests->total() }} result{{ $requests->total() == 1 ? '' : 's' }} found.</div>
@endsection
