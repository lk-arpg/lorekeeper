<h3>Rewards</h3>
<h4>User Rewards <i class="fas fa-user"></i></h4>
@if (!$queue->rewards)
    No user rewards.
@else
    <table class="table table-sm">
        <thead>
            <tr>
                <th width="70%">Reward</th>
                <th width="30%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($queue->rewardItems as $key => $type)
                @if (count($type))
                    <tr>
                        <td colspan="2"><strong>{!! strtoupper($key) !!}</strong></td>
                    </tr>
                    @foreach ($type as $asset)
                        <tr>
                            <td>{!! $asset['asset']->displayName !!}</td>
                            <td>{{ $asset['quantity'] }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
@endif
@if ($queue->configSet('character_submit'))
    <h4>Character Rewards <i class="fas fa-paw"></i></h4>
    @if (!$queue->characterRewards)
        No character rewards.
    @else
        <table class="table table-sm">
            <thead>
                <tr>
                    <th width="70%">Reward</th>
                    <th width="30%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($queue->characterRewardItems as $key => $type)
                    @if (count($type))
                        <tr>
                            <td colspan="2"><strong>{!! strtoupper($key) !!}</strong></td>
                        </tr>
                        @foreach ($type as $asset)
                            <tr>
                                <td>{!! $asset['asset']->displayName !!}</td>
                                <td>{{ $asset['quantity'] }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
@endif
