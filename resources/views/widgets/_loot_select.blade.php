@php
    // This represents a common source and definition for assets used in loot_select
    // While it is not per se as tidy as defining these in the controller(s),
    // doing so this way enables better compatibility across disparate extensions
    $type = isset($type) ? $type : 'Reward';
    $isTradeable = isset($isTradeable) ? $isTradeable : false;
    if (isset($useCustomSelectize) && $useCustomSelectize) {
        $characterCurrencies = \App\Models\Currency\Currency::where('is_character_owned', 1)
            ->where(function ($query) use ($isTradeable) {
                if ($isTradeable) {
                    $query->where('allow_user_to_user', 1);
                }
            })
            ->orderBy('sort_character', 'DESC')
            ->get()
            ->mapWithKeys(function ($currency) {
                return [
                    $currency->id => json_encode([
                        'name' => $currency->name,
                        'image_url' => $currency->currencyIconUrl,
                    ]),
                ];
            });
        $items = \App\Models\Item\Item::orderBy('name')
            ->where(function ($query) use ($isTradeable) {
                if ($isTradeable) {
                    $query->where('allow_transfer', 1);
                }
            })
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->id => json_encode([
                        'name' => $item->name,
                        'image_url' => $item->imageUrl,
                    ]),
                ];
            });
        $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)
            ->where(function ($query) use ($isTradeable) {
                if ($isTradeable) {
                    $query->where('allow_user_to_user', 1);
                }
            })
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($currency) {
                return [
                    $currency->id => json_encode([
                        'name' => $currency->name,
                        'image_url' => $currency->currencyIconUrl,
                    ]),
                ];
            });
        if ($showLootTables) {
            $tables = \App\Models\Loot\LootTable::orderBy('name')
                ->get()
                ->mapWithKeys(function ($table) {
                    return [
                        $table->id => json_encode([
                            'name' => $table->name,
                        ]),
                    ];
                });
        }
        if ($showRaffles) {
            $raffles = \App\Models\Raffle\Raffle::where('rolled_at', null)
                ->where('is_active', 1)
                ->orderBy('name')
                ->get()
                ->mapWithKeys(function ($raffle) {
                    return [
                        $raffle->id => json_encode([
                            'name' => $raffle->name,
                        ]),
                    ];
                });
        }
    } else {
        $characterCurrencies = \App\Models\Currency\Currency::where('is_character_owned', 1)
            ->where(function ($query) use ($isTradeable) {
                if ($isTradeable) {
                    $query->where('allow_user_to_user', 1);
                }
            })
            ->orderBy('sort_character', 'DESC')
            ->pluck('name', 'id');
        $items = \App\Models\Item\Item::where(function ($query) use ($isTradeable) {
            if ($isTradeable) {
                $query->where('allow_transfer', 1);
            }
        })
            ->orderBy('name')
            ->pluck('name', 'id');
        $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)
            ->where(function ($query) use ($isTradeable) {
                if ($isTradeable) {
                    $query->where('allow_user_to_user', 1);
                }
            })
            ->orderBy('name')
            ->pluck('name', 'id');
        if ($showLootTables) {
            $tables = \App\Models\Loot\LootTable::orderBy('name')->pluck('name', 'id');
        }
        if ($showRaffles) {
            $raffles = \App\Models\Raffle\Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        }
    }
@endphp
<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addLoot">Add {{ $type }}</a>
</div>
<table class="table table-sm" id="lootTable">
    <thead>
        <tr>
            <th width="35%">{{ $type }} Type</th>
            <th width="35%">{{ $type }}</th>
            <th width="20%">Quantity</th>
            <th width="10%"></th>
        </tr>
    </thead>
    <tbody id="lootTableBody">
        @if ($loots)
            @foreach ($loots as $loot)
                <tr class="loot-row">
                    <td>{!! Form::select('rewardable_type[]', ['Item' => 'Item', 'Currency' => 'Currency'] + ($showLootTables ? ['LootTable' => 'Loot Table'] : []) + ($showRaffles ? ['Raffle' => 'Raffle Ticket'] : []), $loot->rewardable_type, [
                        'class' => 'form-control reward-type',
                        'placeholder' => 'Select ' . $type . ' Type',
                    ]) !!}</td>
                    <td class="loot-row-select">
                        @if ($loot->rewardable_type == 'Item')
                            {!! Form::select('rewardable_id[]', $items, $loot->rewardable_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                        @elseif($loot->rewardable_type == 'Currency')
                            {!! Form::select('rewardable_id[]', $currencies, $loot->rewardable_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                        @elseif($showLootTables && $loot->rewardable_type == 'LootTable')
                            {!! Form::select('rewardable_id[]', $tables, $loot->rewardable_id, ['class' => 'form-control table-select selectize', 'placeholder' => 'Select Loot Table']) !!}
                        @elseif($showRaffles && $loot->rewardable_type == 'Raffle')
                            {!! Form::select('rewardable_id[]', $raffles, $loot->rewardable_id, ['class' => 'form-control raffle-select selectize', 'placeholder' => 'Select Raffle']) !!}
                        @endif
                    </td>
                    <td>{!! Form::text('quantity[]', $loot->quantity, ['class' => 'form-control']) !!}</td>
                    <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
