{!! Form::open(['url' => $option->id ? 'admin/data/criteria/step/' . $stepId . '/option/' . $option->id : 'admin/data/criteria/step/' . $stepId . '/option', 'files' => true]) !!}

<div class="row">
    <div class="form-group col-6">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $option->name, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group col-6">
        {!! Form::label('Summary (Optional)') !!}
        {!! Form::text('summary', $option->summary, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('description') !!} {!! add_help('This is used for the criterion\'s guide.') !!}
    {{-- uniqid helps keep tinymce from being upset that we keep adding the same id'd textarea to it --}}
    {!! Form::textarea('description', $option->description, ['class' => 'form-control wysiwyg', 'id' => uniqid('description-', true)]) !!}
</div>

<div class="row align-items-end">
    <div class="form-group col-6">
        {!! Form::checkbox('is_active', 1, $option->is_active === 1 ?? 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Steps that are not active will be hidden from view.') !!}
    </div>
    <div class="form-group col-6">
        {!! Form::label('Amount') !!} {!! add_help('This is the amount applied with the step\'s calculation type if the user selects this option') !!}
        {!! Form::text('amount', $option->amount ?? 1, ['class' => 'form-control selectize']) !!}
    </div>
</div>

<div class="form-group hide">
    {!! Form::text('criterion_step_id', $stepId, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
</div>

<div class="text-right mt-4">
    {!! Form::submit($option->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@include('js._tinymce_wysiwyg')
