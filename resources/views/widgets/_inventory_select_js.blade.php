@php
    if (!isset($fieldPrefix)) {
        $fieldPrefix = '';
    }
@endphp
<script>
    $(document).ready(function() {
        var $itemIdFilter = $('#{{ $fieldPrefix }}itemIdFilter');
        $itemIdFilter.selectize({
            maxOptions: 10,
            render: {
                option: customItemSelectizeRender,
                item: customItemSelectizeRender
            }
        });
        $itemIdFilter.on('change', function(e) {
            refreshFilter();
        });
        $('.{{ $fieldPrefix }}clear-item-filter').on('click', function(e) {
            e.preventDefault();
            $itemIdFilter[0].selectize.setValue(null);
        });

        var $userItemCategory = $('#{{ $fieldPrefix }}userItemCategory');
        $userItemCategory.on('change', function(e) {
            refreshFilter();
        });
        $('.{{ $fieldPrefix }}inventory-select-all').on('click', function(e) {
            e.preventDefault();
            selectVisible();
        });
        $('.{{ $fieldPrefix }}inventory-clear-selection').on('click', function(e) {
            e.preventDefault();
            deselectVisible();
        });
        $('.{{ $fieldPrefix }}inventory-checkbox').on('change', function() {
            $checkbox = $(this);
            var rowId = "#{{ $fieldPrefix }}itemRow" + $checkbox.val()
            if ($checkbox.is(":checked")) {
                $(rowId).addClass('category-selected');
                $(rowId).find('.quantity-select').prop('name', 'stack_quantity[' + $checkbox.val() + ']')
            } else {
                $(rowId).removeClass('category-selected');
                $(rowId).find('.quantity-select').prop('name', '')
            }
        });
        $('#{{ $fieldPrefix }}toggle-checks').on('click', function() {
            ($(this).is(":checked")) ? selectVisible(): deselectVisible();
        });

        function refreshFilter() {
            var display = $userItemCategory.val();
            var itemId = $itemIdFilter.val();
            $('.{{ $fieldPrefix }}user-item').addClass('hide');
            $('.{{ $fieldPrefix }}user-item.category-' + display + '.item-' + (itemId ? itemId : 'all')).removeClass('hide');
            $('#{{ $fieldPrefix }}toggle-checks').prop('checked', false);
        }

        function selectVisible() {
            var $target = $('.{{ $fieldPrefix }}user-item:not(.hide)');
            $target.find('.{{ $fieldPrefix }}inventory-checkbox').prop('checked', true);
            $target.find('.{{ $fieldPrefix }}inventory-checkbox').trigger('change');
            $('#{{ $fieldPrefix }}toggle-checks').prop('checked', true);
        }

        function deselectVisible() {
            var $target = $('.{{ $fieldPrefix }}user-item:not(.hide)');
            $target.find('.{{ $fieldPrefix }}inventory-checkbox').prop('checked', false);
            $target.find('.{{ $fieldPrefix }}inventory-checkbox').trigger('change');
            $('#{{ $fieldPrefix }}toggle-checks').prop('checked', false);
            $target.find('.{{ $fieldPrefix }}quantity-select').prop('name', '');
        }

        function customItemSelectizeRender(item, escape) {
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
