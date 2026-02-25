<h1>
    Submission (#{{ $submission->id }})
    @if (Auth::check() && $submission->user_id == Auth::user()->id && $submission->status == 'Draft')
        <a href="{{ url('queue-submissions/draft/' . $submission->id) }}" class="btn btn-sm btn-outline-secondary ml-3">Edit Draft <i class="fas fa-pen ml-2"></i></a>
    @endif
    <span class="float-right badge badge-{{ $submission->status == 'Pending' || $submission->status == 'Draft' ? 'secondary' : ($submission->status == 'Approved' ? 'success' : 'danger') }}">{{ $submission->status }}</span>

</h1>


<div class="card mb-3" style="clear:both;">
    <div class="card-body">
        <div class="row mb-2 no-gutters">
            <div class="col-md-2">
                <h5 class="mb-0">User</h5>
            </div>
            <div class="col-md-10">{!! $submission->user->displayName !!}</div>
        </div>
        <div class="row mb-2 no-gutters">
            <div class="col-md-2">
                <h5 class="mb-0">Queue</h5>
            </div>
            <div class="col-md-10">{!! $submission->queue->displayName !!}</div>
        </div>
        <div class="row mb-2 no-gutters">
            <div class="col-md-2">
                <h5 class="mb-0">Submitted</h5>
            </div>
            <div class="col-md-10">
                {!! format_date($submission->created_at) !!} ({{ $submission->created_at->diffForHumans() }})
            </div>
        </div>
        @if ($submission->status != 'Pending' && $submission->status != 'Draft')
            <div class="row mb-2 no-gutters">
                <div class="col-md-2">
                    <h5 class="mb-0">Processed</h5>
                </div>
                <div class="col-md-10">
                    {!! format_date($submission->updated_at) !!} ({{ $submission->updated_at->diffForHumans() }}) by {!! $submission->staff->displayName !!}
                </div>
            </div>
        @endif
    </div>
</div>

<div class="card mb-3">
    <div class="card-header h2">Comments</div>
    <div class="card-body">
        {!! $submission->parsed_comments !!}
    </div>

    @if (Auth::check() && $submission->staff_comments && ($submission->user_id == Auth::user()->id || Auth::user()->hasPower('manage_submissions')))
        <div class="card-header h2">Staff Comments</div>
        <div class="card-body">
            @if (isset($submission->parsed_staff_comments))
                {!! $submission->parsed_staff_comments !!}
            @else
                {!! $submission->staff_comments !!}
            @endif
        </div>
    @endif
</div>
<div class="card mb-3">
    <div class="card-header h2">Default Rewards</div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th width="70%">Reward</th>
                    <th width="30%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach (parseAssetData(isset($submission->data['rewards']) ? $submission->data['rewards'] : $submission->data) as $type)
                    @foreach ($type as $asset)
                        <tr>
                            <td>{!! $asset['asset'] ? $asset['asset']->displayName : 'Deleted Asset' !!}</td>
                            <td>{{ $asset['quantity'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-3">
    @if (View::exists('home.queues.types.' . $queue->queue_type))
        @include('home.queues.types.' . $queue->queue_type, ['data' => isset($submission->data['queue']) ? $submission->data['queue'] : null])
    @else
        <div class="card-header h2">Queue Form</div>
        <div class="card-body">
            <p>This queue has no associated extra form to fill in.</p>
        </div>
    @endif
</div>

<div class="card mb-3">
    <div class="card-header h2">Characters</div>
    <div class="card-body">
        @if ($queue->configSet('character_submit') && View::exists('home.queues.types.characters.' . $queue->queue_type . '_select_submitted'))
            @if (count($submission->characters()->whereRelation('character', 'deleted_at', null)->get()) != count($submission->characters()->get()))
                <div class="alert alert-warning">
                    Some characters have been deleted since this submission was created.
                </div>
            @endif
            @foreach ($submission->characters()->whereRelation('character', 'deleted_at', null)->get() as $character)
                @include('home.queues.types.characters.' . $queue->queue_type . '_select_submitted', ['data' => $character->data])
            @endforeach
        @else
            <p>This queue does not use characters.</p>
        @endif
    </div>
</div>

<div class="card mb-3">
    <div class="card-header h2">Add-Ons</div>
    @if ($queue->configSet('consume_items'))
        <div class="card-body">
            @if (isset($inventory['user_items']) && array_filter($inventory['user_items']))
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
        </div>
        @if (isset($inventory['currencies']) && array_filter($inventory['currencies']))
            <div class="card mb-3">
                <div class="card-header h2">{!! $submission->user->displayName !!}'s Bank</div>
                <div class="card-body">
                    <table class="table table-sm mb-3">
                        <thead class="thead-light">
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
                </div>
            </div>
        @endif
    @else
        <div class="card-body">
            <p class="mb-0">This queue does not consume add-ons.</p>
        </div>
    @endif
</div>
