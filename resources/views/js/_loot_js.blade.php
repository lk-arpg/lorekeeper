@php
    $prefix = $prefix ?? '';
@endphp
<script>
    $(document).ready(function() {
        var $lootTable = $('#{{ $prefix }}lootTableBody');
        var $lootRow = $('#{{ $prefix }}lootRow').find('.{{ $prefix }}loot-row');
        var $itemSelect = $('#{{ $prefix }}lootRowData').find('.{{ $prefix }}item-select');
        var $currencySelect = $('#{{ $prefix }}lootRowData').find('.{{ $prefix }}currency-select');

        @if ($showLootTables)
            var $tableSelect = $('#{{ $prefix }}lootRowData').find('.{{ $prefix }}table-select');
        @endif
        @if (!isset($isCharacter))
            @if ($showRaffles)
                var $raffleSelect = $('#{{ $prefix }}lootRowData').find('.{{ $prefix }}raffle-select');
            @endif
        @endif

        $('#{{ $prefix }}lootTableBody .selectize').selectize();
        attachRemoveListener($('#{{ $prefix }}lootTableBody .{{ $prefix }}remove-loot-button'));

        $('#{{ $prefix }}addLoot').on('click', function(e) {
            e.preventDefault();
            var $clone = $lootRow.clone();
            $lootTable.append($clone);
            attachRewardTypeListener($clone.find('.{{ $prefix }}reward-type'));
            attachRemoveListener($clone.find('.{{ $prefix }}remove-loot-button'));
        });

        $('.{{ $prefix }}reward-type').on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().parent().find('.{{ $prefix }}loot-row-select');

            var $clone = null;
            if (val == 'Item') $clone = $itemSelect.clone();
            else if (val == 'Currency') $clone = $currencySelect.clone();
            @if ($showLootTables)
                else if (val == 'LootTable') $clone = $tableSelect.clone();
            @endif
            @if (!isset($isCharacter))
                @if ($showRaffles)
                    else if (val == 'Raffle') $clone = $raffleSelect.clone();
                @endif
            @endif

            $cell.html('');
            $cell.append($clone);
        });

        function attachRewardTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.{{ $prefix }}loot-row-select');

                var $clone = null;
                if (val == 'Item') $clone = $itemSelect.clone();
                else if (val == 'Currency') $clone = $currencySelect.clone();
                @if ($showLootTables)
                    else if (val == 'LootTable') $clone = $tableSelect.clone();
                @endif
                @if (!isset($isCharacter))
                    @if ($showRaffles)
                        else if (val == 'Raffle') $clone = $raffleSelect.clone();
                    @endif
                @endif

                $cell.html('');
                $cell.append($clone);
                $clone.selectize();
            });
        }

        function attachRemoveListener(node) {
            node.on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });
        }

    });
</script>
