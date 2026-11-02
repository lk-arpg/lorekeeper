<div class="form-group">
    <div class="row">
        <div class="col-md-6">
            {!! Form::label('conversion_id', 'Convert to:') !!}
            {!! Form::select('conversion_id', $convertOptions, null, ['class' => 'form-control', 'placeholder' => 'Select Currency', 'id' => 'conversion-currency']) !!}
        </div>
        <div class="col-md-6 quantity hide">
            {!! Form::label('quantity', 'Quantity') !!}
            {!! Form::number('quantity', null, ['class' => 'form-control', 'id' => 'quantity']) !!}
        </div>
    </div>
</div>

<div class="alert alert-info hide conversion-info">
    <strong>Conversion Rate:</strong> <span id="conversion-rate"></span>
</div>

<script>
    $('#conversion-currency').on('change', function() {
        let conversionId = $(this).val();
        let currencyId = $('#convert-currency').val();
        if (conversionId) {
            $.ajax({
                url: '{{ url('bank/convert') }}/' + currencyId + '/rate/' + conversionId,
                type: 'GET',
                success: function(data) {
                    console.log(data);
                    $('#conversion-rate').html(data);
                    $('.conversion-info').removeClass('hide');

                    // get first number from conversion rate (form N:M)
                    let conversionRate = data.split(':')[0];
                    $('.quantity').removeClass('hide');
                    $('#quantity').attr('placeholder', 'Min: ' + conversionRate);
                    $('#quantity').attr('min', conversionRate);
                }
            });
        } else {
            $('.conversion-info').addClass('hide');
            $('.quantity').addClass('hide');
        }
    });
</script>
