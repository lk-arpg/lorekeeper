@php
    $characters = \App\Models\Character\Character::visible(Auth::user() ?? null)
        ->myo(0)
        ->orderBy('slug', 'DESC')
        ->get()
        ->pluck('fullName', 'slug')
        ->toArray();
    $tables = \App\Models\Loot\LootTable::orderBy('name')->pluck('name', 'id');
@endphp

<div class="submission-character mb-3 card">
    <div class="card-body">
        <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a></div>
        <div class="row">
            <div class="col-md-2 align-items-stretch d-flex">
                <div class="d-flex text-center align-items-center">
                    <div class="character-image-blank hide">Enter character code.</div>
                    <div class="character-image-loaded">
                        @include('home._character', ['character' => $character->character ? $character->character : $character])
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <div class="form-group">
                    {!! Form::label('slug[]', 'Character Code') !!}
                    {!! Form::select('slug[]', $characters, $character->character ? $character->character->slug : $character->slug, ['class' => 'form-control character-code', 'placeholder' => 'Select Character']) !!}
                </div>
                <div class="character-rewards">
                    <h4>Character Rewards</h4>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                @if ($expanded_rewards)
                                    <th width="35%">Reward Type</th>
                                    <th width="35%">Reward</th>
                                @else
                                    <th width="70%">Reward</th>
                                @endif
                                <th width="30%">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="character-rewards">
                            @foreach ($character->rewards ?? [] as $reward)
                                <tr class="character-reward-row">
                                    @if ($expanded_rewards)
                                        <td>
                                            {!! Form::select('character_rewardable_type[' . $character->character_id . '][]', ['Item' => 'Item', 'Currency' => 'Currency', 'LootTable' => 'Loot Table'], $reward->rewardable_type, [
                                                'class' => 'form-control character-rewardable-type',
                                                'placeholder' => 'Select Reward Type',
                                            ]) !!}
                                        </td>
                                        <td class="lootDivs">
                                            <div class="character-currencies  {{ $reward->rewardable_type == 'Currency' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $characterCurrencies, $reward->rewardable_type == 'Currency' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-currency-id',
                                                'placeholder' => 'Select Currency',
                                            ]) !!}</div>
                                            <div class="character-items  {{ $reward->rewardable_type == 'Item' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $items, $reward->rewardable_type == 'Item' ? $reward->rewardable_id : null, ['class' => 'form-control character-item-id', 'placeholder' => 'Select Item']) !!}</div>
                                            <div class="character-tables {{ $reward->rewardable_type == 'Loot Table' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $tables, $reward->rewardable_type == 'Loot Table' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-table-id',
                                                'placeholder' => 'Select Loot Table',
                                            ]) !!}</div>
                                        </td>
                                    @else
                                        <td class="lootDivs">
                                            {!! Form::hidden('character_rewardable_type[' . $character->character_id . '][]', 'Currency', ['class' => 'character-rewardable-type']) !!}
                                            {!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $characterCurrencies, $reward->rewardable_type == 'Currency' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-currency-id',
                                                'placeholder' => 'Select Currency',
                                            ]) !!}
                                        </td>
                                    @endif
                                    <td class="d-flex align-items-center">
                                        {!! Form::number('character_rewardable_quantity[' . $character->character_id . '][]', $reward->quantity, ['class' => 'form-control mr-2 character-rewardable-quantity']) !!}
                                        <a href="#" class="remove-reward d-block"><i class="fas fa-times text-muted"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-right">
                        <a href="#" class="btn btn-outline-primary btn-sm add-reward">Add Reward</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
