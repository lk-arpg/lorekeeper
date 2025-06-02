@extends('character.design.layout')

@section('design-title')
    Index
@endsection

@section('design-content')
    {!! breadcrumbs(['Design Approvals' => 'designs'] + ($status == 'draft' ? ['Drafts' => 'designs'] : ['Submissions' => 'designs/' . $status])) !!}

    @if ($status == 'draft')
        <h1>Design Approval Drafts</h1>

        <p>Design approval requests allow you to submit updates to your character's design, or submit a finished design for a MYO slot. To create a new approval request, go to the character or MYO slot's page and choose "Update Design" from the sidebar.
        </p>
    @else
        <h1>
            Design Approvals
        </h1>

        <p>This is a list of design approval requests you have submitted. These will be reviewed by staff, and approved if the design meets the requirements and guidelines. </p>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $status == 'pending' ? 'active' : '' }}" href="{{ url('designs/pending') }}">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'approved' ? 'active' : '' }}" href="{{ url('designs/approved') }}">Approved</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'rejected' ? 'active' : '' }}" href="{{ url('designs/rejected') }}">Rejected</a>
            </li>
        </ul>
    @endif

    @if (count($requests))
        {!! $requests->render() !!}
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Character/MYO Slot</th>
                    <th width="20%">Submitted</th>
                    @if ($status != 'draft')
                        <th width="20%">Status</th>
                    @endif
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $r)
                    <tr>
                        <td>{!! $r->character ? $r->character->displayName : 'Deleted Character [#' . $r->character_id . ']' !!}</td>
                        <td>{!! $r->submitted_at ? format_date($r->submitted_at) : '---' !!}</td>
                        @if ($status != 'draft')
                            <td>
                                <span class="badge badge-{{ $r->status == 'Pending' ? 'secondary' : ($r->status == 'Approved' ? 'success' : 'danger') }}">{{ $r->status }}</span>
                            </td>
                        @endif
                        <td class="text-end"><a href="{{ $r->url }}" class="btn btn-primary btn-sm">Details</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {!! $requests->render() !!}
    @else
        <p>No {{ 'requests' }} found.</p>
    @endif

@endsection
