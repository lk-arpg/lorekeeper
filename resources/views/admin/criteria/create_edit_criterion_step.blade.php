@extends('admin.layout')

@section('admin-title')
    Criterion Step
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Criteria' => 'admin/data/criteria',
        'Edit Criterion' => 'admin/data/criteria/edit/' . $criterionId,
        ($step->id ? 'Create' : 'Edit') . ' Criterion Step' => $step->id ? 'admin/data/criteria/' . $criterionId . '/step/' . $step->id : 'admin/data/criteria/' . $criterionId . '/step',
    ]) !!}

    <h1>
        {{ $step->id ? 'Edit' : 'Create' }} Step
        @if ($step->id)
            <a href="#" class="btn btn-danger float-right delete-button">Delete Step</a>
        @endif
    </h1>

    {!! Form::open(['url' => $step->id ? 'admin/data/criteria/' . $criterionId . '/step/' . $step->id : 'admin/data/criteria/' . $criterionId . '/step', 'files' => true]) !!}

    <h3>Basic Information</h3>
    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $step->name, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('Summary (Optional)') !!}
        {!! Form::text('summary', $step->summary, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Guide Image (Optional)') !!} {!! add_help('This image is used as an example of the step if the guide page is enabled for your criterion.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($step->has_image)
            <img style="height: 100px; width: auto;" src="{{ $step->imageUrl }}" />
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('description') !!} {!! add_help('This is used for the criterion\'s guide.') !!}
        {!! Form::textarea('description', $step->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row align-items-end">
        <div class="form-group col-6">
            {!! Form::checkbox('is_active', 1, $step->is_active === 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Steps that are not active will be hidden from view.') !!}
        </div>
    </div>

    <div class="row align-items-end">
        <div class="form-group col-6">
            {!! Form::label('Type') !!} {!! add_help('This is the type of form element presented to the user. Input is an number input, options is a select, and boolean is a checkbox') !!}
            {!! Form::select('type', ['options' => 'Dropdown Input / Select from Options', 'input' => 'Number Input', 'boolean' => 'Boolean'], $step->type, ['class' => 'form-control selectize']) !!}
        </div>
        <div class="form-group col-6">
            {!! Form::label('Calculation Type') !!} {!! add_help('This is the type of calculation done with the amount calculated from the selected option. If multiplicative, negative amounts will be divided.') !!}
            {!! Form::select('calc_type', ['additive' => 'Additive', 'multiplicative' => 'Multiplicative'], $step->calc_type, ['class' => 'form-control selectize']) !!}
        </div>
    </div>

    @if ($step->id)
        <h2 class="mt-5">Step Options @if ($step->type === 'options')
                <button type="button" class="btn btn-primary float-right option-create">+ Add Option</button>
            @endif
        </h2>
        <p>If you change the Criterion Step's type, it will delete what's specified in this section, and populate it with new configuration.</p>
        @if ($step->type === 'options')
            <p>These can be re-ordered using Drag and Drop. If you set a minimum requirement on a prompt, only options after that one will be choose-able.</p>
            <div id="sortable" class="sortable">
                @foreach ($step->options->sortBy('order') as $option)
                    <div class="card p-3 mb-2 pl-0" data-id="{{ $option->id }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <i class="fas fa-grip-lines-vertical mr-3" style="font-size: 150%; color: rgba(0,0,0,.3); cursor: grab; margin-left: -3px;"></i>
                            <div class="flex-grow-1">
                                <h4 class="pb-0 mb-0">
                                    @if ($option->is_active === 0)
                                        <i class="fas fa-eye-slash"></i>
                                    @else
                                        <i class="fas fa-eye"></i>
                                    @endif
                                    {{ $option->name }}
                                </h4>
                                <span class="text-secondary">{!! $step->criterion->currency->display($option->amount) !!} · {{ $option->summary }}</span>
                            </div>
                            <div style="flex: 0 0 auto;">
                                <button class="option-edit btn btn-info text-white mr-2" data-id="{{ $option->id }}" type="button"><i class="fas fa-pencil-alt"></i></button>
                                <button class="btn btn-danger option-delete" data-id="{{ $option->id }}" type="button"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {!! Form::hidden('sort', null, ['id' => 'sortableOrder']) !!}
        @elseif($step->type === 'input')
            <div class="row align-items-end">
                <div class="form-group col-6">
                    {!! Form::label('Input Calculation Type') !!} {!! add_help('This is the type of calculation applied with the amount entered by the user and the value to the left. If multiplicative, negative amounts will be divided.') !!}
                    {!! Form::select('input_calc_type', ['additive' => 'Additive', 'multiplicative' => 'Multiplicative'], $step->input_calc_type ?? 'multiplicative', ['class' => 'form-control selectize']) !!}
                </div>
                <div class="form-group col-6">
                    {!! Form::label('Amount') !!} {!! add_help('This is the amount used with the Input Calculation Type and the number the user enters. If left as 1 and multiplicative, you\'ll just get the user the number entered.') !!}
                    {!! Form::text('options[amount]', $step->options->first()->amount ?? 1, ['class' => 'form-control selectize']) !!}
                </div>
            </div>

            <div class="form-group hide">
                {!! Form::checkbox('options[is_active]', 1, 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::text('options[id]', $step->options->first()->id ?? -1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            </div>
        @else
            <div class="row align-items-end">
                <div class="form-group col-12">
                    {!! Form::label('Amount') !!} {!! add_help('This is the amount used with the Calculation Type if the user marks this option as completed.') !!}
                    {!! Form::text('options[amount]', $step->options->first()->amount ?? 1, ['class' => 'form-control selectize']) !!}
                </div>
            </div>

            <div class="form-group hide">
                {!! Form::checkbox('options[is_active]', 1, 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::text('options[id]', $step->options->first()->id ?? -1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            </div>
        @endif
    @else
        <p>More Options will be presented here once the Step is created.</p>
    @endif


    <div class="text-right mt-4">
        {!! Form::submit($step->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.delete-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/criteria/step/delete/' . $step->id) }}", 'Delete Step');
            });

            $('.option-edit').on('click', function(e) {
                e.preventDefault();
                var id = $(this).attr('data-id');
                loadModal("{{ url('admin/data/criteria/step/' . $step->id . '/option') }}/" + id, 'Edit Option');
            });

            $('.option-create').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/criteria/step/' . $step->id . '/option') }}/", 'Create Option');
            });

            $('.option-delete').on('click', function(e) {
                e.preventDefault();
                var id = $(this).attr('data-id');
                loadModal("{{ url('admin/data/criteria/option/delete') }}/" + id, 'Delete Option');
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
