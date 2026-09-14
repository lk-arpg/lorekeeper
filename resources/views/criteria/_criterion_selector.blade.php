<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="flex-grow-1 mr-2">
        {!! Form::select('criterion[#][id]', $criteria, null, ['class' => 'form-control criterion-select', 'placeholder' => 'Select a Criterion to set options']) !!}
    </div>
    <div>
        <button class="btn btn-danger delete-calc" type="button"><i class="fas fa-trash"></i></button>
    </div>
</div>
<div class="form">Select a criterion to populate this area.</div>
