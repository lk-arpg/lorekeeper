@extends('admin.layout')

@section('admin-title')
    Submission (#{{ $submission->id }})
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Queue Submissions' => 'admin/queue-submissions/pending', 'Submission (#' . $submission->id . ')' => $submission->viewUrl]) !!}


    @if ($submission->status == 'Pending')

        <h1>
            Submission (#{{ $submission->id }})
            <span class="float-right badge badge-{{ $submission->status == 'Pending' || $submission->status == 'Draft' ? 'secondary' : ($submission->status == 'Approved' ? 'success' : 'danger') }}">
                {{ $submission->status }}
            </span>
        </h1>

        <div class="mb-1">
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>User</h5>
                </div>
                <div class="col-md-10 col-8">{!! $submission->user->displayName !!}</div>
            </div>
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>Queue</h5>
                </div>
                <div class="col-md-10 col-8">{!! $submission->queue->displayName !!}</div>
            </div>
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>Previous Submissions</h5>
                </div>
                <div class="col-md-10 col-8">{{ $count }} {!! add_help('This is the number of times the user has submitted this queue before and had their submission approved.') !!}</div>
            </div>
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>Submitted</h5>
                </div>
                <div class="col-md-10 col-8">{!! format_date($submission->created_at) !!} ({{ $submission->created_at->diffForHumans() }})</div>
            </div>
        </div>
        <h2>Comments</h2>
        <div class="card mb-3">
            <div class="card-body">{!! $submission->parsed_comments !!}</div>
        </div>
        @if (Auth::check() && $submission->staff_comments && ($submission->user_id == Auth::user()->id || Auth::user()->hasPower('manage_submissions')))
            <h2>Staff Comments ({!! $submission->staff->displayName !!})</h2>
            <div class="card mb-3">
                <div class="card-body">
                    @if (isset($submission->parsed_staff_comments))
                        {!! $submission->parsed_staff_comments !!}
                    @else
                        {!! $submission->staff_comments !!}
                    @endif
                </div>
            </div>
        @endif

        {!! Form::open(['url' => url()->current(), 'id' => 'submissionForm']) !!}

        <h2>Rewards</h2>
        @include('widgets._loot_select', ['loots' => $submission->rewards, 'showLootTables' => true, 'showRaffles' => true])
        <div class="mb-3">
            @include('home.queues._queue', ['queue' => $submission->queue, 'staffView' => true])
        </div>


        <div class="card mb-3">
            <div class="card-header h2">Characters</div>
            <div class="card-body">
                @if ($queue->configSet('character_submit') && View::exists('home.queues.types.characters.' . $queue->queue_type . '_select_entry'))
                    <div id="characters" class="mb-3">
                        @if (count($submission->characters()->whereRelation('character', 'deleted_at', null)->get()) != count($submission->characters()->get()))
                            <div class="alert alert-warning">
                                Some characters have been deleted since this submission was created.
                            </div>
                        @endif
                        @foreach ($submission->characters()->whereRelation('character', 'deleted_at', null)->get() as $character)
                            @include('home.queues.types.characters.' . $queue->queue_type . '_select_entry', ['data' => $character->data])
                        @endforeach
                    </div>
                    <div class="text-right mb-3">
                        <a href="#" class="btn btn-outline-info" id="addCharacter">Add Character</a>
                    </div>
                @else
                    <p>This queue does not use characters.</p>
                @endif
            </div>
        </div>

        <h2>Add-Ons</h2>
        @if ($queue->configSet('item_consume'))
            @if (isset($inventory['user_items']))
                <p>These items have been removed from the submitter's inventory and will be refunded if the request is rejected or consumed if it is approved.</p>
                <table class="table table-sm">
                    <thead class="thead-light">
                        <tr class="d-flex">
                            <th class="col-2">Item</th>
                            <th class="col-4">Source</th>
                            <th class="col-4">Notes</th>
                            <th class="col-2">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventory['user_items'] as $itemRow)
                            <tr class="d-flex">
                                <td class="col-2">
                                    @if (isset($itemsrow[$itemRow['asset']->item_id]->image_url))
                                        <img class="small-icon" src="{{ $itemsrow[$itemRow['asset']->item_id]->image_url }}" alt="{{ $itemsrow[$itemRow['asset']->item_id]->name }}">
                                    @endif {!! $itemsrow[$itemRow['asset']->item_id]->name !!}
                                <td class="col-4">{!! array_key_exists('data', $itemRow['asset']->data) ? ($itemRow['asset']->data['data'] ? $itemRow['asset']->data['data'] : 'N/A') : 'N/A' !!}</td>
                                <td class="col-4">{!! array_key_exists('notes', $itemRow['asset']->data) ? ($itemRow['asset']->data['notes'] ? $itemRow['asset']->data['notes'] : 'N/A') : 'N/A' !!}</td>
                                <td class="col-2">{!! $itemRow['quantity'] !!}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if (isset($inventory['currencies']))
                <h3>{!! $submission->user->displayName !!}'s Bank</h3>
                <table class="table table-sm mb-3">
                    <thead>
                        <tr>
                            <th width="70%">Currency</th>
                            <th width="30%">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventory['currencies'] as $currency)
                            <tr>
                                <td>{!! $currency['asset']->name !!}</td>
                                <td>{{ $currency['quantity'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @else
            <p>This queue does not consume add-ons.</p>
        @endif

        <div class="form-group">
            {!! Form::label('staff_comments', 'Staff Comments (Optional)') !!}
            {!! Form::textarea('staff_comments', $submission->staffComments, ['class' => 'form-control wysiwyg']) !!}
        </div>

        <div class="text-right">
            <a href="#" class="btn btn-danger mr-2" id="rejectionButton">Reject</a>
            <a href="#" class="btn btn-secondary mr-2" id="cancelButton">Cancel</a>
            <a href="#" class="btn btn-success" id="approvalButton">Approve</a>
        </div>

        {!! Form::close() !!}

        @include('widgets._loot_select_row', ['showLootTables' => true, 'showRaffles' => true])

        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content hide" id="approvalContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Approval</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will approve the submission and distribute the above rewards to the user.</p>
                        <div class="text-right">
                            <a href="#" id="approvalSubmit" class="btn btn-success">Approve</a>
                        </div>
                    </div>
                </div>
                <div class="modal-content hide" id="cancelContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Cancellation</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will cancel the submission and send it back to drafts. Make sure to include a staff comment if you do this!</p>
                        <div class="text-right">
                            <a href="#" id="cancelSubmit" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
                <div class="modal-content hide" id="rejectionContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Rejection</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will reject the submission.</p>
                        <div class="text-right">
                            <a href="#" id="rejectionSubmit" class="btn btn-danger">Reject</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-danger">This submission has already been processed.</div>
        @include('home.queues._submission_content', ['submission' => $submission])
    @endif

@endsection

@section('scripts')
    @parent
    @if ($submission->status == 'Pending')
        @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
        @if ($queue->configSet('character_submit') && View::exists('home.queues.types.characters.' . $queue->queue_type . '_select_js'))
            @include('home.queues.types.characters.' . $queue->queue_type . '_select_js')
        @endif
        <script>
            $(document).ready(function() {
                var $confirmationModal = $('#confirmationModal');
                var $submissionForm = $('#submissionForm');

                var $approvalButton = $('#approvalButton');
                var $approvalContent = $('#approvalContent');
                var $approvalSubmit = $('#approvalSubmit');

                var $rejectionButton = $('#rejectionButton');
                var $rejectionContent = $('#rejectionContent');
                var $rejectionSubmit = $('#rejectionSubmit');

                var $cancelButton = $('#cancelButton');
                var $cancelContent = $('#cancelContent');
                var $cancelSubmit = $('#cancelSubmit');

                $approvalButton.on('click', function(e) {
                    e.preventDefault();
                    $approvalContent.removeClass('hide');
                    $rejectionContent.addClass('hide');
                    $cancelContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $rejectionButton.on('click', function(e) {
                    e.preventDefault();
                    $rejectionContent.removeClass('hide');
                    $approvalContent.addClass('hide');
                    $cancelContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $cancelButton.on('click', function(e) {
                    e.preventDefault();
                    $cancelContent.removeClass('hide');
                    $rejectionContent.addClass('hide');
                    $approvalContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $approvalSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}/approve');
                    $submissionForm.submit();
                });

                $rejectionSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}/reject');
                    $submissionForm.submit();
                });

                $cancelSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}/cancel');
                    $submissionForm.submit();
                });
            });
        </script>
    @endif
@endsection
