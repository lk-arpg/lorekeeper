<script>
    $(document).ready(function() {
        var $itemIdFilter = $('#itemIdFilter');
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
        $('.clear-item-filter').on('click', function(e) {
            e.preventDefault();
            $itemIdFilter[0].selectize.setValue(null);
        });

        var $userItemCategory = $('#userItemCategory');
        $userItemCategory.on('change', function(e) {
            refreshFilter();
        });
        $('.inventory-select-all').on('click', function(e) {
            e.preventDefault();
            selectVisible();
        });
        $('.inventory-clear-selection').on('click', function(e) {
            e.preventDefault();
            deselectVisible();
        });
        $('.inventory-checkbox').on('change', function() {
            $checkbox = $(this);
            var rowId = "#itemRow" + $checkbox.val()
            if ($checkbox.is(":checked")) {
                $(rowId).addClass('category-selected');
                $(rowId).find('.quantity-select').prop('name', 'stack_quantity[' + $checkbox.val() + ']')
            } else {
                $(rowId).removeClass('category-selected');
                $(rowId).find('.quantity-select').prop('name', '')
            }
        });
        $('#toggle-checks').on('click', function() {
            ($(this).is(":checked")) ? selectVisible(): deselectVisible();
        });

        function refreshFilter() {
            var display = $userItemCategory.val();
            var itemId = $itemIdFilter.val();
            $('.user-item').addClass('hide');
            $('.user-item.category-' + display + '.item-' + (itemId ? itemId : 'all')).removeClass('hide');
            $('#toggle-checks').prop('checked', false);
        }

        function selectVisible() {
            var $target = $('.user-item:not(.hide)');
            $target.find('.inventory-checkbox').prop('checked', true);
            $target.find('.inventory-checkbox').trigger('change');
            $('#toggle-checks').prop('checked', true);
        }

        function deselectVisible() {
            var $target = $('.user-item:not(.hide)');
            $target.find('.inventory-checkbox').prop('checked', false);
            $target.find('.inventory-checkbox').trigger('change');
            $('#toggle-checks').prop('checked', false);
            $target.find('.quantity-select').prop('name', '');
        }

        function customItemSelectizeRender(item, escape) {
            item = JSON.parse(item.text);
            option_render = '<div class="option">';
            if (item['image_url']) {
                option_render += '<div class="d-inline me-1"><img class="small-icon" alt="' + escape(item['name']) + '" src="' + escape(item['image_url']) + '"></div>';
            }
            option_render += '<span>' + escape(item['name']) + '</span></div>';
            return option_render;
        }
    });
</script>
