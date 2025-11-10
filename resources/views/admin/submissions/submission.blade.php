@extends('admin.layout')

@section('admin-title')
    {{ $submission->prompt_id ? 'Submission' : 'Claim' }} (#{{ $submission->id }})
@endsection

@section('admin-content')
    @if ($submission->prompt_id)
        {!! breadcrumbs(['Admin Panel' => 'admin', 'Prompt Queue' => 'admin/submissions/pending', 'Submission (#' . $submission->id . ')' => $submission->viewUrl]) !!}
    @else
        {!! breadcrumbs(['Admin Panel' => 'admin', 'Claim Queue' => 'admin/claims/pending', 'Claim (#' . $submission->id . ')' => $submission->viewUrl]) !!}
    @endif

    @if ($submission->status == 'Pending')

        <h1>
            {{ $submission->prompt_id ? 'Submission' : 'Claim' }} (#{{ $submission->id }})
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
            @if ($submission->prompt_id)
                <div class="row">
                    <div class="col-md-2 col-4">
                        <h5>Prompt</h5>
                    </div>
                    <div class="col-md-10 col-8">{!! $submission->prompt->displayName !!}</div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-4">
                        <h5>Previous Submissions</h5>
                    </div>
                    <div class="col-md-10 col-8">{{ $count }} {!! add_help('This is the number of times the user has submitted this prompt before and had their submission approved.') !!}</div>
                </div>
            @endif
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>URL</h5>
                </div>
                <div class="col-md-10 col-8"><a href="{{ $submission->url }}">{{ $submission->url }}</a></div>
            </div>
            @if (config('lorekeeper.settings.allow_gallery_submissions_on_prompts') && $submission->data['gallery_submission_id'])
                <div class="row mb-2 no-gutters">
                    <div class="col-md-2">
                        <h5 class="mb-0">Gallery Submission</h5>
                    </div>
                    <div class="col-md-10"><a href="{{ $submission->gallerySubmission->url }}">{{ $submission->gallerySubmission->title }}</a></div>
                </div>
            @endif
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>Submitted</h5>
                </div>
                <div class="col-md-10 col-8">{!! format_date($submission->created_at) !!} ({{ $submission->created_at->diffForHumans() }})</div>
            </div>
        </div>
        <h2>Comments</h2>
        <div class="card mb-3">
            <div class="card-body">{!! nl2br(htmlentities($submission->comments)) !!}</div>
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
        @if ($submission->prompt_id)
            <div class="mb-3">
                @include('home._prompt', ['prompt' => $submission->prompt, 'staffView' => true])
            </div>
        @endif

        <h2>Characters</h2>
        <div id="characters" class="mb-3">
            @if (count($submission->characters()->whereRelation('character', 'deleted_at', null)->get()) != count($submission->characters()->get()))
                <div class="alert alert-warning">
                    Some characters have been deleted since this submission was created.
                </div>
            @endif
            @foreach ($submission->characters()->whereRelation('character', 'deleted_at', null)->get() as $character)
                @include('widgets._character_select_entry', ['characterCurrencies' => $characterCurrencies, 'items' => $items, 'tables' => $tables, 'character' => $character, 'expanded_rewards' => $expanded_rewards])
            @endforeach
        </div>
        <div class="text-right mb-3">
            <a href="#" class="btn btn-outline-info" id="addCharacter">Add Character</a>
        </div>

        @if (isset($inventory['user_items']))
            <h2>Add-Ons</h2>
            <p>These items have been removed from the {{ $submission->prompt_id ? 'submitter' : 'claimant' }}'s inventory and will be refunded if the request is rejected or consumed if it is approved.</p>
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

        <div id="characterComponents" class="hide">
            <div class="submission-character mb-3 card">
                <div class="card-body">
                    <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a></div>
                    <div class="row">
                        <div class="col-md-2 align-items-stretch d-flex">
                            <div class="d-flex text-center align-items-center">
                                <div class="character-image-blank">Enter character code.</div>
                                <div class="character-image-loaded hide"></div>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <div class="form-group">
                                {!! Form::label('slug', 'Character Code') !!}
                                {!! Form::select('slug[]', $characters, null, ['class' => 'form-control character-code', 'placeholder' => 'Select Character']) !!}
                            </div>
                            <div class="character-rewards hide">
                                <h4>Character Rewards</h4>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            @if ($expanded_rewards)
                                                <th width="35%">Reward Type</th>
                                                <th width="35%">Reward</th>
                                            @else
                                                <th width="70%">Reward</th>
                                            @endif
                                            <th width="30%">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="character-rewards">
                                    </tbody>
                                </table>
                                <div class="text-right">
                                    <a href="#" class="btn btn-outline-primary btn-sm add-reward">Add Reward</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <table>
                <tr class="character-reward-row">

                    @if ($expanded_rewards)
                        <td>
                            {!! Form::select('character_rewardable_type[]', ['Item' => 'Item', 'Currency' => 'Currency', 'LootTable' => 'Loot Table'], null, ['class' => 'form-control character-rewardable-type', 'placeholder' => 'Select Reward Type']) !!}
                        </td>
                        <td class="lootDivs">
                            <div class="character-currencies hide">{!! Form::select('character_rewardable_id[]', $characterCurrencies, 0, ['class' => 'form-control character-currency-id', 'placeholder' => 'Select Currency']) !!}</div>
                            <div class="character-items hide">{!! Form::select('character_rewardable_id[]', $items, 0, ['class' => 'form-control character-item-id', 'placeholder' => 'Select Item']) !!}</div>
                            <div class="character-tables hide">{!! Form::select('character_rewardable_id[]', $tables, 0, ['class' => 'form-control character-table-id', 'placeholder' => 'Select Loot Table']) !!}</div>
                        </td>
                    @else
                        <td class="lootDivs">
                            {!! Form::hidden('character_rewardable_type[]', 'Currency', ['class' => 'character-rewardable-type']) !!}
                            {!! Form::select('character_rewardable_id[]', $characterCurrencies, 0, ['class' => 'form-control character-currency-id', 'placeholder' => 'Select Currency']) !!}
                        </td>
                    @endif

                    <td class="d-flex align-items-center">
                        {!! Form::text('character_quantity[]', 0, ['class' => 'form-control mr-2  character-rewardable-quantity']) !!}
                        <a href="#" class="remove-reward d-block"><i class="fas fa-times text-muted"></i></a>
                    </td>
                </tr>
            </table>
        </div>
        @include('widgets._loot_select_row', ['showLootTables' => true, 'showRaffles' => true])

        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content hide" id="approvalContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Approval</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will approve the {{ $submission->prompt_id ? 'submission' : 'claim' }} and distribute the above rewards to the user.</p>
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
                        <p>
                            This will cancel the {{ $submission->prompt_id ? 'submission' : 'claim' }} and send it back to drafts.
                            Make sure to include a staff comment if you do this!
                        </p>
                        @if ($submission->prompt_id && hasLimits($submission->prompt))
                            <div class="alert alert-secondary">
                                The user will not have to meet the prompt's requirements to submit again.
                                <div class="text-center mb-0">
                                    @include('widgets._limits', [
                                        'object' => $submission->prompt,
                                        'compact' => true,
                                        'hideUnlock' => true,
                                    ])
                                </div>
                            </div>
                        @endif
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
                        <p>This will reject the {{ $submission->prompt_id ? 'submission' : 'claim' }}.</p>
                        <div class="text-right">
                            <a href="#" id="rejectionSubmit" class="btn btn-danger">Reject</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-danger">This {{ $submission->prompt_id ? 'submission' : 'claim' }} has already been processed.</div>
        @include('home._submission_content', ['submission' => $submission])
    @endif

@endsection

@section('scripts')
    @parent
    @if ($submission->status == 'Pending')
        @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
        @include('js._character_select_js')
        @include('js._tinymce_wysiwyg')
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
