<div class="p-2">
    @if ($stock->id)
        {!! Form::open(['url' => 'admin/data/shops/stock/edit/' . $stock->id]) !!}
    @else
        {!! Form::open(['url' => 'admin/data/shops/stock/' . $shop->id]) !!}
    @endif

    <h5>Stock</h5>
    <p>
        Random stock will select a random item from the list below on creation / edit.
        <br />
        <b>If a restock period is set and the stock is set to "random", it will select a new random stock of the chosen type.</b>
        <br />
        <b>If a category exists for the chosen stock type, it can be used as a random filter.</b>
    </p>
    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('stock_type', 'Type') !!}
            {!! Form::select('stock_type', ['Item' => 'Item'], $stock->stock_type ?? null, ['class' => 'form-control stock-field', 'placeholder' => 'Select Stock Type', 'id' => 'type']) !!}
        </div>
        <div class="col-md-6 form-group" id="stock">
            @if ($stock->id)
                @include('admin.shops._stock_item', ['items' => $items, 'stock' => $stock])
            @endif
        </div>
    </div>

    <h5>Costs</h5>
    <p>
        You can select multiple costs for the item. Setting no costs will make the item free.
        <br />
        <b>By default, all costs are required to purchase the item unless they are assigned seperate groups.</b>
    </p>
    <div class="mb-3">
        <div class="text-right">
            <div class="btn btn-primary mb-2" id="addCost">
                Add Cost
            </div>
        </div>
        <div id="costs">
            <div class="text-center row no-gutters border-bottom mb-2">
                <div class="col-3">Cost Type</div>
                <div class="col-4">Cost Object</div>
                <div class="col-2">Quantity</div>
                <div class="col-2">
                    Group
                    {!! add_help('You can group costs together to allow users to choose which group they want to pay with.') !!}
                </div>
            </div>
            @foreach ($stock->costs ?? [] as $cost)
                <div class="row mb-3">
                    <div class="col-3">
                        {!! Form::select(
                            'cost_type[]',
                            [
                                'Currency' => 'Currency',
                                'Item' => 'Item',
                            ],
                            $cost->cost_type ?? null,
                            ['class' => 'form-control cost-type', 'placeholder' => 'Select Cost Type'],
                        ) !!}
                    </div>
                    <div class="col-4 costObjects">
                        @include('admin.shops._stock_cost', [
                            'cost' => $cost,
                            'costItems' => $cost->items,
                        ])
                    </div>
                    <div class="col-2">
                        {!! Form::number('cost_quantity[]', $cost->quantity ?? 1, ['class' => 'form-control', 'min' => 1]) !!}
                    </div>
                    <div class="col-2">
                        {!! Form::number('group[]', $cost->group ?? 1, ['class' => 'form-control', 'min' => 1]) !!}
                    </div>
                    <div class="col-1">
                        <div class="btn btn-danger removeCost">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <hr />

    <h5>Coupon Usage</h5>
    @if ($shop->use_coupons)
        <p>You can set which groups can use coupons on this stock. Note that you must do this after creating the stock and groups!</p>
        @if ($stock->id)
            @foreach ($stock->groups ?? [] as $group)
                <div class="form-group">
                    {!! Form::checkbox('can_group_use_coupon[' . $group . ']', 1, $stock->canGroupUseCoupons($group), ['class' => 'form-check-input stock-field', 'data-toggle' => 'checkbox']) !!}
                    {!! Form::label('can_group_use_coupon[' . $group . ']', 'Allow group #' . $group . ' to use coupons', ['class' => 'form-check-label ml-3']) !!}
                </div>
            @endforeach
        @else
            <div class="alert alert-info">You must create the stock before setting coupon usage.</div>
        @endif
    @else
        <div class="alert alert-info">Coupons are disabled on this shop.</div>
    @endif

    <hr />

    <div class="row mb-3">
        <div class="col-md-6">
            {!! Form::label('purchase_limit', 'User Purchase Limit') !!} {!! add_help('This is the maximum amount of this item a user can purchase from this shop. Set to 0 to allow infinite purchases.') !!}
            {!! Form::number('purchase_limit', $stock ? $stock->purchase_limit : 0, ['class' => 'form-control stock-field', 'data-name' => 'purchase_limit']) !!}
        </div>
        <div class="col-md-6">
            {!! Form::label('purchase_limit_timeframe', 'Purchase Limit Timeout') !!} {!! add_help('This is the timeframe that the purchase limit will apply to. I.E. yearly will only look at purchases made after the beginning of the current year. Weekly starts on Sunday. Rollover will happen on UTC time.') !!}
            {!! Form::select('purchase_limit_timeframe', ['lifetime' => 'Lifetime', 'yearly' => 'Yearly', 'monthly' => 'Monthly', 'weekly' => 'Weekly', 'daily' => 'Daily'], $stock ? $stock->purchase_limit_timeframe : 0, [
                'class' => 'form-control stock-field',
                'data-name' => 'purchase_limit_timeframe',
                'placeholder' => 'Select Timeframe',
            ]) !!}
        </div>
    </div>

    <div class="row no-gutters">
        <div class="col-md-6 form-group">
            {!! Form::checkbox('use_user_bank', 1, $stock->use_user_bank ?? 1, ['class' => 'form-check-input stock-toggle stock-field', 'data-toggle' => 'checkbox', 'data-name' => 'use_user_bank']) !!}
            {!! Form::label('use_user_bank', 'Use User Bank', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will allow users to purchase the item using the currency in their accounts, provided that users can own that currency.') !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('use_character_bank', 1, $stock->use_character_bank ?? 1, ['class' => 'form-check-input stock-toggle stock-field', 'data-toggle' => 'checkbox', 'data-name' => 'use_character_bank']) !!}
            {!! Form::label('use_character_bank', 'Use Character Bank', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will allow users to purchase the item using the currency belonging to characters they own, provided that characters can own that currency.') !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('is_fto', 1, $stock->is_fto ?? 0, ['class' => 'form-check-input stock-toggle stock-field', 'data-toggle' => 'checkbox', 'data-name' => 'is_fto']) !!}
            {!! Form::label('is_fto', 'FTO Only?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, only FTO will be able to purchase the item.') !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('disallow_transfer', 1, $stock->disallow_transfer ?? 0, ['class' => 'form-check-input stock-toggle stock-field', 'data-name' => 'disallow_transfer']) !!}
            {!! Form::label('disallow_transfer', 'Disallow Transfer', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, users will be unable to transfer this item after purchase.') !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $stock->is_visible ?? 1, ['class' => 'form-check-input stock-limited stock-toggle stock-field', 'data-toggle' => 'checkbox']) !!}
        {!! Form::label('is_visible', 'Set Visibility', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off it will not appear in the store.') !!}
    </div>
    <div class="form-group">
        {!! Form::checkbox('is_limited_stock', 1, $stock->is_limited_stock ?? 0, ['class' => 'form-check-input stock-limited stock-toggle stock-field', 'data-toggle' => 'checkbox', 'id' => 'is_limited_stock']) !!}
        {!! Form::label('is_limited_stock', 'Set Limited Stock', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, will limit the amount purchaseable to the quantity set below.') !!}
    </div>

    <div class="card mb-3 stock-limited-quantity {{ $stock->is_limited_stock ? '' : 'hide' }}">
        <div class="card-body">
            <div>
                {!! Form::label('quantity', 'Quantity') !!} {!! add_help('If left blank, will be set to 0 (sold out).') !!}
                {!! Form::text('quantity', $stock->quantity ?? 0, ['class' => 'form-control stock-field']) !!}
            </div>
            <div class="my-2">
                {!! Form::checkbox('restock', 1, $stock->restock ?? 0, ['class' => 'form-check-input', 'data-toggle' => 'checkbox']) !!}
                {!! Form::label('restock', 'Auto Restock?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If ticked to yes it will auto restock at the interval defined below.') !!}
            </div>
            <div>
                {!! Form::label('restock_interval', 'Restock Interval') !!}
                {!! Form::select('restock_interval', [1 => 'Day', 2 => 'Week', 3 => 'Month'], $stock->restock_interval ?? 2, ['class' => 'form-control stock-field']) !!}
            </div>
            <div class="my-2">
                {!! Form::checkbox('range', 1, $stock->range ?? 0, ['class' => 'form-check-input', 'data-toggle' => 'checkbox']) !!}
                {!! Form::label('range', 'Restock in Range?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If ticked to yes it will restock a random quantity between 1 and the quantity set above.') !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_timed_stock', 1, $stock->is_timed_stock ?? 0, ['class' => 'form-check-input stock-timed stock-toggle stock-field', 'data-toggle' => 'checkbox', 'id' => 'is_timed_stock']) !!}
        {!! Form::label('is_timed_stock', 'Set Timed Stock', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Sets the stock as timed between the chosen dates.') !!}
    </div>
    <div class="card stock-timed-quantity {{ $stock->is_timed_stock ? '' : 'hide' }}">
        <div class="card-body">
            <h3>Stock Time Period</h3>
            <p>Both of the below options can work together. If both are set, the stock will only be available during the specific time period, and on the specific days of the week and months.</p>

            <h5>Specific Time Period</h5>
            <p>The time period below is between the specific dates and times, rather than an agnostic period like "every November".</p>
            <div class="row">
                <div class="col-md-6 form-group">
                    {!! Form::label('stock_start_at', 'Start Time') !!} {!! add_help('Stock will cycle in at this date.') !!}
                    {!! Form::text('stock_start_at', $stock->start_at, ['class' => 'form-control datepicker']) !!}
                </div>
                <div class="col-md-6 form-group">
                    {!! Form::label('stock_end_at', 'End Time') !!} {!! add_help('Stock will cycle out at this date.') !!}
                    {!! Form::text('stock_end_at', $stock->end_at, ['class' => 'form-control datepicker']) !!}
                </div>
            </div>

            <h5>Repeating Time Period</h5>
            <p>Select the months and days of the week that the stock will be available.</p>
            <p><b>If months are set alongside days, the stock will only be available on those days in those months.</b></p>
            <div class="form-group">
                {!! Form::label('stock_days', 'Days of the Week') !!}
                {!! Form::select('stock_days[]', ['Monday' => 'Monday', 'Tuesday' => 'Tuesday', 'Wednesday' => 'Wednesday', 'Thursday' => 'Thursday', 'Friday' => 'Friday', 'Saturday' => 'Saturday', 'Sunday' => 'Sunday'], $stock->days ?? null, [
                    'class' => 'form-control selectize',
                    'multiple' => 'multiple',
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('stock_months', 'Months of the Year') !!}
                {!! Form::select(
                    'stock_months[]',
                    [
                        'January' => 'January',
                        'February' => 'February',
                        'March' => 'March',
                        'April' => 'April',
                        'May' => 'May',
                        'June' => 'June',
                        'July' => 'July',
                        'August' => 'August',
                        'September' => 'September',
                        'October' => 'October',
                        'November' => 'November',
                        'December' => 'December',
                    ],
                    $stock->months ?? null,
                    ['class' => 'form-control selectize', 'multiple' => 'multiple'],
                ) !!}
            </div>
        </div>
    </div>

    <div class="text-right mt-1">
        {!! Form::submit($stock->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}

    <div class="form-group hide original cost-row">
        <div class="row">
            <div class="col-3">
                {!! Form::select(
                    'cost_type[]',
                    [
                        'Currency' => 'Currency',
                        'Item' => 'Item',
                    ],
                    null,
                    ['class' => 'form-control cost-type', 'placeholder' => 'Select Cost Type'],
                ) !!}
            </div>
            <div class="col-4 costObjects">
                Select Cost Type
            </div>
            <div class="col-2">
                {!! Form::number('cost_quantity[]', 1, ['class' => 'form-control', 'min' => 1]) !!}
            </div>
            <div class="col-2">
                {!! Form::number('group[]', 1, ['class' => 'form-control', 'min' => 1]) !!}
            </div>
            <div class="col-1">
                <div class="btn btn-danger removeCost">
                    <i class="fas fa-times"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.selectize').selectize();

        // foreach .form-check-input
        $('.form-check-input').each(function() {
            $(this).attr('data-toggle', 'toggle').bootstrapToggle();
        });

        // add remove cost listener to all removeCost buttons
        $('.removeCost').each(function() {
            addRemoveCostListener($(this));
        });

        // add cost change listener to all cost-type selects
        $('.cost-type').each(function() {
            addCostChangeListener($(this));
        });

        $('#addCost').click(function() {
            var $costRow = $('.original.cost-row').clone();
            addCostChangeListener($costRow.find('.cost-type'));
            addRemoveCostListener($costRow.find('.removeCost'));
            $costRow.removeClass('hide cost-row original');
            $('#costs').append($costRow);
        });

        function addRemoveCostListener(node) {
            node.on('click', function() {
                $(this).parent().parent().remove();
            });
        }

        function addCostChangeListener(node) {
            node.on('change', function(e) {
                var type = $(this).val();
                $.ajax({
                    type: "GET",
                    url: "{{ url('admin/data/shops/stock-cost-type') }}?type=" + type,
                    dataType: "text"
                }).done(function(res) {
                    node.parent().parent().find('.costObjects').html(res);
                    // selectize
                    node.parent().parent().find('.cost-selectize').selectize();
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert("AJAX call failed: " + textStatus + ", " + errorThrown);
                });
            });
        }

        $('#type').change(function() {
            var type = $(this).val();
            $.ajax({
                type: "GET",
                url: "{{ url('admin/data/shops/stock-type') }}?type=" + type,
                dataType: "text"
            }).done(function(res) {
                $("#stock").html(res);
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        });

        // is_limited_stock change
        $('#is_limited_stock').change(function() {
            if ($(this).is(':checked')) {
                $('.stock-limited-quantity').removeClass('hide');
            } else {
                $('.stock-limited-quantity').addClass('hide');
            }
        });
        // is_timed_stock change
        $('#is_timed_stock').change(function() {
            if ($(this).is(':checked')) {
                $('.stock-timed-quantity').removeClass('hide');
            } else {
                $('.stock-timed-quantity').addClass('hide');
            }
        });

        $(".datepicker").datetimepicker({
            dateFormat: "yy-mm-dd",
            timeFormat: 'HH:mm:ss',
            changeMonth: true,
            changeYear: true,
            timezone: '{!! Carbon\Carbon::now()->utcOffset() !!}',
            altFieldTimeOnly: false,
            beforeShow: function(input, inst) {
                const box = inst.input[0].getBoundingClientRect();
                setTimeout(function() {
                    inst.dpDiv.css({
                        top: box.top - inst.dpDiv[0].offsetHeight,
                        left: box.left
                    });
                }, 0);
            }
        });
    });
</script>
