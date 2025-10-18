@if ($submission->status == 'Draft')
    {!! Form::open(['url' => 'queue-submissions/edit', 'id' => 'submissionForm', 'files' => $queue->configSet('image_upload') ? true : false]) !!}
@else
    {!! Form::open(['url' => 'queue-submissions/new', 'id' => 'submissionForm', 'files' => $queue->configSet('image_upload') ? true : false]) !!}
@endif

@if (Auth::check() && $submission->staff_comments && ($submission->user_id == Auth::user()->id || Auth::user()->hasPower('manage_submissions')))
    <h2>Staff Comments ({!! $submission->staff->displayName !!})</h2>
    <div class="card mb-3">
        <div class="card-body">
            @if (isset($submission->parsed_staff_comments))
                {!! $submission->parsed_staff_comments !!}
            @else
                {!! $submission->staff_comments !!}
            @endif
        </div>
    </div>
@endif

@if (!$submission->id)
    <h4>New Submission For {!! $queue->displayName !!}</h4>
    {!! Form::hidden('queue_id', $queue->id) !!}
@else
    <h4>Submission For {!! $queue->displayName !!}</h4>
@endif

<div class="form-group">
    {!! Form::label('comments', 'Comments (Optional)') !!} {!! add_help('Enter a comment for your submission. This will be viewed by the mods when reviewing your submission.') !!}
    {!! Form::textarea('comments', isset($submission->parsed_comments) ? $submission->parsed_comments : (old('comments') ? Request::get('comments') : ($queue->parsed_form ? $queue->parsed_form : null)), ['class' => 'form-control wysiwyg']) !!}
</div>


<div class="mb-3">
    @include('home.queues._queue', ['staffView' => false])
</div>

@if ($queue->configSet('character_submit') && View::exists('home.queues.types.characters.' . $queue->queue_type . '_select_entry'))
    <div class="card mb-3">
        <div class="card-header h2">
            <a href="#" class="btn btn-outline-info float-right" id="addCharacter">Add Character</a>
            Characters
        </div>
        <div class="card-body" style="clear:both;">
            <div id="characters" class="mb-3">
                @foreach ($submission->characters as $character)
                    @include('home.queues.types.characters.' . $queue->queue_type . '_select_entry')
                @endforeach
                @if (old('slug') && !$submission->id)
                    @php
                        session()->forget('_old_input.character_rewardable_type');
                        session()->forget('_old_input.character_rewardable_id');
                        session()->forget('_old_input.character_rewardable_quantity');
                    @endphp
                    @foreach (array_unique(old('slug')) as $slug)
                        @include('home.queues.types.characters.' . $queue->queue_type . '_select_entry', ['character' => \App\Models\Character\Character::where('slug', $slug)->first()])
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@else
    <div class="card mb-3">
        <div class="card-header h2">Characters</div>
        <div class="card-body">
            <p>This queue does not use characters.</p>
        </div>
    </div>
@endif

<div class="card mb-3">
    <div class="card-header h2">
        Add-Ons
    </div>
    <div class="card-body">
        @if ($queue->configSet('item_consume'))
            <p>If your submission consumes items, attach them here. Otherwise, this section can be left blank. These items will be removed from your inventory but refunded if your submission is
                rejected.</p>
            @if (isset($queue->data['items']))
                <p>This queue has specific requirements for items, and has filtered your inventory out automatically.</p>
                <p>Applicable Items:</p>
                <ul>
                    @foreach ($queue->items as $item)
                        <li>{!! $item->displayName !!}</li>
                    @endforeach
                </ul>
            @endif
            <div id="addons" class="mb-3">
                @include('widgets._inventory_select', [
                    'user' => Auth::user(),
                    'inventory' => $inventory,
                    'categories' => $categories,
                    'selected' => $submission->id ? $submission->getInventory($submission->user) : (old('stack_id') ? array_combine(old('stack_id'), old('stack_quantity')) : []),
                    'page' => $page,
                ])
                @include('widgets._bank_select', [
                    'owner' => Auth::user(),
                    'selected' => $submission->id ? $submission->getCurrencies($submission->user) : (old('currency_id') ? array_combine(old('currency_id')['user-' . Auth::user()->id], old('currency_quantity')['user-' . Auth::user()->id]) : []),
                ])
            </div>
        @else
            <p>This queue does not consume add-ons.</p>
        @endif
    </div>
</div>

@if ($queue->checklist)
    <div class="alert alert-info">
        <p class="mb-0 font-weight-bold h5">Before you submit, make sure you have completed the following:</p>
        <hr class="w-50 ml-0 mb-1" />
        <ul>
            {{-- exclude form_id --}}
            @foreach ($queue->checklist as $key => $item)
                <li>
                    {{ Form::checkbox('checklist[]', $item, false, ['class' => 'form-check-input submission-checklist-box']) }}
                    {{ Form::label('checklist_' . $item, $item, ['class' => 'form-check-label']) }}
                </li>
            @endforeach
        </ul>
        <h5 class="text-danger">❗❗ Double check everything before clicking submit ❗❗</h5>
        <p>If you did not complete all of the above, your submission may be rejected. Staff are not responsible for any issues that arise from incomplete submissions.</p>
    </div>

    <script>
        // ensure that the user has checked all the boxes before submitting
        $(document).ready(function() {
            $('form').submit(function(e) {
                e.preventDefault();
                let checkboxes = $('.submission-checklist-box');

                if (checkboxes.filter(':checked').length != checkboxes.length) {
                    alert('Please ensure you have checked all the boxes before submitting.');
                    return false;
                }

                this.submit();
            });
        });
    </script>
@endif

@if ($submission->status == 'Draft')
    <div class="text-right">
        <a href="#" class="btn btn-danger mr-2" id="cancelButton">Delete Draft</a>
        <a href="#" class="btn btn-secondary mr-2" id="draftButton">Save Draft</a>
        <a href="#" class="btn btn-primary" id="confirmButton">Submit</a>
    </div>
@else
    <div class="text-right">
        <a href="#" class="btn btn-secondary mr-2" id="draftButton">Save Draft</a>
        <a href="#" class="btn btn-primary" id="confirmButton">Submit</a>
    </div>
@endif

{!! Form::close() !!}

@if ($queue->configSet('character_submit') && View::exists('home.queues.types.characters.' . $queue->queue_type . '_select'))
    @include('home.queues.types.characters.' . $queue->queue_type . '_select')
@endif
