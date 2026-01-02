@php
    // This represents a common source and definition for assets used in loot_select
    // While it is not per se as tidy as defining these in the controller(s),
    // doing so this way enables better compatibility across disparate extensions

    if (!isset($type)) {
        $type = 'Reward';
    }
    if (!isset($prefix)) {
        $prefix = '';
    }
    if (!isset($showRecipient)) {
        $showRecipient = false;
    }
    if (!isset($isCharacter)) {
        $isCharacter = false;
    }
    if (!isset($useCustomSelectize)) {
        $useCustomSelectize = false;
    }

    $rewardableRecipients = ['Character' => 'Character', 'User' => 'User'];
    $recipient = $isCharacter ? 'Character' : 'User';

    // Put any logic for handling 'showXYZ' variables in this array
    $showData = isset($showData)
        ? $showData
        : [
            'isTradeable' => isset($isTradeable) && $isTradeable ? $isTradeable : false,
            'showLootTables' => isset($showLootTables) && $showLootTables ? $showLootTables : false,
            'showRaffles' => isset($showRaffles) && $showRaffles ? $showRaffles : false,
        ];

    // Fetch valid reward types, defined in AssetHelpers
    // This is also called individually for each pre-existing loot row, to fill out the table accurately
    // All available reward or asset types should now be added to getRewardTypes
    $rewardTypes = getRewardTypes($showData, $recipient);

    // Fetch reward data, defined in AssetHelpers
    // All previous code that defines available asset IDs should now be moved to getRewardLootData
    if ($showRecipient) {
        foreach ($rewardableRecipients as $recipient) {
            $rewardLootData[$recipient] = getRewardLootData($showData, $recipient, $useCustomSelectize);
        }
    } else {
        $rewardLootData = getRewardLootData($showData, $recipient, $useCustomSelectize);
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
                            {!! Form::select($prefix . 'rewardable_recipient[]', $rewardableRecipients, $loot->rewardable_recipient ?? $recipient, [
                                'class' => 'form-control recipient-type',
                                'placeholder' => 'Select Recipient Type',
                            ]) !!}
                        </td>
                    @endif

                    <td class="{{ $prefix }}loot-row-type">
                        {{-- The long array of key value pairs is now defined in getRewardTypes and data should be moved there --}}
                        {!! Form::select($prefix . 'rewardable_type[]', getRewardTypes($showData, $loot->rewardable_recipient ?? $recipient), $loot->rewardable_type, [
                            'class' => 'form-control reward-type',
                            'placeholder' => 'Select ' . $type . ' Type',
                        ]) !!}
                    </td>
                    <td class="{{ $prefix }}loot-row-select">
                        {{-- If statements here can be removed and replaced with the below code. They are now defined programmatically --}}
                        {!! Form::select($prefix . 'rewardable_id[]', $showRecipient ? $rewardLootData[$loot->rewardable_recipient ?? $recipient][$loot->rewardable_type] : $rewardLootData[$loot->rewardable_type], $loot->rewardable_id, [
                            'class' => 'form-control ' . strtolower($loot->rewardable_type) . '-select',
                            'placeholder' => 'Select ' . $rewardTypes[$loot->rewardable_type],
                        ]) !!}
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
