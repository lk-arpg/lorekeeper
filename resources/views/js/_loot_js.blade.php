<script>
    $(document).ready(function() {
        var $lootTable = $('#lootTableBody');
        var $lootRow = $('#lootRow').find('.loot-row');
        var $itemSelect = $('#lootRowData').find('.item-select');
        var $currencySelect = $('#lootRowData').find('.currency-select');
        @if ($showLootTables)
            var $tableSelect = $('#lootRowData').find('.table-select');
        @endif
        @if ($showRaffles)
            var $raffleSelect = $('#lootRowData').find('.raffle-select');
        @endif

        @if (isset($useCustomSelectize) && $useCustomSelectize)
            $('#lootTableBody .selectize').selectize({
                render: {
                    option: customLootSelectizeRender,
                    item: customLootSelectizeRender
                }
            });
        @else
            $('#lootTableBody .selectize').selectize();
        @endif
        attachRemoveListener($('#lootTableBody .remove-loot-button'));

        $('#addLoot').on('click', function(e) {
            e.preventDefault();
            var $clone = $lootRow.clone();
            $lootTable.append($clone);
            attachRewardTypeListener($clone.find('.reward-type'));
            attachRemoveListener($clone.find('.remove-loot-button'));
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

        function customLootSelectizeRender(item, escape) {
            console.log(item);
            item = JSON.parse(item.text);
            console.log(item);
            option_render = '<div class="option">';
            if (item['image_url']) {
                option_render += '<div class="d-inline mr-1"><img class="small-icon" alt="' + escape(item['name']) + '" src="' + escape(item['image_url']) + '"></div>';
            }
            option_render += '<span>' + escape(item['name']) + '</span></div>';
            return option_render;
        }
    });
</script>
