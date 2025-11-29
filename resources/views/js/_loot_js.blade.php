@php
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
@endphp
<script>
    $(document).ready(function() {
        var $lootTable = $('#{{ $prefix }}lootTableBody');
        var $lootRow = $('#{{ $prefix }}lootRow').find('.loot-row');
        console.log($lootRow);
        var $itemSelect = $('#{{ $prefix }}lootRowData').find('.item-select');
        var $currencySelect = $('#{{ $prefix }}lootRowData').find('.currency-select');
        @if ($showLootTables)
            var $tableSelect = $('#{{ $prefix }}lootRowData').find('.table-select');
        @endif
        @if ($showRaffles)
            var $raffleSelect = $('#{{ $prefix }}lootRowData').find('.raffle-select');
        @endif

        @if (isset($useCustomSelectize) && $useCustomSelectize)
            $('#{{ $prefix }}lootTableBody .selectize').selectize({
                render: {
                    option: customLootSelectizeRender,
                    item: customLootSelectizeRender
                }
            });
        @else
            $('#{{ $prefix }}lootTableBody .selectize').selectize();
        @endif
        attachRemoveListener($('#{{ $prefix }}lootTableBody .remove-loot-button'));

        $('#{{ $prefix }}addLoot').on('click', function(e) {
            e.preventDefault();
            var $clone = $lootRow.clone();
            console.log($clone);
            $lootTable.append($clone);
            attachRewardTypeListener($clone.find('.reward-type'));
            attachRemoveListener($clone.find('.remove-loot-button'));
            if ($clone.find('.loot-weight').length) {
                attachWeightListener($clone.find('.loot-weight'));
                refreshChances();
            }
        });

        $('.reward-type').on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().parent().find('.loot-row-select');

            var $clone = null;
            if (val == 'Item') $clone = $itemSelect.clone();
            else if (val == 'Currency') $clone = $currencySelect.clone();
            @if ($showLootTables)
                else if (val == 'LootTable') $clone = $tableSelect.clone();
            @endif
            @if ($showRaffles)
                else if (val == 'Raffle') $clone = $raffleSelect.clone();
            @endif

            $cell.html('');
            $cell.append($clone);
        });

        function attachRewardTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.loot-row-select');

                var $clone = null;
                if (val == 'Item') $clone = $itemSelect.clone();
                else if (val == 'Currency') $clone = $currencySelect.clone();
                @if ($showLootTables)
                    else if (val == 'LootTable') $clone = $tableSelect.clone();
                @endif
                @if ($showRaffles)
                    else if (val == 'Raffle') $clone = $raffleSelect.clone();
                @endif

                $cell.html('');
                $cell.append($clone);
                @if (isset($useCustomSelectize) && $useCustomSelectize)
                    $clone.selectize({
                        render: {
                            option: customLootSelectizeRender,
                            item: customLootSelectizeRender
                        }
                    });
                @else
                    $clone.selectize();
                @endif
            });
        }

        function attachRemoveListener(node) {
            node.on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });
        }

        // Weight is a special field
        // check if there is a '.loot-weight' element
        if ($('#{{ $prefix }}lootTableBody .loot-weight').length) {
            refreshChances();
            attachWeightListener($('#{{ $prefix }}lootTableBody .loot-weight'));
        }

        function attachWeightListener(node) {
            node.on('change', function(e) {
                refreshChances();
            });
        }

        function refreshChances() {
            var total = 0;
            var weights = [];
            $('#{{ $prefix }}lootTableBody .loot-weight').each(function(index) {
                var current = parseInt($(this).val());
                total += current;
                weights.push(current);
            });


            $('#{{ $prefix }}lootTableBody .loot-row-chance').each(function(index) {
                var current = (weights[index] / total) * 100;
                $(this).html(current.toString() + '%');
            });
        }

        function customLootSelectizeRender(item, escape) {
            item = JSON.parse(item.text);
            option_render = '<div class="option">';
            if (item['image_url']) {
                option_render += '<div class="d-inline mr-1"><img class="small-icon" alt="' + escape(item['name']) + '" src="' + escape(item['image_url']) + '"></div>';
            }
            option_render += '<span>' + escape(item['name']) + '</span></div>';
            return option_render;
        }
    });
</script>
