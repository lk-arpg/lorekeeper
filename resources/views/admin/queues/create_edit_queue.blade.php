@extends('admin.layout')

@section('admin-title')
    {{ $queue->id ? 'Edit' : 'Create' }} Queues
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Queues' => 'admin/data/queues', ($queue->id ? 'Edit' : 'Create') . ' Queue' => $queue->id ? 'admin/data/queues/edit/' . $queue->id : 'admin/data/queues/create']) !!}

    <h1>{{ $queue->id ? 'Edit' : 'Create' }} Queue
        @if ($queue->id)
            <a href="#" class="btn btn-danger float-right delete-queue-button">Delete Queue</a>
        @endif
    </h1>

    {!! Form::open(['url' => $queue->id ? 'admin/data/queues/edit/' . $queue->id : 'admin/data/queues/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-8 form-group">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $queue->name, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md form-group">
            {!! Form::label('Prefix (Optional)') !!} {!! add_help('This is used to label submissions associated with this queue in the gallery.') !!}
            {!! Form::text('prefix', $queue->prefix, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($queue->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Queue Category (Optional)') !!}
                {!! Form::select('queue_category_id', $categories, $queue->queue_category_id, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Associated Staff Ranks (Optional)') !!}{!! add_help('Queues are shown to all staff with the submission power by default. Setting this makes it so that only selected ranks will see this queue.') !!}
                {!! Form::select('staff_rank_ids[]', $ranks, $queue->staff_rank_ids, ['class' => 'form-control selectize', 'multiple']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Summary (Optional)') !!} {!! add_help('This is a short blurb that shows up on the consolidated queues page. HTML cannot be used here.') !!}
        {!! Form::text('summary', $queue->summary, ['class' => 'form-control', 'maxLength' => 250]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!} {!! add_help('This is a full description of the queue that shows up on the full queue page.') !!}
        {!! Form::textarea('description', $queue->description, ['class' => 'form-control wysiwyg']) !!}
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('start_at', 'Start Time (Optional)') !!} {!! add_help('Queues cannot be submitted to the queue before the starting time.') !!}
                {!! Form::text('start_at', $queue->start_at, ['class' => 'form-control datepicker']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('end_at', 'End Time (Optional)') !!} {!! add_help('Queues cannot be submitted to the queue after the ending time.') !!}
                {!! Form::text('end_at', $queue->end_at, ['class' => 'form-control datepicker']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::checkbox('hide_before_start', 1, $queue->id ? $queue->hide_before_start : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('hide_before_start', 'Hide Before Start Time', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If hidden, the queue will not be shown on the queue list before the starting time is reached. A starting time needs to be set.') !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('hide_after_end', 1, $queue->id ? $queue->hide_after_end : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('hide_after_end', 'Hide After End Time', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If hidden, the queue will not be shown on the queue list after the ending time is reached. An end time needs to be set.') !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('is_active', 1, $queue->id ? $queue->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Queues that are not active will be hidden from the queue list. The start/end time hide settings override this setting, i.e. if this is set to active, it will still be hidden outside of the start/end times.') !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('staff_only', 1, $queue->id ? $queue->staff_only : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('staff_only', 'Staff Only', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is set, the queue will only be visible to staff, and only they will be able to submit to it.') !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Hide Submissions (Optional)') !!} {!! add_help('Hide submissions to this queue until the queue ends, or forever. <strong>Hiding until the queue ends requires a set end time.</strong>') !!}
        {!! Form::select('hide_submissions', [0 => 'Submissions Visible After Approval', 1 => 'Hide Submissions Until Queue Ends', 2 => 'Hide Submissions Always'], $queue->hide_submissions, ['class' => 'form-control']) !!}
    </div>

    <div class="card mb-3">
        <div class="card-header h3">Configuration Settings</div>
        <div class="card-body">

            <h5>User Form (Optional)</h5>
            <p>This is the template that will be shown in the comment section when a user select this queue in submission.</p>
            <div class="form-group">
                {!! Form::label('User Form (Optional)') !!}
                {!! Form::textarea('form', $queue->form, ['class' => 'form-control wysiwyg']) !!}
            </div>

            <hr />

            <h5>Queue Type</h5>
            <p>Queue types change how the form changes in functionality.</p>
            <div class="form-group">
                {!! Form::select('queue_type', [null => 'Select a Type'] + $types, $queue->queue_type ?? null, ['class' => 'form-control']) !!}
            </div>

            <hr />

            <h5>Checklist</h5>
            <p>Create a checklist below to force users to acknowledge all necessary steps of submitting to the queue are complete. The user will not be able to submit until they have accepted that they have completed each step.</p>
            <p>Each "line" represents one checkbox that will need to be ticked off.</p>

            <div class="text-right mb-3">
                <a href="#" class="btn btn-info" id="addCheck">Add Step</a>
            </div>
            <table class="table table-sm" id="checkList">
                <thead>
                    <tr>
                        <th width="40%">Step</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody id="checkListBody">
                    @if ($queue->checklist)
                        @foreach ($queue->checklist as $check)
                            <tr class="check-row">
                                <td class="check-row-select">
                                    {!! Form::text('check_text[]', $check, [
                                        'class' => 'form-control',
                                        'placeholder' => 'Enter checklist text',
                                    ]) !!}
                                </td>
                                <td class="text-right"><a href="#" class="btn btn-danger remove-check-button">Remove</a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    @include('widgets._add_submission_limits', ['object' => $queue])

    {{-- blade-formatter-disable --}}
    @include('widgets._add_rewards', [
        'object' => $queue,
        'useForm' => false,
        'showRaffles' => true,
        'showLootTables' => true,
        'showRecipient' => $queue->configSet('can_character_submit') ?? false,
        'info' => '<p>User rewards are credited on a per-user basis, character rewards are rewarded to all characters attached. Mods are able to modify the specific rewards granted at approval time.</p><p class="mb-0">You can add loot tables containing any kind of currencies (both user- and character-attached), but be sure to keep track of which are being distributed! <strong>Character-only currencies cannot be given to users.</strong></p>',
    ])
    {{-- blade-formatter-enable --}}

    <div class="text-right">
        {!! Form::submit($queue->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <div id="checkRowData" class="hide">
        <table class="table table-sm">
            <tbody id="checkRow">
                <tr class="check-row">
                    <td class="check-row-select">{!! Form::text('check_text[]', null, ['class' => 'form-control', 'placeholder' => 'Enter checklist text']) !!}</td>
                    <td class="text-right"><a href="#" class="btn btn-danger remove-check-button">Remove</a></td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($queue->queue_type)
        <hr>
        <h2 class="text-center">Manage Type</h2>
        {!! Form::open(['url' => 'admin/data/queues/types/' . $queue->id]) !!}

        @if (View::exists('admin.queues.types.' . $queue->queue_type))
            @include('admin.queues.types.' . $queue->queue_type, ['data' => $queue->data])
        @endif

        @if ($queue->configSet('consume_items'))
            <h3>Item Preset</h3>
            <p>This queue consumes items, so you have the option to select which items the user may attach to their submission when they make it.</p>
            <div class="text-right mb-3">
                <a href="#" class="btn btn-info" id="addpreset">Add Item</a>
            </div>
            <table class="table table-sm" id="presetTable">
                <thead>
                    <tr>
                        <th width="40%">Item</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody id="presetTableBody">
                    @if (isset($queue->data['items']))
                        @foreach ($queue->data['items'] as $item)
                            <tr class="preset-row">
                                <td class="preset-row-select">
                                    {!! Form::select('item_id[]', $item_limits, $item, [
                                        'class' => 'form-control pet-select selectize',
                                        'placeholder' => 'Select Item',
                                    ]) !!}
                                </td>
                                <td class="text-right"><a href="#" class="btn btn-danger remove-preset-button">Remove</a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        @endif

        <div class="text-right">
            {!! Form::submit('Edit Type Settings', ['class' => 'btn btn-primary']) !!}
        </div>

        {!! Form::close() !!}

        @if ($queue->configSet('consume_items'))
            <div id="presetRowData" class="hide">
                <table class="table table-sm">
                    <tbody id="presetRow">
                        <tr class="preset-row">
                            <td class="preset-row-select">{!! Form::select('item_id[]', $item_limits, null, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}</td>
                            <td class="text-right"><a href="#" class="btn btn-danger remove-preset-button">Remove</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <script>
                $(document).ready(function() {
                    var $presetTable = $('#presetTableBody');
                    var $presetRow = $('#presetRow').find('.preset-row');

                    $('#presetTableBody .selectize').selectize();
                    attachRemovePresetListener($('#presetTableBody .remove-preset-button'));
                    $('#addpreset').on('click', function(e) {
                        e.preventDefault();
                        var $clone = $presetRow.clone();
                        $presetTable.append($clone);
                        $clone.find('.selectize').selectize();
                        attachRemovePresetListener($clone.find('.remove-preset-button'));
                    });

                    function attachRemovePresetListener(node) {
                        node.on('click', function(e) {
                            e.preventDefault();
                            $(this).parent().parent().remove();
                        });
                    }
                });
            </script>
        @endif

        @if (View::exists('admin.queues.types.' . $queue->queue_type . '_post'))
            @include('admin.queues.types.' . $queue->queue_type . '_post', ['data' => $queue->data])
        @endif

        @if (View::exists('admin.queues.types.' . $queue->queue_type . '_images'))
            <h3>Images</h3>
            <p>These additional images are optional, and the types and numbers of these will vary. They can help you further customize the look of the game.
            </p>

            {!! Form::open(['url' => 'admin/data/queue/images/' . $queue->id, 'files' => true]) !!}
            @include('admin.queues.types.' . $queue->queue_type . '_images', ['data' => $queue->data])
            <div class="text-right">
                {!! Form::submit('Edit Images', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        @endif
        <hr>
    @endif

    @if ($queue->id)
        @include('widgets._add_limits', [
            'object' => $queue,
            'hideAutoUnlock' => true,
        ])

        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('queues._queue_entry', ['queue' => $queue])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    @include('widgets._datetimepicker_js')
    @include('js._tinymce_wysiwyg')
    @if (View::exists('admin.queues.types.' . $queue->queue_type . '_js'))
        @include('admin.queues.types.' . $queue->queue_type . '_js', ['data' => $queue->data])
    @endif
    <script>
        $('.selectize').selectize();

        $(document).ready(function() {
            $('.delete-queue-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/queues/delete') }}/{{ $queue->id }}", 'Delete Queue');
            });

            $(".datepicker").datetimepicker({
                dateFormat: "yy-mm-dd",
                timeFormat: 'HH:mm:ss',
            });

            var $checkList = $('#checkListBody');
            var $checkRow = $('#checkRow').find('.check-row');
            var $itemSelect = $('#checkRowData').find('.item-select');

            $('#checkListBody .selectize').selectize();
            attachRemoveCheckListener($('#checkListBody .remove-check-button'));
            $('#addCheck').on('click', function(e) {
                e.preventDefault();
                var $clone = $checkRow.clone();
                $checkList.append($clone);
                $clone.find('.selectize').selectize();
                attachRemoveCheckListener($clone.find('.remove-check-button'));
            });

            function attachRemoveCheckListener(node) {
                node.on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().remove();
                });
            }
        });
    </script>
@endsection
