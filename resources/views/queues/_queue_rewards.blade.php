@if (!count($queue->rewards))
    No rewards.
@else
    <table class="table table-sm mb-0">
        <thead>
            <tr>
                <th width="70%">Reward</th>
                <th width="30%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($queue->rewards as $reward)
                <tr>
                    <td>
                        {!! $reward->rewardable_recipient == 'User' ? '<i class="fas fa-user" data-toggle="tooltip" title="User Reward"></i>' : '<i class="fas fa-paw" data-toggle="tooltip" title="Character Reward"></i>' !!}
                        {!! $reward->reward ? $reward->reward->displayName : $reward->rewardable_type !!}
                    </td>
                    <td>{{ $reward->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
