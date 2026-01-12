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
    $rewardTypes = getRewardTypes($showData, $recipient);

    // Fetch reward data, defined in AssetHelpers
    // All previous code that defines available asset IDs should now be moved to getRewardLootData
    // Get the character specific loot availability if the recipient is being shown
    if ($showRecipient) {
        foreach ($rewardableRecipients as $recipient) {
            $rewardLootData[$recipient] = getRewardLootData($showData, $recipient, $useCustomSelectize);
        }
    } else {
        $rewardLootData = getRewardLootData($showData, $recipient, $useCustomSelectize);
    }
@endphp

<div id="{{ $prefix }}lootRowData" class="hide">
    <table class="table table-sm">
        <tbody id="{{ $prefix }}lootRow">
            <tr class="loot-row">
                @if ($showRecipient)
                    <td>
                        {!! Form::select($prefix . 'rewardable_recipient[]', $rewardableRecipients, $recipient, [
                            'class' => 'form-control recipient-type',
                            'placeholder' => 'Select Recipient Type',
                        ]) !!}
                    </td>
                @endif
                <td class="{{ $prefix }}loot-row-type">
                    {{-- The long array of key value pairs is now defined in getRewardTypes and data should be moved there --}}
                    {!! Form::select($prefix . 'rewardable_type[]', $rewardTypes, null, [
                        'class' => 'form-control reward-type',
                        'placeholder' => 'Select ' . $type . ' Type',
                    ]) !!}
                </td>
                <td class="{{ $prefix }}loot-row-select"></td>
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
    {{-- If statements here can be removed and replaced with the below code. They are now defined programmatically --}}
    @if ($showRecipient)
        @foreach ($rewardableRecipients as $recipient)
            <div class="rewardable-ids-{{ strtolower($recipient) }}">
                @foreach (getRewardTypes($showData, $recipient) as $rewardKey => $rewardType)
                    {!! Form::select($prefix . 'rewardable_id[]', $rewardLootData[$recipient][$rewardKey], null, ['class' => 'form-control object-select ' . strtolower($rewardKey) . '-select', 'placeholder' => 'Select ' . $rewardType]) !!}
                @endforeach
            </div>
        @endforeach
    @else
        @foreach ($rewardTypes as $rewardKey => $rewardType)
            {!! Form::select($prefix . 'rewardable_id[]', $rewardLootData[$rewardKey], null, ['class' => 'form-control object-select ' . strtolower($rewardKey) . '-select', 'placeholder' => 'Select ' . $rewardType]) !!}
        @endforeach
    @endif
</div>
