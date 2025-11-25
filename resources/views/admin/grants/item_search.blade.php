@extends('admin.layout')

@section('admin-title')
    Item Search
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Item Search' => 'admin']) !!}

    <h1>Item Search</h1>

    <p>Select an item to search for all occurrences of it in user and character inventories. It will only display currently extant stacks (where the count is more than zero). If a stack is currently "held" in a trade, design update, or submission, this
        will be stated and all held locations will be linked.</p>

    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select('item_id', $items, Request::get('item_id'), ['class' => 'form-control selectize', 'placeholder' => 'Select an Item', 'style' => 'width: 25em; max-width: 100%;']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}

    @if ($item)
        <h3>{{ $item->name }}</h3>

        <p>There are currently {{ $userItems->pluck('count')->sum() + $characterItems->pluck('count')->sum() }} of this item owned by users and characters.</p>

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
            @foreach ($characters as $character)
                <li>
                    <a href="{{ $character->url }}">{{ $character->fullName }}</a> has {{ $characterItems->where('character_id', $character->id)->pluck('count')->sum() }}
                </li>
            @endforeach
        </ul>
    @endif

    <script>
        $(document).ready(function() {
            $('.selectize').selectize();
        });
    </script>

@endsection
