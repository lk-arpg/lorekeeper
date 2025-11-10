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

    <h3>Rewards</h3>
    <p>Rewards are credited on a per-user basis. Mods are able to modify the specific rewards granted at approval time.</p>
    <p>You can add loot tables containing any kind of currencies (both user- and character-attached), but be sure to keep track of which are being distributed! Character-only currencies cannot be given to users.</p>
    @include('widgets._loot_select', ['loots' => $prompt->rewards, 'showLootTables' => true, 'showRaffles' => true])

    <div class="text-right">
        {!! Form::submit($prompt->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._loot_select_row', ['showLootTables' => true, 'showRaffles' => true])

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
    @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
    @include('widgets._datetimepicker_js')
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.delete-prompt-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/prompts/delete') }}/{{ $prompt->id }}", 'Delete Prompt');
            });
        });
    </script>
@endsection
