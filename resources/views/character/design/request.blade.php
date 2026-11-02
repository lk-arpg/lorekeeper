@extends('character.design.layout')

@section('design-title')
    Request (#{{ $request->id }})
@endsection

@section('design-content')
    {!! breadcrumbs(['Design Approvals' => 'designs', 'Request (#' . $request->id . ')' => 'designs/' . $request->id]) !!}

    @include('character.design._header', ['request' => $request])

    @if ($request->status == 'Draft')
        <p>
            This request has not been submitted to the approval queue yet.
            @if ($request->user_id == Auth::user()->id)
                Staff members are able to view this page when provided with a direct link. Click on any of the tabs to edit the section.
            @else
                As a staff member with the ability to edit the masterlist, you can view the details of the request by clicking the tabs.
            @endif
        </p>
        @if ($request->user_id == Auth::user()->id)
            @if ($request->isComplete)
                <div class="text-right">
                    <button class="btn btn-outline-danger delete-button">Delete Request</button>
                    <a href="#" class="btn btn-outline-primary submit-button">Submit Request</a>
                </div>
            @else
                <p class="text-danger">Not all sections have been completed yet. Please visit the necessary tab(s) and click Save to update them, even if no modifications to the information are needed.</p>
                <div class="text-right">
                    <button class="btn btn-outline-danger delete-button">Delete Request</button>
                    <button class="btn btn-outline-primary" disabled>Submit Request</button>
                </div>
            @endif
        @endif
    @elseif($request->status == 'Pending')
        <p>
            This request is in the approval queue.
            @if (!Auth::user()->hasPower('manage_characters'))
                Please wait for it to be processed{{ $canCancel ? ' or cancel it below.' : '.' }}
                @if ($canCancel)
                    <div class="card mb-3">
                        <div class="card-body">
                            <a href="#" class="btn btn-outline-secondary cancel-button btn-sm float-right" data-action="cancel">Cancel</a>
                            <strong class="text-secondary">Cancelling</strong> the request returns it to its draft status, allowing you to make further edits.
                        </div>
                    </div>
                @endif
            @else
                As a staff member with the ability to edit the masterlist, you can view the details of the request, but can only edit certain parts of it.
            @endif
        </p>
        @if (Auth::user()->hasPower('manage_characters'))
            <div class="card mb-3">
                <div class="card-body">
                    <a href="#" class="btn btn-outline-secondary process-button btn-sm float-right" data-action="cancel">Cancel</a>
                    <strong class="text-secondary">Cancelling</strong> the request returns it to its draft status, allowing the user to make further edits.
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <a href="#" class="btn btn-outline-success process-button btn-sm float-right" data-action="approve">Approve</a>
                    <strong class="text-success">Approving</strong> the request creates the update.
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="#" class="btn btn-outline-danger process-button btn-sm float-right" data-action="reject">Reject</a>
                    <strong class="text-danger">Rejecting</strong> the update returns any attached items and the user may not edit it any more.
                </div>
            </div>
        @endif
    @elseif($request->status == 'Approved')
        <p>This request has been approved. The data is preserved as a record of this submission.</p>
    @endif

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            @if ($request->user_id == Auth::user()->id && $request->status == 'Draft')
                $('.submit-button').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('designs/' . $request->id . '/confirm/') }}", 'Confirm Submission');
                });
                $('.delete-button').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('designs/' . $request->id . '/delete/') }}", 'Delete Submission');
                });
            @endif

            @if ($request->user_id == Auth::user()->id && $request->status == 'Pending' && $canCancel)
                $('.cancel-button').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('designs/' . $request->id . '/cancel/') }}", 'Cancel Submission');
                });
            @endif

            @if (Auth::user()->hasPower('manage_characters'))
                $('.process-button').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/designs/edit/' . $request->id) }}/" + $(this).data('action'), 'Confirm Action');
                });
            @endif
        });
    </script>
@endsection
