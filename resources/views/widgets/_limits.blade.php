@php
    $limitTypes = collect(config('lorekeeper.limits.limit_types'))->map(function ($value, $key) {
        return $value['name'];
    });
    $limits = hasLimits($object) ? getLimits($object) : null;
    if (!isset($hideUnlock)) {
        $hideUnlock = false;
    }
    if (!isset($showNoLimits)) {
        $showNoLimits = false;
    }
@endphp

@if ($limits)
    @if (!isset($compact) || !$compact)
        <h4 class="my-3">{!! $object->displayName !!}'s Requirements</h4>
        <p>
            You must obtain or complete all of the following in order to access this {{ $object->assetType ? (substr($object->assetType, -1) === 's' ? substr($object->assetType, 0, -1) : $object->assetType) : '' }}.
        </p>
        @if ($limits->first()->is_unlocked && $limits->first()->isUnlocked(Auth::user() ?? null))
            <div class="alert alert-success">
                You previously have met these requirements and have unlocked access to this {{ $object->assetType ? (substr($object->assetType, -1) === 's' ? substr($object->assetType, 0, -1) : $object->assetType) : '' }}.
            </div>
        @else
            <div class="alert alert-warning">
                You must meet the following requirements to access this {{ $object->assetType ? (substr($object->assetType, -1) === 's' ? substr($object->assetType, 0, -1) : $object->assetType) : '' }}
                {{ $limits->first()->is_unlocked ? 'once.' : 'every time you interact with it.' }}
            </div>
        @endif
        <div class="mb-2 logs-table">
            <div class="logs-table-header">
                <div class="row no-gutters">
                    <div class="col-6 col-md-4">
                        <div class="logs-table-cell">Limit Type</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="logs-table-cell">Limit</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Quantity</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Is Debited?</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($limits as $limit)
                    <div class="logs-table-row">
                        <div class="row no-gutters flex-wrap">
                            <div class="col-6 col-md-4">
                                <div class="logs-table-cell">
                                    <i class="fas fa-question-circle mr-1" data-toggle="tooltip" title="{{ config('lorekeeper.limits.limit_types')[$limit->limit_type]['description'] }}"></i>
                                    {{ $limitTypes[$limit->limit_type] }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="logs-table-cell">
                                    {!! $limit->limit->displayName !!}
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="logs-table-cell">
                                    {{ $limit->quantity > 0 ? 'x' . $limit->quantity : 'N/A' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="logs-table-cell">
                                    @if ($limit->debit)
                                        <span class="badge badge-success" data-toggle="tooltip" title="This limit is debited from your account possessions.">
                                            <i class="fas fa-check" aria-hidden="true"></i>
                                        </span>
                                    @else
                                        <span class="badge badge-danger" data-toggle="tooltip" title="This limit will not be debited from your account.">
                                            <i class="fas fa-times" aria-hidden="true"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @if (!$hideUnlock)
            @if (Auth::check() && !$limits->first()->isUnlocked(Auth::user() ?? null) && !$limits->first()->is_auto_unlocked)
                <div class="alert alert-secondary text-center px-0 py-1 mb-0">
                    {!! Form::open(['url' => 'limits/unlock/' . $limits->first()->id]) !!}
                    {!! Form::submit('Unlock', ['class' => 'btn btn-sm btn-secondary']) !!}
                    {!! Form::close() !!}
                </div>
            @else
                <div class="alert alert-secondary text-center px-0 p-1 mb-0 font-weight-bold">
                    You must be logged in to unlock this limit.
                </div>
            @endif
        @endif
    @else
        <div class="alert alert-{{ $limits->first()->is_unlocked && $limits->first()->isUnlocked(Auth::user() ?? null) ? 'info' : 'danger' }} p-0 mt-2">
            <small>
                (Requires {!! implode(
                    ', ',
                    $limits->map(function ($limit) use ($limitTypes) {
                            return ($limit->quantity ? $limit->quantity . ' ' : '') . $limit->limit->displayName;
                        })->toArray(),
                ) !!}
                {{ $limits->first()->is_unlocked ? 'once' : 'every time you interact with it' }}.)
                @if (!$hideUnlock && !$limits->first()->isUnlocked(Auth::user() ?? null) && !$limits->first()->is_auto_unlocked)
                    <div class="alert alert-secondary text-center p-0 mb-0">
                        <small>
                            {!! Form::open(['url' => 'limits/unlock/' . $limits->first()->id]) !!}
                            {!! Form::submit('Unlock', ['class' => 'btn btn-sm btn-secondary']) !!}
                            {!! Form::close() !!}
                        </small>
                    </div>
                @endif
            </small>
        </div>
    @endif
@elseif ($showNoLimits)
    @if (!isset($compact) || !$compact)
        <h4 class="my-3">{!! $object->displayName !!}'s Requirements</h4>
        <div class="alert alert-info">
            No requirements to access this {{ $object->assetType ? (substr($object->assetType, -1) === 's' ? substr($object->assetType, 0, -1) : $object->assetType) : '' }}.
        </div>
    @else
        <div class="alert alert-info p-0 mb-0">
            <small>
                No requirements to access this {{ $object->assetType ? (substr($object->assetType, -1) === 's' ? substr($object->assetType, 0, -1) : str_replace('_', ' ', $object->assetType)) : '' }}.
            </small>
        </div>
    @endif
@endif
