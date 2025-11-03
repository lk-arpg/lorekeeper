@php
    // This file represents a common source and definition for assets used in loot_select
    // While it is not per se as tidy as defining these in the controller(s),
    // doing so this way enables better compatibility across disparate extensions

    if (!isset($isCharacter)) {
        $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
        $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id');
        if ($showRaffles) {
            $raffles = \App\Models\Raffle\Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        }
    } else {
        $items = \App\Models\Item\Item::whereIn('item_category_id', \App\Models\Item\ItemCategory::where('is_character_owned', 1)->pluck('id')->toArray())
            ->orderBy('name')
            ->pluck('name', 'id');
        $characterCurrencies = \App\Models\Currency\Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id');
    }

    if ($showLootTables) {
        $tables = \App\Models\Loot\LootTable::orderBy('name')->pluck('name', 'id');
    }

    if (!isset($prefix)) {
        $prefix = '';
    }
@endphp

<div id="{{ $prefix }}lootRowData" class="hide">
    <table class="table table-sm">
        <tbody id="{{ $prefix }}lootRow">
            <tr class="{{ $prefix }}loot-row">
                <td>{!! Form::select($prefix . 'rewardable_type[]', ['Item' => 'Item', 'Currency' => 'Currency'] + (!isset($isCharacter) ? ($showLootTables ? ['LootTable' => 'Loot Table'] : []) + ($showRaffles ? ['Raffle' => 'Raffle Ticket'] : []) : []), null, [
                    'class' => 'form-control ' . $prefix . 'reward-type',
                    'placeholder' => 'Select Reward Type',
                ]) !!}</td>
                <td class="{{ $prefix }}loot-row-select"></td>
                <td>{!! Form::text($prefix . 'quantity[]', 1, ['class' => 'form-control']) !!}</td>
                <td class="text-right"><a href="#" class="btn btn-danger {{ $prefix }}remove-loot-button">Remove</a></td>
            </tr>
        </tbody>
    </table>
    {!! Form::select($prefix . 'rewardable_id[]', $items, null, ['class' => 'form-control ' . $prefix . 'item-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::select($prefix . 'rewardable_id[]', !isset($isCharacter) ? $currencies : $characterCurrencies, null, ['class' => 'form-control ' . $prefix . 'currency-select', 'placeholder' => 'Select Currency']) !!}
    @if ($showLootTables)
        {!! Form::select($prefix . 'rewardable_id[]', $tables, null, ['class' => 'form-control ' . $prefix . 'table-select', 'placeholder' => 'Select Loot Table']) !!}
    @endif
    @if (!isset($isCharacter))
        @if ($showRaffles)
            {!! Form::select($prefix . 'rewardable_id[]', $raffles, null, ['class' => 'form-control ' . $prefix . 'raffle-select', 'placeholder' => 'Select Raffle']) !!}
        @endif
    @endif
</div>
