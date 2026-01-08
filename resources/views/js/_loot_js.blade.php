@php
    if (!isset($type)) {
        $type = 'Reward';
    }
    if (!isset($prefix)) {
        $prefix = '';
    }
    if (!isset($useCustomSelectize)) {
        $useCustomSelectize = false;
    }

    // Put any logic for handling 'showXYZ' variables in this array
    $showData = [
        'isTradeable' => isset($isTradeable) && $isTradeable ? $isTradeable : false,
        'showLootTables' => isset($showLootTables) && $showLootTables ? $showLootTables : false,
        'showRaffles' => isset($showRaffles) && $showRaffles ? $showRaffles : false,
    ];
@endphp
<script>
    $(document).ready(function() {
        var $lootTable = $('#{{ $prefix }}lootTableBody');
        var $lootRow = $('#{{ $prefix }}lootRow').find('.loot-row');
        //The clone "itemSelect" etc variables here are no longer necessary, and can be deleted

        @if ($useCustomSelectize)
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
            $lootTable.append($clone);
            attachRewardTypeListener($clone.find('.reward-type'));
            attachRewardRecipientListener($clone.find('.recipient-type'));
            attachRemoveListener($clone.find('.remove-loot-button'));
            if ($clone.find('.loot-weight').length) {
                attachWeightListener($clone.find('.loot-weight'));
                refreshChances();
            }
        });

        $('.recipient-type').on('change', function(e) {
            var $rewardTypeCell = $(this).parent().parent().find('.{{ $prefix }}loot-row-type');
            var $rewardIdsCell = $(this).parent().parent().find('.{{ $prefix }}loot-row-select');
            var $recipient = $(this).val();

            //Update the lootRow with the new types
            $.ajax({
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                url: "{{ url('rewards/types') }}",
                data: {
                    recipient: $recipient,
                    prefix: '{{ $prefix }}',
                    type: '{{ $type }}',
                    showData: JSON.parse('{!! json_encode($showData) !!}'),
                    useCustomSelectize: '{{ $useCustomSelectize }}'
                },
                dataType: "text"
            }).done(function(res) {
                $rewardTypeCell.html(res);
                attachRewardTypeListener($rewardTypeCell.find('.reward-type'));
                $rewardIdsCell.html('');
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        });

        $('.reward-type').on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().parent().find('.{{ $prefix }}loot-row-select');
            var $recipient = $(this).parent().parent().find('.recipient-type').val();

            console.log($recipient);

            //All if statements here are replaced with the following line
            var $clone = cloneRewardableId(val, $recipient);

            $cell.html('');
            $cell.append($clone);
        });

        function attachRewardRecipientListener(node) {
            node.on('change', function(e) {
                var $rewardTypeCell = $(this).parent().parent().find('.{{ $prefix }}loot-row-type');
                var $rewardIdsCell = $(this).parent().parent().find('.{{ $prefix }}loot-row-select');
                var $recipient = $(this).val();

                //Update the lootRow with the new types
                $.ajax({
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    url: "{{ url('rewards/types') }}",
                    data: {
                        recipient: $recipient,
                        prefix: '{{ $prefix }}',
                        type: '{{ $type }}',
                        showData: JSON.parse('{!! json_encode($showData) !!}'),
                        useCustomSelectize: '{{ $useCustomSelectize }}'
                    },
                    dataType: "text"
                }).done(function(res) {
                    $rewardTypeCell.html(res);
                    attachRewardTypeListener($rewardTypeCell.find('.reward-type'));
                    $rewardIdsCell.html('');
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert("AJAX call failed: " + textStatus + ", " + errorThrown);
                });
            });
        }

        function attachRewardTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.{{ $prefix }}loot-row-select');
                var $recipient = $(this).parent().parent().find('.recipient-type').val();

                console.log($recipient);

                //If statements here are replaced with the following line
                var $clone = cloneRewardableId(val, $recipient);

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

        //The below replaces any "clone" if statements
        function cloneRewardableId(val, recipient = null) {
            if (recipient != null) {
                return $('#{{ $prefix }}lootRowData').find('.rewardable-ids-' + recipient.toLowerCase()).find('.' + val.toLowerCase() + '-select').clone();
            } else {
                return $('#{{ $prefix }}lootRowData').find('.' + val.toLowerCase() + '-select').clone();
            }
        }
    });
</script>
