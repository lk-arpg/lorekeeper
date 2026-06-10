@extends('admin.layout')

@section('admin-title')
    {{ $prompt->id ? 'Edit' : 'Create' }} Prompts
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Prompts' => 'admin/data/prompts', ($prompt->id ? 'Edit' : 'Create') . ' Prompt' => $prompt->id ? 'admin/data/prompts/edit/' . $prompt->id : 'admin/data/prompts/create']) !!}

    <h1>{{ $prompt->id ? 'Edit' : 'Create' }} Prompt
        @if ($prompt->id)
            <a href="#" class="btn btn-danger float-right delete-prompt-button">Delete Prompt</a>
        @endif
    </h1>

    {!! Form::open(['url' => $prompt->id ? 'admin/data/prompts/edit/' . $prompt->id : 'admin/data/prompts/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-8 form-group">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $prompt->name, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md form-group">
            {!! Form::label('Prefix (Optional)') !!} {!! add_help('This is used to label submissions associated with this prompt in the gallery.') !!}
            {!! Form::text('prefix', $prompt->prefix, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($prompt->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Prompt Category (Optional)') !!}
        {!! Form::select('prompt_category_id', $categories, $prompt->prompt_category_id, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Summary (Optional)') !!} {!! add_help('This is a short blurb that shows up on the consolidated prompts page. HTML cannot be used here.') !!}
        {!! Form::text('summary', $prompt->summary, ['class' => 'form-control', 'maxLength' => 250]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!} {!! add_help('This is a full description of the prompt that shows up on the full prompt page.') !!}
        {!! Form::textarea('description', $prompt->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('start_at', 'Start Time (Optional)') !!} {!! add_help('Prompts cannot be submitted to the queue before the starting time.') !!}
            {!! Form::text('start_at', $prompt->start_at, ['class' => 'form-control datepicker']) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::label('end_at', 'End Time (Optional)') !!} {!! add_help('Prompts cannot be submitted to the queue after the ending time.') !!}
            {!! Form::text('end_at', $prompt->end_at, ['class' => 'form-control datepicker']) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::checkbox('hide_before_start', 1, $prompt->id ? $prompt->hide_before_start : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('hide_before_start', 'Hide Before Start Time', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If hidden, the prompt will not be shown on the prompt list before the starting time is reached. A starting time needs to be set.') !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('hide_after_end', 1, $prompt->id ? $prompt->hide_after_end : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('hide_after_end', 'Hide After End Time', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If hidden, the prompt will not be shown on the prompt list after the ending time is reached. An end time needs to be set.') !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('is_active', 1, $prompt->id ? $prompt->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Prompts that are not active will be hidden from the prompt list. The start/end time hide settings override this setting, i.e. if this is set to active, it will still be hidden outside of the start/end times.') !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('staff_only', 1, $prompt->id ? $prompt->staff_only : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('staff_only', 'Staff Only', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is set, the prompt will only be visible to staff, and only they will be able to submit to it.') !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Hide Submissions (Optional)') !!} {!! add_help('Hide submissions to this prompt until the prompt ends, or forever. <strong>Hiding until the prompt ends requires a set end time.</strong>') !!}
        {!! Form::select('hide_submissions', [0 => 'Submissions Visible After Approval', 1 => 'Hide Submissions Until Prompt Ends', 2 => 'Hide Submissions Always'], $prompt->hide_submissions, ['class' => 'form-control']) !!}
    </div>

    <div class="card">
        <div class="card-body">
            <h3>Submission Limits</h3>
            <p>Limit the number of times a user can submit. Leave blank to allow endless submissions.</p>
            <p>Set a number into number of submissions. This will be applied for all time if you leave period blank, or per time period (ex: once a month, twice a week) if selected.</p>
            <p>If you turn 'per character' on, then the number of submissions multiplies per character (ex: if you can submit twice a month per character and you own three characters, that's 6 submissions) HOWEVER it will not keep track of which
                characters are being submitted due to conflicts arising in character cameos. A user will be able to submit those full 6 times with just one character...!</p>
            <div class="row">
                <div class="col-md-6 form-group">
                    {!! Form::label('limit', 'Number of Submissions (Optional)') !!} {!! add_help('Enter a number to limit how many times a user can submit. Leave blank to allow endless submissions.') !!}
                    {!! Form::text('limit', $prompt->limit, ['class' => 'form-control']) !!}
                </div>
                <div class="col-md-6 form-group">
                    {!! Form::label('limit_period', 'Limit Period') !!} {!! add_help('The time period that the limit is set for.') !!}
                    {!! Form::select('limit_period', $limit_periods, $prompt->limit_period, ['class' => 'form-control', 'data-name' => 'limit_period']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::checkbox('limit_character', 1, $prompt->limit_character, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('limit_character', 'Per Character', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, they can submit once per character they own on the masterlist.') !!}
            </div>
        </div>
    </div>

    {{-- blade-formatter-disable --}}
    @include('widgets._add_rewards', [
        'object' => $prompt,
        'useForm' => false,
        'showRaffles' => true,
        'showLootTables' => true,
        'showRecipient' => true,
        'info' => '<p>User rewards are credited on a per-user basis, character rewards are rewarded to all characters attached. Mods are able to modify the specific rewards granted at approval time.</p><p class="mb-0">You can add loot tables containing any kind of currencies (both user- and character-attached), but be sure to keep track of which are being distributed! <strong>Character-only currencies cannot be given to users.</strong></p>',
    ])
    {{-- blade-formatter-enable --}}

    <div class="card mb-3">
        <div class="card-body">
            @include('criteria._default_selector', ['type' => 'prompt'])

            <hr />

            <h3 class="mt-5">
                Criteria Rewards
                <button class="btn btn-primary float-right add-calc" type="button">+ Criterion</button>
            </h3>
            <p>Criteria can be used in addition to or in replacement of rewards. They can be created under the "criterion" section of the admin panel,
                and allow for dynamic reward amounts to be generated based on user / admin selected criteria like the type of art, or the number of words.</p>
            <div id="criteria">
                @foreach ($prompt->criteria as $criterion)
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
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($prompt->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
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

    @if ($prompt->id)
        @include('widgets._add_limits', [
            'object' => $prompt,
            'hideAutoUnlock' => true,
        ])

        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('prompts._prompt_entry', ['prompt' => $prompt])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    @include('widgets._datetimepicker_js')
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.delete-prompt-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/prompts/delete') }}/{{ $prompt->id }}", 'Delete Prompt');
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
