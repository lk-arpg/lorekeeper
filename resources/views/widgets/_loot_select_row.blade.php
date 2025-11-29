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

<div id="{{ $prefix }}lootRowData" class="hide">
    <table class="table table-sm">
        <tbody id="{{ $prefix }}lootRow">
            <tr class="loot-row">
                @if ($showRecipient)
                    <td>
                        {!! Form::select($prefix . 'rewardable_recipient[]', ['Character' => 'Character', 'User' => 'User'], 'User', [
                            'class' => 'form-control',
                            'placeholder' => 'Select Recipient Type',
                        ]) !!}
                    </td>
                @endif
                <td>
                    {!! Form::select($prefix . 'rewardable_type[]', $rewardTypes, null, [
                        'class' => 'form-control reward-type',
                        'placeholder' => 'Select ' . $type . ' Type',
                    ]) !!}
                </td>
                <td class="loot-row-select"></td>
                <td>{!! Form::text($prefix . 'quantity[]', 1, ['class' => 'form-control']) !!}</td>
                @if (isset($extra_fields))
                    @foreach ($extra_fields as $field => $data)
                        <td>
                            @php
                                $field_name = $prefix . $field . '[]';
                                $value = $data['default'] ?? null;
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
        </tbody>
    </table>
    {!! Form::select($prefix . 'rewardable_id[]', $items, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::select($prefix . 'rewardable_id[]', $currencies, null, ['class' => 'form-control currency-select', 'placeholder' => 'Select Currency']) !!}
    @if ($showLootTables)
        {!! Form::select($prefix . 'rewardable_id[]', $tables, null, ['class' => 'form-control table-select', 'placeholder' => 'Select Loot Table']) !!}
    @endif
    @if ($showRaffles)
        {!! Form::select($prefix . 'rewardable_id[]', $raffles, null, ['class' => 'form-control raffle-select', 'placeholder' => 'Select Raffle']) !!}
    @endif
</div>
