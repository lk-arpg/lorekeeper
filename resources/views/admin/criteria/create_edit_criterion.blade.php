@extends('admin.layout')

@section('admin-title')
    Criterion
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Criteria' => 'admin/data/criteria', ($criterion->id ? 'Edit' : 'Create') . ' Criterion' => $criterion->id ? 'admin/data/criteria/edit/' . $criterion->id : 'admin/data/criteria/create']) !!}

    <h1>{{ $criterion->id ? 'Edit' : 'Create' }} Criterion
        @if ($criterion->id)
            <a href="#" class="btn btn-danger float-right delete-button">Delete Criterion</a>
        @endif
    </h1>

    {!! Form::open(['url' => $criterion->id ? 'admin/data/criteria/edit/' . $criterion->id : 'admin/data/criteria/create', 'files' => true]) !!}

    <h3>Basic Information</h3>
    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $criterion->name, ['class' => 'form-control']) !!}
    </div>


    <div class="form-group">
        {!! Form::label('Summary (Optional)') !!}
        {!! Form::text('summary', $criterion->summary, ['class' => 'form-control']) !!}
    </div>

    <div class="row align-items-end">
        <div class="form-group col-6">
            {!! Form::label('Base Amount (Optional)') !!} {!! add_help('This is the base reward going into calculation steps.') !!}
            {!! Form::text('base_value', $criterion->base_value, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-6">
            {!! Form::label('Currency') !!} {!! add_help('This is the type of currency that this criterion awards.') !!}
            {!! Form::select('currency_id', $currencies, $criterion->currency_id, ['class' => 'form-control selectize', 'placeholder' => 'Select a Currency']) !!}
        </div>
    </div>

    <div class="row align-items-end">
        <div class="form-group col-4">
            {!! Form::label('Rounding') !!} {!! add_help('Whether the Criterion rounds fractional values to whole values.') !!}
            {!! Form::select('rounding', ['No Rounding' => 'No Rounding', 'Traditional Rounding' => 'Traditional Rounding', 'Always Rounds Up' => 'Always Rounds Up', 'Always Rounds Down' => 'Always Rounds Down'], $criterion->rounding, [
                'class' => 'form-control selectize',
                'placeholder' => 'Select Rounding',
            ]) !!}
        </div>
        <div class="form-group col-6">
            {!! Form::label('Rounding Precision') !!} {!! add_help('The place value to round to, ie 1 = whole values, 2 = the nearest 10th value, 3 = the nearest 100th value, etc.') !!}
            {!! Form::text('round_precision', $criterion->round_precision ?? 1, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="row align-items-end">
        <div class="form-group col-4">
            {!! Form::checkbox('is_active', 1, $criterion->is_active === 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Criteria that are not active will be hidden from view.') !!}
        </div>
        <div class="form-group col-4">
            {!! Form::checkbox('is_guide_active', 1, $criterion->is_guide_active, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_guide_active', 'Is Guide Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Toggles whether the guide page for this criterion is accessible.') !!}
        </div>
    </div>

    @if ($criterion->id)
        <h2 class="mt-5">Criterion Steps <a href="{{ url('admin/data/criteria/' . $criterion->id . '/step') }}" class="btn btn-primary float-right">+ Add Step</a></h2>
        <p>Drag and Drop the cards to re-order your steps - the ordering determines the order of the final calculation. Steps that are inactive will not be shown or included in the final calculation.</p>
        <div id="sortable" class="sortable">
            @foreach ($criterion->steps->sortBy('order') as $step)
                <div class="card p-3 mb-2 pl-0" data-id="{{ $step->id }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <i class="fas fa-grip-lines-vertical mr-3" style="font-size: 150%; color: rgba(0,0,0,.3); cursor: grab; margin-left: -3px;"></i>
                        <div class="flex-grow-1">
                            <h4 class="pb-0 mb-0">
                                @if ($step->is_active === 0)
                                    <i class="fas fa-eye-slash"></i>
                                @else
                                    <i class="fas fa-eye"></i>
                                @endif
                                {{ $step->name }}
                            </h4>
                            <span class="text-secondary">{{ ucfirst($step->type) }} · {{ ucfirst($step->calc_type) }} · {{ $step->summary }}</span>
                        </div>
                        <div style="flex: 0 0 auto;">
                            <a href="{{ url('admin/data/criteria/' . $criterion->id . '/step/' . $step->id) }}" class="btn btn-info text-white mr-2"><i class="fas fa-pencil-alt"></i></a>
                            <button class="btn btn-danger delete-step" type="button" data-id="{{ $step->id }}"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {!! Form::hidden('sort', null, ['id' => 'sortableOrder']) !!}
    @else
        <p>Criterion Steps will be able to be added here once you've created the criterion.</p>
    @endif


    <div class="text-right mt-4">
        {!! Form::submit($criterion->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/criteria/delete') }}/{{ $criterion->id }}", 'Delete Criterion');
            });

            $('.delete-step').on('click', function(e) {
                e.preventDefault();
                var id = $(this).attr('data-id')
                loadModal("{{ url('admin/data/criteria/step/delete') }}/" + id, 'Delete Step');
            });

            $("#sortable").sortable({
                steps: '.sort-item',
                placeholder: "sortable-placeholder card p-4 mb-2",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
