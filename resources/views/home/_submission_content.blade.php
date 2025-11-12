<h1>
    {{ $submission->prompt_id ? 'Submission' : 'Claim' }} (#{{ $submission->id }})
    @if (Auth::check() && $submission->user_id == Auth::user()->id && $submission->status == 'Draft')
        <a href="{{ url(($isClaim ? 'claims' : 'submissions') . '/draft/' . $submission->id) }}" class="btn btn-sm btn-outline-secondary ml-3">Edit Draft <i class="fas fa-pen ml-2"></i></a>
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
        @if ($submission->prompt_id)
            <div class="row mb-2 no-gutters">
                <div class="col-md-2">
                    <h5 class="mb-0">Prompt</h5>
                </div>
                <div class="col-md-10">{!! $submission->prompt->displayName !!}</div>
            </div>
        @endif
        <div class="row mb-2 no-gutters">
            <div class="col-md-2">
                <h5 class="mb-0">URL</h5>
            </div>
            <div class="col-md-10"><a href="{{ $submission->url }}">{{ $submission->url }}</a></div>
        </div>
        @if (config('lorekeeper.settings.allow_gallery_submissions_on_prompts') && $submission->data['gallery_submission_id'])
            <div class="row mb-2 no-gutters">
                <div class="col-md-2">
                    <h5 class="mb-0">Gallery Submission</h5>
                </div>
                <div class="col-md-10"><a href="{{ $submission->gallerySubmission->url }}">{{ $submission->gallerySubmission->title }}</a></div>
            </div>
        @endif
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
        {!! nl2br(htmlentities($submission->comments)) !!}
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

@if (array_filter(parseAssetData(isset($submission->data['rewards']) ? $submission->data['rewards'] : $submission->data)))
    <div class="card mb-3">
        <div class="card-header h2">Rewards</div>
        <div class="card-body">
            <table class="table table-sm">
                <thead class="thead-light">
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
@endif

<div class="card mb-3">
    <div class="card-header h2">Characters</div>
    <div class="card-body">
        @if (count($submission->characters()->whereRelation('character', 'deleted_at', null)->get()) != count($submission->characters()->get()))
            <div class="alert alert-warning">
                Some characters have been deleted since this submission was created.
            </div>
        @endif
        @foreach ($submission->characters()->with('character', 'character.image')->whereRelation('character', 'deleted_at', null)->get() as $character)
            <div class="submission-character-row mb-2">
                <div class="submission-character-thumbnail">
                    <a href="{{ $character->character->url }}"><img src="{{ $character->character->image->thumbnailUrl }}" class="img-thumbnail" alt="Thumbnail for {{ $character->character->fullName }}" /></a>
                </div>
                <div class="submission-character-info card ml-2">
                    <div class="card-body">
                        <div class="submission-character-info-content">
                            <h3 class="mb-2 submission-character-info-header"><a href="{{ $character->character->url }}">{{ $character->character->fullName }}</a></h3>
                            <div class="submission-character-info-body">
                                @if (array_filter(parseAssetData($character->data)))
                                    <table class="table table-sm mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="70%">Reward</th>
                                                <th width="30%">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach (parseAssetData($character->data) as $key => $type)
                                                @foreach ($type as $asset)
                                                    <tr>
                                                        <td>{!! $asset['asset']->displayName !!} ({!! ucfirst($key) !!})</td>
                                                        <td>{{ $asset['quantity'] }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach

                                            {{--

                                            If you want to "Categorize" the rewards by type, uncomment this and comment or remove the above @foreach.

                                            @foreach (parseAssetData($character->data) as $key => $type)
                                                @if (count($type))
                                                <tr><td colspan="2"><strong>{!! strtoupper($key) !!}</strong></td></tr>
                                                    @foreach ($type as $asset)
                                                        <tr>
                                                            <td>{!! $asset['asset']->displayName !!}</td>
                                                            <td>{{ $asset['quantity'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach

                                            --}}
                                        </tbody>
                                    </table>
                                @else
                                    <p>
                                        No rewards set.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@if (isset($inventory['user_items']) && array_filter($inventory['user_items']))
    <div class="card mb-3">
        <div class="card-header h2">Add-Ons</div>
        <div class="card-body">
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
        </div>
    </div>
@endif

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
