<div class="card">
    <div class="card-body">
        <h4>Default Prompt Rewards</h4>
        @if (isset($staffView) && $staffView)
            <p>For reference, these are the default rewards for this prompt. The editable section above is <u>inclusive</u> of these rewards.</p>
            @if ($count['all'])
                <p>The user has completed this prompt <strong>{{ $count['all'] }}</strong> time{{ $count['all'] == 1 ? '' : 's' }} overall.</p>
                @if ($prompt->limit)
                    <p>They have now submitted this prompt {{ $prompt->limit_period ? $count[$prompt->limit_period] : $count['all'] }} out of {{ $limit }} times
                        {{ $prompt->limit_period ? 'for this ' . strtolower($prompt->limit_period) : '' }}.
                @endif
            @endif
            <div class="{{ $prompt->limit ? 'text-danger' : '' }}">
                <p>{{ $prompt->limit ? 'Users can submit this prompt ' . $prompt->limit . ' time(s)' : 'Users can submit this prompt an unlimited number of times' }}
                    {{ $prompt->limit_period ? ' per ' . strtolower($prompt->limit_period) : '' }}
                    {{ $prompt->limit_character ? ' per character' : '' }}.</p>
            </div>
        @else
            <p>These are the default rewards for this prompt. The actual rewards you receive may be edited by a staff member during the approval process.</p>
            @if ($count['all'])
                <p>You have completed this prompt <strong>{{ $count['all'] }}</strong> time{{ $count['all'] == 1 ? '' : 's' }} overall.</p>
                @if ($prompt->limit)
                    <p>You have already submitted this prompt {{ $prompt->limit_period ? $count[$prompt->limit_period] : $count['all'] }} out of {{ $limit }} times
                        {{ $prompt->limit_period ? 'for this ' . strtolower($prompt->limit_period) : '' }}.
                @endif
            @endif
            <div class="{{ $prompt->limit ? 'text-danger' : '' }}">
                <p>{{ $prompt->limit ? 'You can submit this prompt ' . $prompt->limit . ' time(s)' : 'You can submit this prompt an unlimited number of times' }}
                    {{ $prompt->limit_period ? ' per ' . strtolower($prompt->limit_period) : '' }}
                    {{ $prompt->limit_character ? ' per character' : '' }}.</p>
            </div>
        @endif
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th width="10%">Recipient</th>
                    <th width="60%">Reward</th>
                    <th width="30%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prompt->rewards as $reward)
                    <tr>
                        <td>{!! $reward->rewardable_recipient == 'User' ? '<i class="fas fa-user"></i> User' : '<i class="fas fa-paw"></i> Character' !!}</td>
                        <td>{!! $reward->reward ? $reward->reward->displayName : $reward->rewardable_type !!}</td>
                        <td>{{ $reward->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if (count(getLimits($prompt)))
        <div class="card-footer">
            @include('widgets._limits', [
                'object' => $prompt,
                'hideUnlock' => true,
            ])
        </div>
    @endif
</div>
