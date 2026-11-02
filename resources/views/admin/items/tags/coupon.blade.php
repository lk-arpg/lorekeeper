<h3>Coupon</h3>

<h4>Discount</h4>
<p>Input discount percent. You can select what coupons can be used in each shop on the shop edit page.</p>

<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::label('discount', 'Discount') !!}
        {!! Form::number('discount', $tag->getData()['discount'], ['class' => 'form-control', 'placeholder' => 'Input Discount Percent', 'min' => 1, 'max' => 100]) !!}
    </div>
    <div class="col-md-6 form-group pt-4">
        {!! Form::checkbox('infinite', 1, $tag->getData()['infinite'], ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('infinite', 'Should this coupon be unlimited use?', ['class' => 'ml-3 form-check-label']) !!}
    </div>
</div>
