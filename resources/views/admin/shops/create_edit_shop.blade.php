@extends('admin.layout')

@section('admin-title')
    {{ $shop->id ? 'Edit' : 'Create' }} Shop
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Shops' => 'admin/data/shops', ($shop->id ? 'Edit' : 'Create') . ' Shop' => $shop->id ? 'admin/data/shops/edit/' . $shop->id : 'admin/data/shops/create']) !!}

    <h1>{{ $shop->id ? 'Edit' : 'Create' }} Shop
        @if ($shop->id)
            ({!! $shop->displayName !!})
            <a href="#" class="btn btn-danger float-right delete-shop-button">Delete Shop</a>
        @endif
    </h1>

    {!! Form::open(['url' => $shop->id ? 'admin/data/shops/edit/' . $shop->id : 'admin/data/shops/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $shop->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Shop Image (Optional)') !!} {!! add_help('This image is used on the shop index and on the shop page as a header.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: None (Choose a standard size for all shop images)</div>
        @if ($shop->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $shop->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('is_active', 1, $shop->id ? $shop->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the shop will not be accessible to anyone. Please note that this option is overridden if this is set as a Timed Shop.') !!}
        </div>
        <div class="col-md form-group">
            {!! Form::checkbox('is_hidden', 0, $shop->id ? $shop->is_hidden : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_hidden', 'Set Hidden', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, the shop will not be visible on the shop index, but still accessible.') !!}
        </div>
        <div class="col-md form-group">
            {!! Form::checkbox('is_staff', 1, $shop->id ? $shop->is_staff : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_staff', 'For Staff?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, the shop will not be visible to or accessible by regular users, only staff.') !!}
        </div>
        <div class="col-md form-group">
            {!! Form::checkbox('is_fto', 1, $shop->id ? $shop->is_fto : 0, ['class' => 'form-check-label', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_fto', 'FTO Only?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, only users who are currently FTO or staff can enter.') !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::checkbox('use_coupons', 1, $shop->id ? $shop->use_coupons : 0, ['class' => 'form-check-label', 'data-toggle' => 'toggle', 'id' => 'use_coupons']) !!}
        {!! Form::label('use_coupons', 'Allow Coupons?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Note that ALL coupons will be allowed to be used, unless specified otherwise.') !!}
    </div>
    <div class="form-group coupon-row {{ $shop->use_coupons ? '' : 'hide' }}">
        {!! Form::label('allowed_coupons', 'Allowed Coupon(s)', ['class' => 'form-check-label']) !!}
        <p>Leave blank to allow ALL coupons.</p>
        {!! Form::select('allowed_coupons[]', $coupons, $shop->allowed_coupons, ['multiple', 'class' => 'form-check-label', 'placeholder' => 'Select Coupons', 'id' => 'allowed_coupons']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_timed_shop', 1, $shop->is_timed_shop ?? 0, ['class' => 'form-check-input shop-timed shop-toggle shop-field', 'data-toggle' => 'toggle', 'id' => 'is_timed_shop']) !!}
        {!! Form::label('is_timed_shop', 'Set Timed Shop', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Sets the shop as timed between the chosen dates.') !!}
    </div>
    <div class="card mb-3 shop-timed-quantity {{ $shop->is_timed_shop ? '' : 'hide' }}">
        <div class="card-body">
            <h3>Shop Time Period</h3>
            <p>Both of the below options can work together. If both are set, the shop will only be available during the specific time period, and on the specific days of the week and months.</p>

            <h5>Specific Time Period</h5>
            <p>The time period below is between the specific dates and times, rather than an agnostic period like "every November".</p>
            <div class="row">
                <div class="col-md-6 form-group">
                    {!! Form::label('start_at', 'Start Time') !!} {!! add_help('The shop will cycle in at this date.') !!}
                    {!! Form::text('start_at', $shop->start_at, ['class' => 'form-control datepicker']) !!}
                </div>
                <div class="col-md-6 form-group">
                    {!! Form::label('end_at', 'End Time') !!} {!! add_help('The shop will cycle out at this date.') !!}
                    {!! Form::text('end_at', $shop->end_at, ['class' => 'form-control datepicker']) !!}
                </div>
            </div>

            <h5>Repeating Time Period</h5>
            <p>Select the months and days of the week that the shop will be available.</p>
            <p><b>If months are set alongside days, the shop will only be available on those days in those months.</b></p>
            <div class="form-group">
                {!! Form::label('shop_days', 'Days of the Week') !!}
                {!! Form::select('shop_days[]', ['Monday' => 'Monday', 'Tuesday' => 'Tuesday', 'Wednesday' => 'Wednesday', 'Thursday' => 'Thursday', 'Friday' => 'Friday', 'Saturday' => 'Saturday', 'Sunday' => 'Sunday'], $shop->days ?? null, [
                    'class' => 'form-control selectize',
                    'multiple' => 'multiple',
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('shop_months', 'Months of the Year') !!}
                {!! Form::select(
                    'shop_months[]',
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
                    $shop->months ?? null,
                    ['class' => 'form-control selectize', 'multiple' => 'multiple'],
                ) !!}
            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($shop->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($shop->id)
        <hr />

        @include('widgets._add_limits', ['object' => $shop])

        <hr />

        <h3>Shop Stock</h3>
        <div class="text-right mb-3">
            <a href="#" class="add-stock-button btn btn-outline-primary">Add Stock</a>
        </div>
        <div class="row">
            @foreach ($shop->stock as $stock)
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="row">
                                @if ($stock->item?->has_image)
                                    <div class="col-4">
                                        <img src="{{ $stock->item?->imageUrl }}" class="img-fluid" alt="{{ $stock->item?->name }}">
                                    </div>
                                @endif
                                <div class="col-{{ $stock->item?->has_image ? '8' : '10' }}">
                                    <div>
                                        <a href="{{ $stock->item?->idUrl }}">
                                            <strong>{{ $stock->item?->name ?? 'Deleted' }} - {{ $stock->stock_type }}</strong>
                                        </a>
                                        @if ($stock->isRandom)
                                            <span class="ml-1 badge badge-primary">Random</span>
                                        @endif
                                    </div>
                                    <div><strong>Cost: </strong> {!! $stock->displayCosts() ?? 'Free' !!}</div>
                                    @if (!$stock->is_visible)
                                        <div><i class="fas fa-eye-slash"></i></div>
                                    @endif
                                    @if ($stock->is_timed_stock)
                                        <i class="fas fa-clock"></i>
                                    @endif
                                    @if ($stock->is_limited_stock)
                                        <div>Stock: {{ $stock->quantity }}</div>
                                    @endif
                                    @if ($stock->is_limited_stock)
                                        <div>Restock: {!! $stock->restock ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}</div>
                                    @endif
                                    @if ($stock->purchase_limit)
                                        <div class="text-danger">Max {{ $stock->purchase_limit }}
                                            @if ($stock->purchase_limit_timeframe !== 'lifetime')
                                                {{ $stock->purchase_limit_timeframe }}
                                            @endif per user
                                        </div>
                                    @endif
                                    @if ($stock->disallow_transfer)
                                        <div class="text-danger">Cannot be transferred</div>
                                    @endif
                                </div>
                            </div>
                            @if ($stock->is_timed_stock)
                                <div class="row no-gutters d-flex">
                                    <small>
                                        {!! $stock->displayTime() !!}
                                    </small>
                                </div>
                            @endif
                            <div class="text-right mb-0">
                                <button class="btn btn-primary" onclick="editStock({{ $stock->id }})">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <div class="btn btn-danger" onclick="deleteStock({{ $stock->id }})">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    @include('widgets._datetimepicker_js')
    @include('js._tinymce_wysiwyg')
    <script>
        $('.selectize').selectize();

        $('#is_timed_shop').change(function() {
            if ($(this).is(':checked')) {
                $('.shop-timed-quantity').removeClass('hide');
            } else {
                $('.shop-timed-quantity').addClass('hide');
            }
        });

        // edit stock function
        function editStock(id) {
            loadModal("{{ url('admin/data/shops/stock/edit') }}/" + id, 'Edit Stock');
        }

        function deleteStock(id) {
            loadModal("{{ url('admin/data/shops/stock/delete') }}/" + id, 'Delete Stock');
        }

        $(document).ready(function() {
            $('#use_coupons').change(function() {
                if ($(this).is(':checked')) {
                    $('.coupon-row').removeClass('hide');
                } else {
                    $('.coupon-row').addClass('hide');
                }
            });

            $('#allowed_coupons').selectize({
                maxItems: 5
            });

            $('.delete-shop-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/shops/delete') }}/{{ $shop->id }}", 'Delete Shop');
            });
            $('.add-stock-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/shops/stock') }}/{{ $shop->id }}", 'Add Stock');
            });

            $('#add-feature').on('click', function(e) {
                e.preventDefault();
                addFeatureRow();
            });
            $('.remove-feature').on('click', function(e) {
                e.preventDefault();
                removeFeatureRow($(this));
            });

            function addFeatureRow() {
                var $clone = $('.feature-row').clone();
                $('#featureList').append($clone);
                $clone.removeClass('hide feature-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-feature').on('click', function(e) {
                    e.preventDefault();
                    removeFeatureRow($(this));
                })
                $clone.find('.feature-select').selectize();
            }

            function removeFeatureRow($trigger) {
                $trigger.parent().remove();
            }

            $('.is-restricted-class').change(function(e) {
                $('.br-form-group').css('display', this.checked ? 'block' : 'none')
            })
            $('.br-form-group').css('display', $('.is-restricted-class').prop('checked') ? 'block' : 'none')
        });
    </script>
@endsection
