@php
    // This represents a common source and definition for assets used in loot_select
    // While it is not per se as tidy as defining these in the controller(s),
    // doing so this way enables better compatibility across disparate extensions

    if (!isset($type)) {
        $type = 'Reward';
    }
    if (!isset($isTradeable)) {
        $isTradeable = false;
    }
    if (!isset($prefix)) {
        $prefix = '';
    }

    // View options
    if (!isset($showRecipient)) {
        $showRecipient = false;
    }
    if (!isset($showLootTables)) {
        $showLootTables = false;
    }
    if (!isset($showRaffles)) {
        $showRaffles = false;
    }

    // Reward types, should reduce friction of merge conflicts
    $rewardTypes =
        [
            'Item' => 'Item',
            'Currency' => 'Currency',
        ] +
        ($showLootTables ? ['LootTable' => 'Loot Table'] : []) +
        ($showRaffles ? ['Raffle' => 'Raffle Ticket'] : []);

    // Custom Selectize
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
    <a href="#" class="btn btn-outline-info" id="{{ $prefix }}addLoot">Add {{ $type }}</a>
</div>
<table class="table table-sm" id="{{ $prefix }}lootTable">
    <thead>
        <tr>
            @if ($showRecipient)
                <th width="{{ isset($extra_fields) ? '10%' : '15%' }}">{{ $type }} Recipient</th>
            @endif
            <th width="{{ $showRecipient ? (isset($extra_fields) ? '15%' : '25%') : (isset($extra_fields) ? '25%' : '35%') }}">{{ $type }} Type</th>
            <th width="{{ $showRecipient || isset($extra_fields) ? '25%' : '35%' }}">{{ $type }}</th>
            <th width="{{ $showRecipient ? (isset($extra_fields) ? '15%' : '20%') : '20%' }}">Quantity</th>
            @if (isset($extra_fields))
                @foreach ($extra_fields as $field => $data)
                    @if ($data['label'] == 'Weight')
                        <th>{{ $data['label'] }} {!! add_help($data['tooltip'] ?? '') !!}</th>
                        <th>Chance</th>
                    @else
                        <th>{{ $data['label'] }} {!! add_help($data['tooltip'] ?? '') !!}</th>
                    @endif
                @endforeach
            @endif
            <th width="10%"></th>
        </tr>
    </thead>
    <tbody id="{{ $prefix }}lootTableBody">
        @if ($loots)
            @foreach ($loots as $loot)
                <tr class="loot-row">
                    @if ($showRecipient)
                        <td>
                            {!! Form::select($prefix . 'rewardable_recipient[]', ['Character' => 'Character', 'User' => 'User'], $loot->rewardable_recipient, [
                                'class' => 'form-control recipient-type',
                                'placeholder' => 'Select Recipient Type',
                            ]) !!}
                        </td>
                    @endif
                    <td>
                        {!! Form::select($prefix . 'rewardable_type[]', $rewardTypes, $loot->rewardable_type, [
                            'class' => 'form-control reward-type',
                            'placeholder' => 'Select ' . $type . ' Type',
                        ]) !!}
                    </td>
                    <td class="loot-row-select">
                        @if ($loot->rewardable_type == 'Item')
                            {!! Form::select($prefix . 'rewardable_id[]', $items, $loot->rewardable_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                        @elseif($loot->rewardable_type == 'Currency')
                            {!! Form::select($prefix . 'rewardable_id[]', $currencies, $loot->rewardable_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                        @elseif($showLootTables && $loot->rewardable_type == 'LootTable')
                            {!! Form::select($prefix . 'rewardable_id[]', $tables, $loot->rewardable_id, ['class' => 'form-control table-select selectize', 'placeholder' => 'Select Loot Table']) !!}
                        @elseif($showRaffles && $loot->rewardable_type == 'Raffle')
                            {!! Form::select($prefix . 'rewardable_id[]', $raffles, $loot->rewardable_id, ['class' => 'form-control raffle-select selectize', 'placeholder' => 'Select Raffle']) !!}
                        @endif
                    </td>
                    <td>{!! Form::text($prefix . 'quantity[]', $loot->quantity, ['class' => 'form-control']) !!}</td>
                    @if (isset($extra_fields))
                        @foreach ($extra_fields as $field => $data)
                            <td>
                                @php
                                    $field_name = $prefix . $field . '[]';
                                    $value = $loot->data[$field] ?? ($data['default'] ?? null);
                                    $attributes = $data['attributes'] ?? [];
                                @endphp
                                {!! Form::{$data['type']}($field_name, $value, array_merge(['class' => 'form-control ' . ($data['class'] ?? ''), 'placeholder' => $data['label']], $attributes)) !!}
                            </td>
                            @if ($data['label'] == 'Weight')
                                <td class="loot-row-chance"></td>
                            @endif
                        @endforeach
                    @endif
                    <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
