@extends('admin.layout')

@section('admin-title')
    {{ $currency->id ? 'Edit' : 'Create' }} Currency
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Currencies' => 'admin/data/currencies', ($currency->id ? 'Edit' : 'Create') . ' Currency' => $currency->id ? 'admin/data/currencies/edit/' . $currency->id : 'admin/data/currencies/create']) !!}

    <h1>{{ $currency->id ? 'Edit' : 'Create' }} Currency
        @if ($currency->id)
            <a href="#" class="btn btn-danger float-right delete-currency-button">Delete Currency</a>
        @endif
    </h1>

    {!! Form::open(['url' => $currency->id ? 'admin/data/currencies/edit/' . $currency->id : 'admin/data/currencies/create', 'files' => true]) !!}

    <h3>Basic Information</h3>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('Currency Name') !!}
                {!! Form::text('name', $currency->name, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('Abbreviation (Optional)') !!} {!! add_help('This will be used to denote the currency if an icon is not provided. If an abbreviation is not given, the currency\'s full name will be used.') !!}
                {!! Form::text('abbreviation', $currency->abbreviation, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-4 form-group">
            {!! Form::label('Currency Category (Optional)') !!}
            {!! Form::select('currency_category_id', $categories, $currency->currency_category_id, ['class' => 'form-control', 'placeholder' => 'No category']) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Icon Image (Optional)') !!} {!! add_help('This will be used to denote the currency. If not provided, the abbreviation will be used.') !!}
                <div class="custom-file">
                    {!! Form::label('icon', 'Choose icon file...', ['class' => 'custom-file-label']) !!}
                    {!! Form::file('icon', ['class' => 'custom-file-input']) !!}
                </div>
                <div class="text-muted">Recommended height: 16px</div>
                @if ($currency->has_icon)
                    <div class="form-check">
                        {!! Form::checkbox('remove_icon', 1, false, ['class' => 'form-check-input']) !!}
                        {!! Form::label('remove_icon', 'Remove current image', ['class' => 'form-check-label']) !!}
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
                <div class="custom-file">
                    {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
                    {!! Form::file('image', ['class' => 'custom-file-input']) !!}
                </div>
                <div class="text-muted">Recommended size: 200px x 200px</div>
                @if ($currency->has_image)
                    <div class="form-check">
                        {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                        {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $currency->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <h3>Usage</h3>
    <p>Choose whether this currency should be attached to users and/or characters. Both can be selected at the same time, but at least one must be selected.</p>
    <div class="form-group">
        <div class="form-check">
            <label class="form-check-label">
                {!! Form::checkbox('is_user_owned', 1, $currency->is_user_owned, ['class' => 'form-check-input', 'id' => 'userOwned']) !!}
                Attach to Users
            </label>
        </div>
    </div>
    <div class="card mb-3" id="userOptions">
        <div class="card-body">
            <div class="mb-2">
                {!! Form::checkbox('is_displayed', 1, $currency->is_displayed, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_displayed', 'Profile Display', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
                    'If this is on, it will be displayed on users\' main profile pages. Additionally, if the user does not own the currency, it will be displayed as 0 currency. (If this is off, currencies not owned will not be displayed at all.) All owned currencies will still be visible from the user\'s bank page.',
                ) !!}
            </div>
            <div>
                {!! Form::checkbox('allow_user_to_user', 1, $currency->allow_user_to_user, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Allow', 'data-off' => 'Disallow']) !!}
                {!! Form::label('allow_user_to_user', 'User → User Transfers', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will allow users to transfer this currency to other users from their bank.') !!}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="form-check">
            <label class="form-check-label">
                {!! Form::checkbox('is_character_owned', 1, $currency->is_character_owned, ['class' => 'form-check-input', 'id' => 'characterOwned']) !!}
                Attach to Characters
            </label>
        </div>
    </div>
    <div class="card mb-3" id="characterOptions">
        <div class="card-body">
            <div class="mb-2">
                {!! Form::checkbox('allow_user_to_character', 1, $currency->allow_user_to_character, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Allow', 'data-off' => 'Disallow']) !!}
                {!! Form::label('allow_user_to_character', 'User → Character Transfers', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will allow a user to transfer this currency to their own characters unidirectionally.') !!}
            </div>
            <div>
                {!! Form::checkbox('allow_character_to_user', 1, $currency->allow_character_to_user, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Allow', 'data-off' => 'Disallow']) !!}
                {!! Form::label('allow_character_to_user', 'Character → User Transfers', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will allow a user to transfer this currency from their own characters to their bank unidirectionally.') !!}
            </div>
        </div>
    </div>

    @if ($currency->id && $currency->is_user_owned)
        <h3>Conversion Rates</h3>
        <p>
            Choose whether this currency should be able to be converted to other currencies. If so, you can set the conversion rates here.
            <br />
            <strong>Conversion rates are unidirectional.</strong> If you want to allow a currency to be converted both ways, you will need to create conversion on both currencies.
            <br />
            Rates should be in decimal form. For example, 1 USD = 0.75 EUR, so the rate would be 0.75.
            Conversions will only allow whole number conversions, e.g. requiring a user to convert 3 USD to 4 EUR.
            <br />
            <strong>Conversions are only possible on user owned currencies.</strong>
        </p>
        <div class="form-group">
            <div class="d-flex justify-content-end mb-2">
                <a href="#" class="btn btn-primary mb-2" id="add-conversion">Add Conversion</a>
            </div>
            <div id="conversionList">
                @foreach ($currency->conversions as $conversion)
                    <div class="d-flex mb-2">
                        {!! Form::select('conversion_id[]', $currencies, $conversion->conversion_id, ['class' => 'form-control mr-2 conversion-select original', 'placeholder' => 'Select Currency']) !!}
                        {!! Form::text('rate[]', $conversion->rate, ['class' => 'form-control mr-2', 'placeholder' => 'Conversion Rate']) !!}
                        <div class="form-control border-0 w-25">
                            {{ $conversion->ratio() }}
                        </div>
                        <a href="#" class="remove-conversion btn btn-danger mb-2">×</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $currency->id ? $currency->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the currency will not be visible in the currencies list or available for selection in search. Permissioned staff will still be able to see them, however.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($currency->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($currency->id && $currency->is_user_owned)
        <div class="conversion-row hide mb-2">
            {!! Form::select('conversion_id[]', $currencies, null, ['class' => 'form-control mr-2 conversion-select', 'placeholder' => 'Select Currency']) !!}
            {!! Form::text('rate[]', null, ['class' => 'form-control mr-2 conversion-rate', 'placeholder' => 'Conversion Rate']) !!}
            <div class="form-control border-0 w-25">
            </div>
            <a href="#" class="remove-conversion btn btn-danger mb-2">×</a>
        </div>
    @endif

    @if ($currency->id)
        <h3>Previews</h3>

        <h5>Display</h5>
        <div class="card mb-3">
            <div class="card-body">
                {!! $currency->display(100) !!}
            </div>
        </div>

        <h5>World Page Entry</h5>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._currency_entry', ['currency' => $currency])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            var $userOwned = $('#userOwned');
            var $characterOwned = $('#characterOwned');
            var $userOptions = $('#userOptions');
            var $characterOptions = $('#characterOptions');

            var userOwned = $userOwned.is(':checked');
            var characterOwned = $characterOwned.is(':checked');

            updateOptions();

            $userOwned.on('change', function(e) {
                userOwned = $userOwned.is(':checked');

                updateOptions();
            });
            $characterOwned.on('change', function(e) {
                characterOwned = $characterOwned.is(':checked');

                updateOptions();
            });

            function updateOptions() {
                if (userOwned) $userOptions.removeClass('hide');
                else $userOptions.addClass('hide');

                if (userOwned && characterOwned) $characterOptions.removeClass('hide');
                else $characterOptions.addClass('hide');
            }

            $('.delete-currency-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/currencies/delete') }}/{{ $currency->id }}", 'Delete Currency');
            });

            /////////////////////// Conversion Rates ///////////////////////
            $('.original.currency-select').selectize();

            $('#add-conversion').on('click', function(e) {
                e.preventDefault();
                addConversionRow();
            });
            $('.remove-conversion').on('click', function(e) {
                e.preventDefault();
                removeConversionRow($(this));
            })

            function addConversionRow() {
                var $clone = $('.conversion-row').clone();
                $('#conversionList').append($clone);
                $clone.removeClass('hide conversion-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-conversion').on('click', function(e) {
                    e.preventDefault();
                    removeConversionRow($(this));
                })
                $clone.find('.conversion-rate').on('input', function() {
                    var $row = $(this).parent();
                    var rate = parseFloat($(this).val());

                    if (isNaN(rate) || rate <= 0) {
                        return;
                    }

                    function gcd(a, b) {
                        if (b === 0) return a;
                        return gcd(b, a % b);
                    }

                    // Convert rate to a ratio
                    var numerator = rate * 100; // Adjust to avoid floating point issues
                    var denominator = 100;
                    var divisor = gcd(numerator, denominator);

                    numerator = numerator / divisor;
                    denominator = denominator / divisor;

                    $row.find('.w-25').text(numerator + ' : ' + denominator);
                });
                $clone.find('.conversion-select').selectize();
            }

            function removeConversionRow($trigger) {
                $trigger.parent().remove();
            }
        });
    </script>
@endsection
