@extends('admin.layout')

@section('admin-title')
    {{ $table->id ? 'Edit' : 'Create' }} Loot Table
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Loot Tables' => 'admin/data/loot-tables', ($table->id ? 'Edit' : 'Create') . ' Loot Table' => $table->id ? 'admin/data/loot-tables/edit/' . $table->id : 'admin/data/loot-tables/create']) !!}

    <h1>
        {{ $table->id ? 'Edit' : 'Create' }} Loot Table
        @if ($table->id)
            <a href="#" class="btn btn-danger float-right delete-table-button">Delete Loot Table</a>
        @endif
    </h1>

    {!! Form::open(['url' => $table->id ? 'admin/data/loot-tables/edit/' . $table->id : 'admin/data/loot-tables/create']) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!} {!! add_help('This is the name you will use to identify this table internally. This name will not be shown to users and does not have to be unique, but a name that can be easily identified is recommended.') !!}
        {!! Form::text('name', $table->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Display Name') !!} {!! add_help('This is the name that will be shown to users, for example when displaying the rewards for doing a prompt. This is for display purposes and can be something more vague than the above, e.g. "A Random Rare Item"') !!}
        {!! Form::text('display_name', $table->getRawOriginal('display_name'), ['class' => 'form-control']) !!}
    </div>

    <h3>Loot</h3>

    <p>These are the potential rewards from rolling on this loot table. You can add items, currencies or even another loot table. Chaining multiple loot tables is not recommended, however, and may run the risk of creating an infinite loop. @if (!$table->id)
            You can test loot rolling after the loot table is created.
        @endif
    </p>
    <p>You can add any kind of currencies (both user- and character-attached), but be sure to keep track of which are being distributed! Character-only currencies cannot be given to users.</p>

    <div class="text-right mb-3">
        <a href="#" class="btn btn-info" id="addLoot">Add Loot</a>
    </div>
    <table class="table table-sm" id="lootTable">
        <thead>
            <tr>
                <th width="25%">Loot Type</th>
                <th width="35%">Reward</th>
                <th width="10%">Quantity</th>
                <th width="10%">Weight {!! add_help('A higher weight means a reward is more likely to be rolled. Weights have to be integers above 0 (round positive number, no decimals) and do not have to add up to be a particular number.') !!}</th>
                <th width="10%">Chance</th>
                <th width="10%"></th>
            </tr>
        </thead>
        <tbody id="lootTableBody">
            @if ($table->id)
                @foreach ($table->loot as $loot)
                    <tr class="loot-row">
                        <td>{!! Form::select(
                            'rewardable_type[]',
                            config('lorekeeper.extensions.item_entry_expansion.loot_tables.enable')
                                ? ['Item' => 'Item', 'ItemRarity' => 'Item Rarity', 'Currency' => 'Currency', 'LootTable' => 'Loot Table', 'ItemCategory' => 'Item Category', 'ItemCategoryRarity' => 'Item Category (Conditional)', 'None' => 'None']
                                : ['Item' => 'Item', 'Currency' => 'Currency', 'LootTable' => 'Loot Table', 'ItemCategory' => 'Item Category', 'None' => 'None'],
                            $loot->rewardable_type,
                            ['class' => 'form-control reward-type', 'placeholder' => 'Select Reward Type'],
                        ) !!}</td>
                        <td class="loot-row-select">
                            @if ($loot->rewardable_type == 'Item')
                                {!! Form::select('rewardable_id[]', $items, $loot->rewardable_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                            @elseif($loot->rewardable_type == 'ItemRarity')
                                <div class="item-rarity-select d-flex">
                                    {!! Form::select('criteria[]', ['==' => '=', '<' => '<', '>' => '>', '<=' => '<=', '>=' => '>='], isset($loot->data['criteria']) ? $loot->data['criteria'] : null, ['class' => 'form-control', 'placeholder' => 'Criteria']) !!}
                                    {!! Form::select('rarity[]', $rarities, isset($loot->data['rarity']) ? $loot->data['rarity'] : null, ['class' => 'form-control', 'placeholder' => 'Rarity']) !!}
                                </div>
                            @elseif($loot->rewardable_type == 'Currency')
                                {!! Form::select('rewardable_id[]', $currencies, $loot->rewardable_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                            @elseif($loot->rewardable_type == 'LootTable')
                                {!! Form::select('rewardable_id[]', $tables, $loot->rewardable_id, ['class' => 'form-control table-select selectize', 'placeholder' => 'Select Loot Table']) !!}
                            @elseif($loot->rewardable_type == 'ItemCategoryRarity')
                                <div class="category-rarity-select d-flex">
                                    {!! Form::select('rewardable_id[]', $categories, $loot->rewardable_id, ['class' => 'form-control selectize', 'placeholder' => 'Category']) !!}
                                    {!! Form::select('criteria[' . $loop->index . ']', ['==' => '=', '<' => '<', '>' => '>', '<=' => '<=', '>=' => '>='], isset($loot->data['criteria']) ? $loot->data['criteria'] : null, ['class' => 'form-control', 'placeholder' => 'Criteria']) !!}
                                    {!! Form::select('rarity[' . $loop->index . ']', $rarities, isset($loot->data['rarity']) ? $loot->data['rarity'] : null, ['class' => 'form-control', 'placeholder' => 'Rarity']) !!}
                                </div>
                            @elseif($loot->rewardable_type == 'ItemCategory')
                                {!! Form::select('rewardable_id[]', $categories, $loot->rewardable_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                            @elseif($loot->rewardable_type == 'None')
                                {!! Form::select('rewardable_id[]', [1 => 'No reward given.'], $loot->rewardable_id, ['class' => 'form-control']) !!}
                            @endif
                        </td>
                        <td>{!! Form::text('quantity[]', $loot->quantity, ['class' => 'form-control']) !!}</td>
                        <td class="loot-row-weight">{!! Form::text('weight[]', $loot->weight, ['class' => 'form-control loot-weight']) !!}</td>
                        <td class="loot-row-chance"></td>
                        <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="text-right">
        {!! Form::submit($table->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <div id="lootRowData" class="hide">
        <table class="table table-sm">
            <tbody id="lootRow">
                <tr class="loot-row">
                    <td>{!! Form::select(
                        'rewardable_type[]',
                        config('lorekeeper.extensions.item_entry_expansion.loot_tables.enable')
                            ? ['Item' => 'Item', 'ItemRarity' => 'Item Rarity', 'Currency' => 'Currency', 'LootTable' => 'Loot Table', 'ItemCategory' => 'Item Category', 'ItemCategoryRarity' => 'Item Category (Conditional)', 'None' => 'None']
                            : ['Item' => 'Item', 'Currency' => 'Currency', 'LootTable' => 'Loot Table', 'ItemCategory' => 'Item Category', 'None' => 'None'],
                        null,
                        ['class' => 'form-control reward-type', 'placeholder' => 'Select Reward Type'],
                    ) !!}</td>
                    <td class="loot-row-select"></td>
                    <td>{!! Form::text('quantity[]', 1, ['class' => 'form-control']) !!}</td>
                    <td class="loot-row-weight">{!! Form::text('weight[]', 1, ['class' => 'form-control loot-weight']) !!}</td>
                    <td class="loot-row-chance"></td>
                    <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
                </tr>
            </tbody>
        </table>
        {!! Form::select('rewardable_id[]', $items, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!}
        <div class="item-rarity-select d-flex">
            {!! Form::select('criteria[]', ['==' => '=', '<' => '<', '>' => '>', '<=' => '<=', '>=' => '>='], null, ['class' => 'form-control criteria-select', 'placeholder' => 'Criteria']) !!}
            {!! Form::select('rarity[]', $rarities, null, ['class' => 'form-control criteria-select', 'placeholder' => 'Rarity']) !!}
        </div>
        {!! Form::select('rewardable_id[]', $currencies, null, ['class' => 'form-control currency-select', 'placeholder' => 'Select Currency']) !!}
        {!! Form::select('rewardable_id[]', $tables, null, ['class' => 'form-control table-select', 'placeholder' => 'Select Loot Table']) !!}
        {!! Form::select('rewardable_id[]', $categories, null, ['class' => 'form-control category-select', 'placeholder' => 'Select Item Category']) !!}
        <div class="category-rarity-select d-flex">
            {!! Form::select('rewardable_id[]', $categories, null, ['class' => 'form-control', 'placeholder' => 'Category']) !!}
            {!! Form::select('criteria[]', ['==' => '=', '<' => '<', '>' => '>', '<=' => '<=', '>=' => '>='], null, ['class' => 'form-control criteria-select', 'placeholder' => 'Criteria']) !!}
            {!! Form::select('rarity[]', $rarities, null, ['class' => 'form-control criteria-select', 'placeholder' => 'Rarity']) !!}
        </div>
        {!! Form::select('rewardable_id[]', [1 => 'No reward given.'], null, ['class' => 'form-control none-select']) !!}
    </div>

    @if ($table->id)
        <h3>Test Roll</h3>
        <p>If you have made any modifications to the loot table contents above, be sure to save it (click the Edit button) before testing.</p>
        <p>Please note that due to the nature of probability, as long as there is a chance, there will always be the possibility of rolling improbably good or bad results. <i>This is not indicative of the code being buggy or poor game balance.</i> Be
            cautious when adjusting values based on a small sample size, including but not limited to test rolls and a small amount of user reports.</p>
        <div class="form-group">
            {!! Form::label('quantity', 'Number of Rolls') !!}
            {!! Form::text('quantity', 1, ['class' => 'form-control', 'id' => 'rollQuantity']) !!}
        </div>
        <div class="text-right">
            <a href="#" class="btn btn-primary" id="testRoll">Test Roll</a>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            var $lootTable = $('#lootTableBody');
            var $lootRow = $('#lootRow').find('.loot-row');
            var $itemSelect = $('#lootRowData').find('.item-select');
            var $itemRaritySelect = $('#lootRowData').find('.item-rarity-select');
            var $currencySelect = $('#lootRowData').find('.currency-select');
            var $tableSelect = $('#lootRowData').find('.table-select');
            var $categorySelect = $('#lootRowData').find('.category-select');
            var $categoryRaritySelect = $('#lootRowData').find('.category-rarity-select');
            var $noneSelect = $('#lootRowData').find('.none-select');

            refreshChances();
            $('#lootTableBody .selectize').selectize();
            attachRemoveListener($('#lootTableBody .remove-loot-button'));

            $('.delete-table-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/loot-tables/delete') }}/{{ $table->id }}", 'Delete Loot Table');
            });

            $('#testRoll').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/loot-tables/roll') }}/{{ $table->id }}?quantity=" + $('#rollQuantity').val(), 'Rolling Loot Table');
            });

            $('#addLoot').on('click', function(e) {
                e.preventDefault();
                var $clone = $lootRow.clone();
                $lootTable.append($clone);
                attachRewardTypeListener($clone.find('.reward-type'));
                attachRemoveListener($clone.find('.remove-loot-button'));
                attachWeightListener($clone.find('.loot-weight'));
                refreshChances();
            });

            $('.reward-type').on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.loot-row-select');

                var $clone = null;
                if (val == 'Item') $clone = $itemSelect.clone();
                else if (val == 'ItemRarity') $clone = $itemRaritySelect.clone();
                else if (val == 'Currency') $clone = $currencySelect.clone();
                else if (val == 'ItemCategory') $clone = $categorySelect.clone();
                else if (val == 'ItemCategoryRarity') $clone = $categoryRaritySelect.clone();
                else if (val == 'LootTable') $clone = $tableSelect.clone();
                else if (val == 'None') $clone = $noneSelect.clone();

                $cell.html('');
                $cell.append($clone);
                if (val != 'ItemCategoryRarity' && val != 'ItemRarity') $clone.selectize();
                else {
                    var row_num = $(this).parent().parent().index();
                    $clone.find('[name="rarity[]"]').attr('name', `rarity[${row_num}]`);
                    $clone.find('[name="criteria[]"]').attr('name', `criteria[${row_num}]`);
                }
            });

            function attachRewardTypeListener(node) {
                node.on('change', function(e) {
                    var val = $(this).val();
                    var $cell = $(this).parent().parent().find('.loot-row-select');

                    var $clone = null;
                    if (val == 'Item') $clone = $itemSelect.clone();
                    else if (val == 'ItemRarity') $clone = $itemRaritySelect.clone();
                    else if (val == 'ItemCategory') $clone = $categorySelect.clone();
                    else if (val == 'ItemCategoryRarity') $clone = $categoryRaritySelect.clone();
                    else if (val == 'Currency') $clone = $currencySelect.clone();
                    else if (val == 'LootTable') $clone = $tableSelect.clone();
                    else if (val == 'None') $clone = $noneSelect.clone();

                    $cell.html('');
                    $cell.append($clone);
                    if (val != 'ItemCategoryRarity' && val != 'ItemRarity') $clone.selectize();
                    else {
                        var row_num = $(this).parent().parent().index();
                        $clone.find('[name="rarity[]"]').attr('name', `rarity[${row_num}]`);
                        $clone.find('[name="criteria[]"]').attr('name', `criteria[${row_num}]`);
                    }
                });
            }

            function attachRemoveListener(node) {
                node.on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().remove();
                    refreshChances();
                });
            }

            function attachWeightListener(node) {
                node.on('change', function(e) {
                    refreshChances();
                });
            }

            function refreshChances() {
                var total = 0;
                var weights = [];
                $('#lootTableBody .loot-weight').each(function(index) {
                    var current = parseInt($(this).val());
                    total += current;
                    weights.push(current);
                });


                $('#lootTableBody .loot-row-chance').each(function(index) {
                    var current = (weights[index] / total) * 100;
                    $(this).html(current.toString() + '%');
                });
            }
        });
    </script>
@endsection
