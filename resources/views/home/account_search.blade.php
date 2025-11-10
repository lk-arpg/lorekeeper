@extends('home.layout')

@section('home-title')
    Account Search
@endsection

@section('home-content')
    {!! breadcrumbs(['Inventory' => 'inventory', 'Account Search' => 'account-search']) !!}

    <h1>Account Search</h1>

    <p>Select an item to search for all occurrences of it in your and your characters' inventories. If a stack is currently "held" in a trade, design update, or submission, this will be stated and all held locations will be linked.</p>

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

        <p>You currently have {{ $userItems->pluck('count')->sum() + $characterItems->pluck('count')->sum() }} of this item between your and your characters' inventories.</p>

        @if ($userItems->count())
            <h5>In Your Inventory:</h5>
            <ul>
                @foreach ($userItems as $item)
                    <li>
                        A stack of {{ $item->count }}
                        @if ($item->count > $item->availableQuantity)
                            ({{ $item->availableQuantity }} Available {{ $item->getOthers() }})
                            <ul>
                                <?php
                                $tradesSent = $trades->where('sender_id', Auth::user()->id);
                                $tradesReceived = $trades->where('recipient_id', Auth::user()->id);
                                
                                // Collect hold location IDs and quantities
                                $holdLocations = [];
                                if (isset($item->trade_count) && $item->trade_count > 0) {
                                    foreach ($tradesSent as $trade) {
                                        if (isset($trade->data['sender']) && $trade->data['sender'] != [] && isset($trade->data['sender']['user_items']) && isset($trade->data['sender']['user_items'][$item->id])) {
                                            $holdLocations['trade'][$trade->id] = $trade->data['sender']['user_items'][$item->id];
                                        }
                                    }
                                    foreach ($tradesReceived as $trade) {
                                        if (isset($trade->data['recipient']) && $trade->data['recipient'] != [] && isset($trade->data['recipient']['user_items']) && isset($trade->data['recipient']['user_items'][$item->id])) {
                                            $holdLocations['trade'][$trade->id] = $trade->data['recipient']['user_items'][$item->id];
                                        }
                                    }
                                }
                                if (isset($item->update_count) && $item->update_count > 0) {
                                    foreach ($designUpdates as $update) {
                                        if ($update->data['user'] != [] && isset($update->data['user']['user_items']) && isset($update->data['user']['user_items'][$item->id])) {
                                            $holdLocations['update'][$update->id] = $update->data['user']['user_items'][$item->id];
                                        }
                                    }
                                }
                                if (isset($item->submission_count) && $item->submission_count > 0) {
                                    foreach ($submissions as $submission) {
                                        if ($submission->data['user'] != [] && isset($submission->data['user']['user_items']) && isset($submission->data['user']['user_items'][$item->id])) {
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
                                        array_push($held, '<a href="' . App\Models\Character\CharacterDesignUpdate::find($update)->url . '">Design Update #' . App\Models\Character\CharacterDesignUpdate::find($update)->id . '</a>' . ' (' . $quantity . ')');
                                    }
                                }
                                if (isset($holdLocations['submission'])) {
                                    foreach ($holdLocations['submission'] as $submission => $quantity) {
                                        array_push($held, '<a href="' . App\Models\Submission\Submission::find($submission)->viewUrl . '">Submission #' . App\Models\Submission\Submission::find($submission)->id . '</a>' . ' (' . $quantity . ')');
                                    }
                                }
                                ?>
                                @foreach ($held as $location)
                                    <li>
                                        {!! $location !!}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
        @if ($characterItems->count())
            <h5>In Character Inventories:</h5>
            <ul>
                @foreach ($characterItems as $item)
                    <li>
                        <a href="{{ $item->character->url }}">{{ $item->character->fullName }}</a> has a stack of {{ $item->count }}
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    <script>
        $(document).ready(function() {
            $('.selectize').selectize();
        });
    </script>

@endsection
