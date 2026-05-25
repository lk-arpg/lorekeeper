<ul>
    @foreach ($users as $user)
        <li>
            {!! $user->displayName !!} has {{ $userItems->where('user_id', $user->id)->pluck('count')->sum() }}
            @if ($userItems->where('user_id', $user->id)->pluck('count')->sum() > $userItems->where('user_id', $user->id)->pluck('availableQuantity')->sum())
                ({{ $userItems->where('user_id', $user->id)->pluck('availableQuantity')->sum() }} Available)
                <ul>
                    @foreach ($userItems->where('user_id', $user->id) as $item)
                        @if ($item->count > $item->availableQuantity)
                            <?php
                            $userTradesSent = $trades->where('sender_id', $user->id);
                            $userTradesReceived = $trades->where('recipient_id', $user->id);
                            $userUpdates = $designUpdates->where('user_id', $user->id);
                            $userSubmissions = $submissions->where('user_id', $user->id);
                            
                            // Collect hold location IDs and quantities
                            $holdLocations = [];
                            if (isset($item->trade_count) && $item->trade_count > 0) {
                                foreach ($userTradesSent as $trade) {
                                    if (isset($trade->data['sender']) && $trade->data['sender'] != [] && isset($trade->data['sender']['user_items']) && isset($trade->data['sender']['user_items'][$item->id])) {
                                        $holdLocations['trade'][$trade->id] = $trade->data['sender']['user_items'][$item->id];
                                    }
                                }
                                foreach ($userTradesReceived as $trade) {
                                    if (isset($trade->data['recipient']) && $trade->data['recipient'] != [] && isset($trade->data['recipient']['user_items']) && isset($trade->data['recipient']['user_items'][$item->id])) {
                                        $holdLocations['trade'][$trade->id] = $trade->data['recipient']['user_items'][$item->id];
                                    }
                                }
                            }
                            if (isset($item->update_count) && $item->update_count > 0) {
                                foreach ($userUpdates as $update) {
                                    if (isset($update->data['user']) && $update->data['user'] != [] && isset($update->data['user']['user_items']) && isset($update->data['user']['user_items'][$item->id])) {
                                        $holdLocations['update'][$update->id] = $update->data['user']['user_items'][$item->id];
                                    }
                                }
                            }
                            if (isset($item->submission_count) && $item->submission_count > 0) {
                                foreach ($userSubmissions as $submission) {
                                    if (isset($update->data['user']) && $submission->data['user'] != [] && isset($submission->data['user']['user_items']) && isset($submission->data['user']['user_items'][$item->id])) {
                                        $holdLocations['submission'][$submission->id] = $submission->data['user']['user_items'][$item->id];
                                    }
                                }
                            }
                            
                            // Format a string with all the places a stack is held
                            $held = [];
                            if (isset($holdLocations['trade'])) {
                                foreach ($holdLocations['trade'] as $trade => $quantity) {
                                    array_push($held, '<a href="' . App\Models\Trade\Trade::find($trade)->url . '">Trade #' . App\Models\Trade\Trade::find($trade)->id . '</a>' . ' (' . $quantity . ')');
                                }
                            }
                            if (isset($holdLocations['update'])) {
                                foreach ($holdLocations['update'] as $update => $quantity) {
                                    array_push($held, (Auth::user()->hasPower('manage_characters') ? '<a href="' . App\Models\Character\CharacterDesignUpdate::find($update)->url . '">Design Update #' . App\Models\Character\CharacterDesignUpdate::find($update)->id . '</a>' : 'Design Update #' . App\Models\Character\CharacterDesignUpdate::find($update)->id) . ' (' . $quantity . ')');
                                }
                            }
                            if (isset($holdLocations['submission'])) {
                                foreach ($holdLocations['submission'] as $submission => $quantity) {
                                    array_push($held, (Auth::user()->hasPower('manage_submissions') ? '<a href="' . App\Models\Submission\Submission::find($submission)->viewUrl . '">Submission #' . App\Models\Submission\Submission::find($submission)->id . '</a>' : 'Submission #' . App\Models\Submission\Submission::find($submission)->id) . ' (' . $quantity . ')');
                                }
                            }
                            $heldString = implode(', ', $held);
                            ?>
                            <li>
                                {{ $item->getOthers() }} : {!! $heldString !!}
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
    {!! $users->render() !!}
</ul>
