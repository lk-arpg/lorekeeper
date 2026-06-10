@extends('admin.layout')

@section('admin-title')
    Criteria Default
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Criteria' => 'admin/data/criteria',
        'Default Criteria' => 'admin/data/criteria-defaults',
        'Default Criteria' => 'admin/data/criteria-defaults',
        ($default->id ? 'Edit' : 'Create') . ' Default Criteria' => $default->id ? 'admin/data/criteria-defaults/edit/' . $default->id : 'admin/data/criteria-defaults/create',
    ]) !!}

    <h1>{{ $default->id ? 'Edit' : 'Create' }} Criteria Default
        @if ($default->id)
            <a href="#" class="btn btn-danger float-right delete-button">Delete Default</a>
        @endif
    </h1>

    {!! Form::open(['url' => $default->id ? 'admin/data/criteria-defaults/edit/' . $default->id : 'admin/data/criteria-defaults/create']) !!}

    <h3>Basic Information</h3>
    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $default->name, ['class' => 'form-control']) !!}
    </div>


    <div class="form-group">
        {!! Form::label('Summary (Optional)') !!}
        {!! Form::text('summary', $default->summary, ['class' => 'form-control']) !!}
    </div>

    <h3 class="mt-5">Criteria Rewards <button class="btn btn-primary float-right add-calc" type="button">+ Criterion</a></h3>
    <p>Criteria can be used in addition to or in replacement of rewards. They can be created under the "criterion" section of the admin panel,
        and allow for dynamic reward amounts to be generated based on user / admin selected criteria like the type of art, or the number of words.</p>
    <p>When adding criteria here, any defaults set will be populated in for a prompt/gallery if this default is selected.</p>
    <div id="criteria">
        @foreach ($default->criteria as $criterion)
            <div class="card p-3 mb-2 pl-0">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a class="col-1 p-0" data-toggle="collapse" href="#collapsable-{{ $criterion->id }}">
                        <i class="fas fa-angle-down" style="font-size: 24px"></i>
                    </a>
                    <div class="flex-grow-1 mr-2">
                        {!! Form::select('criterion_id[]', $criteria, $criterion->criterion_id, ['class' => 'form-control criterion-select', 'placeholder' => 'Select a Criterion to set Minimum Requirements']) !!}
                    </div>
                    <div>
                        <button class="btn btn-danger delete-calc" type="button"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div id="collapsable-{{ $criterion->id }}" class="form collapse">
                    @include('criteria._minimum_requirements', [
                        'criterion' => $criterion->criterion,
                        'minRequirements' => $criterion->minRequirements,
                        'id' => $criterion->criterion_id,
                        'isAdmin' => true,
                        'criterion_currency' => isset($criterion->criterion_currency_id) ? $criterion->criterion_currency_id : $criterion->criterion->currency_id,
                    ])
                </div>
            </div>
        @endforeach
    </div>

    <div class="text-right mt-4">
        {!! Form::submit($default->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <div id="copy-calc" class="card p-3 mb-2 pl-0 hide">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <a class="col-1 p-0" data-toggle="collapse" href="#collapsable-">
                <i class="fas fa-angle-down" style="font-size: 24px"></i>
            </a>
            <div class="flex-grow-1 mr-2">
                {!! Form::select('criterion_id[]', $criteria, null, ['class' => 'form-control criterion-select', 'placeholder' => 'Select a Criterion to set Minimum Requirements']) !!}
            </div>
            <div>
                <button class="btn btn-danger delete-calc" type="button"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div id="collapsable-" class="form collapse">Select a criterion to populate this area.</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/criteria-defaults/delete') }}/{{ $default->id }}", 'Delete Criteria Default');
            });

            $('.add-calc').on('click', function(e) {
                e.preventDefault();
                var clone = $('#copy-calc').clone();
                clone.removeClass('hide');
                clone.find('.criterion-select').on('change', loadForm);
                clone.find('.delete-calc').on('click', deleteCriterion);
                clone.removeAttr('id');
                const key = $('[data-toggle]').length;
                clone.find('[data-toggle]').attr('href', '#collapsable-' + key);
                clone.find('.collapse').attr('id', 'collapsable-' + key);
                $('#criteria').append(clone);
            });

            $('.delete-calc').on('click', deleteCriterion);

            function deleteCriterion(e) {
                e.preventDefault();
                var toDelete = $(this).closest('.card');
                toDelete.remove();
            }

            function loadForm(e) {
                var id = $(this).val();
                if (id) {
                    var form = $(this).closest('.card').find('.form');
                    form.load("{{ url('criteria') }}/" + id, (response, status, xhr) => {
                        if (status == "error") {
                            var msg = "Error: ";
                            console.error(msg + xhr.status + " " + xhr.statusText);
                        } else {
                            form.find('[data-toggle=tooltip]').tooltip({
                                html: true
                            });
                            form.find('[data-toggle=toggle]').bootstrapToggle();
                        }
                    });
                }
            }

            $('.criterion-select').on('change', loadForm);
        });
    </script>
@endsection
