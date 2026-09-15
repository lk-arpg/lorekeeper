@php
    // map the keys and the 'name' value of config('lorekeeper.limits.limit_types')
    $limitTypes = getLimitTypes();
    $limitData = getLimitData();
    $debitableLimits = array_keys(
        array_filter(config('lorekeeper.limits.limit_types'), function ($limit) {
            return $limit['debitable'] == true;
        }),
    );
    $countableLimits = array_keys(
        array_filter(config('lorekeeper.limits.limit_types'), function ($limit) {
            return $limit['countable'] == true;
        }),
    );

    $limits = hasLimits($object) ? getLimits($object) : null;

    // Hiding auto unlock options are good for cases where the user should not know the option exists for that object
    // Prompts are a good example--users shouldn't know they can auto-unlock prompts, as that would be confusing, since
    // the UI for the prompt interactions occurs on submission...
    // ex the limits are checked as part of submitting a prompt,
    // requiring the user to manually unlock the prompt prior, especially if the limits are needed for every prompt submission, would be problematic.
    // also as currently implemented having manual unlocking required would effectively prevent users from submitting the prompt
    // as a refinement, this could be changed to allow for manual unlocking under certain conditions
    if (!isset($hideAutoUnlock)) {
        $hideAutoUnlock = false;
    }
    // Hide "is unlocked" option when it makes sense for a limit to always be one or the other, much like the above
    if (!isset($hideIsUnlocked)) {
        $hideIsUnlocked = false;
        // Opinionated choice, if "Is Unlocked" is hidden, then the limit will be assume to always be a one-time unlock
        if (!isset($isUnlocked)) {
            $isUnlocked = true;
        }
    }
@endphp

<div class="card p-4 mb-3 mt-3" id="limit-card">
    <h3>{{ isset($customHeader) ? $customHeader : 'Limits' }}</h3>

    <p>
        You can add requirements to this object by clicking "Add Limit" & selecting a requirement from the dropdown below.
        <br />
        Requirements are used to determine if a specific action can be performed on an object.
        <br /><b>Note that the checks for requirements are automatic, but their usage needs to be defined in the code.</b>
        <br /><b>Dynamic limits are created in the admin panel, but execute their logic in the code.</b>
    </p>
    {!! isset($info) ? '<p class="alert alert-info">' . $info . '</p>' : '' !!}

    {!! Form::open(['url' => 'admin/limits']) !!}
    {!! Form::hidden('object_model', get_class($object)) !!}
    {!! Form::hidden('object_id', $object->id) !!}
    <div class="limit">
        <div id="limits">
            @if ($limits)
                <h5>Limits for {!! $limits->first()->object->displayName !!}</h5>
            @endif
            <div class="row border-bottom mb-3">
                @if (!$hideIsUnlocked)
                    <div class="col-md form-group">
                        {!! Form::label('is_unlocked', 'Is Unlocked?', ['class' => 'form-label font-weight-bold']) !!}
                        <p>
                            If this is set to "No", the object will continue to be locked until all requirements are met, every time the user attempts to use or interact with it.
                            <br />
                            If this is set to "Yes", the object will be unlocked for the user to interact with indefinitely after the requirements are met once.
                            <br />
                            The "Yes" option is good for one-time unlocks such as shops, locations, certain prompts, etc.
                        </p>
                        {!! Form::select('is_unlocked', [true => 'Yes', false => 'No'], $limits ? $limits->first()->is_unlocked : false, ['class' => 'form-control']) !!}
                    </div>
                @else
                    {!! Form::hidden('is_unlocked', $isUnlocked) !!}
                @endif
                @if (!$hideAutoUnlock)
                    <div class="col-md form-group border-left">
                        {!! Form::label('is_auto_unlocked', 'Automatically Unlock?', ['class' => 'form-label font-weight-bold']) !!} {!! add_help("This only affects objects with 'Is Unlocked?' set to 'Yes'.") !!}
                        <p>
                            If this is set to "No", the user must manually unlock the object by interacting with it - ex. clicking on the "Unlock" button.
                        <div class="text-warning">
                            This will prevent the limits from being used as part of a series of actions, ex. prompt submissions.
                        </div>
                        <br />
                        If this is set to "Yes", the object will be automatically unlocked when the user attempts to access them - ex. when a user enters a shop.
                        <br />
                        This setting is good for preventing users from being debited before being certain they want to interact with the object.
                        <div class="text-danger">
                            This option is not suitable for objects that should have limits as part of an action workflow, ex. prompt submissions.
                        </div>
                        </p>
                        {!! Form::select('is_auto_unlocked', [true => 'Yes', false => 'No'], $limits ? $limits->first()->is_auto_unlocked : false, ['class' => 'form-control']) !!}
                    </div>
                @else
                    {!! Form::hidden('is_auto_unlocked', true) !!}
                @endif
            </div>
            @if ($limits)
                @foreach ($limits as $limit)
                    <div class="limit-row row border-bottom mb-3">
                        <div class="col-md-3 form-group">
                            {!! Form::label('Limit Type') !!}
                            {!! Form::select('limit_type[]', $limitTypes, $limit->limit_type, ['class' => 'form-control limit-selectize limit-type', 'placeholder' => 'Select Limit Type']) !!}
                        </div>
                        <div class="col-md-4 form-group limit-select">
                            {!! Form::label('limit_id[]', 'Limit') !!}
                            {!! Form::select('limit_id[]', $limitData[$limit->limit_type], $limit->limit_id, [
                                'class' => 'form-control limit-selectize ' . strtolower($limit->limit_type) . '-select',
                                'placeholder' => 'Select ' . ($limitTypes[$limit->limit_type] ?? 'Limit'),
                            ]) !!}
                        </div>
                        <div class="col-md-4 limit-modifiers {{ in_array($limit->limit_type, $debitableLimits) || in_array($limit->limit_type, $countableLimits) ? '' : 'hide' }}">
                            <div class="form-group quantity {{ in_array($limit->limit_type, $countableLimits) ? '' : 'hide' }}">
                                {!! Form::label('Quantity') !!}
                                {!! Form::number('quantity[]', $limit->quantity, ['class' => 'form-control', 'placeholder' => 'Enter Quantity', 'min' => 0, 'step' => 1]) !!}
                            </div>
                            <div class="form-group debit {{ in_array($limit->limit_type, $debitableLimits) ? '' : 'hide' }}">
                                {!! Form::label('Debit') !!}
                                {!! Form::select('debit[]', [true => 'Debit', false => 'Don\'t Debit'], $limit->debit, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="limit-delete {{ in_array($limit->limit_type, $debitableLimits) || in_array($limit->limit_type, $countableLimits) ? 'col-md-1' : 'col-md-5' }} d-flex align-items-center">
                            <div class="btn btn-danger remove-limit mx-auto">X</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="btn btn-secondary" id="add-limit">Add Limit</div>
        @if ($limits)
            <i class="fas fa-trash text-danger float-right mt-2 mx-2 fa-2x" data-toggle="tooltip" title="To delete limits, simply remove all existing limits and click 'Edit Limits'"></i>
        @endif
        {!! Form::submit(($limits ? 'Edit' : 'Create') . ' Limits', ['class' => 'btn btn-primary float-right']) !!}
    </div>
    {!! Form::close() !!}
</div>

<div id="limitRow">
    <div class="limit-row row border-bottom mb-3 hide">
        <div class="col-md-3 form-group">
            {!! Form::label('Limit Type') !!}
            {!! Form::select('limit_type[]', $limitTypes, null, ['class' => 'form-control limit-type', 'placeholder' => 'Select Limit Type']) !!}
        </div>
        <div class="col-md-4 form-group limit-select">
        </div>
        <div class="col-md-4 limit-modifiers hide">
            <div class="form-group quantity">
                {!! Form::label('Quantity') !!}
                {!! Form::number('quantity[]', 0, ['class' => 'form-control', 'placeholder' => 'Enter Quantity', 'min' => 0, 'step' => 1]) !!}
            </div>
            <div class="form-group hide debit">
                {!! Form::label('Debit') !!}
                {!! Form::select('debit[]', [true => 'Debit', false => 'Don\'t Debit'], false, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="limit-delete col-md-1 d-flex align-items-center">
            <div class="btn btn-danger remove-limit mx-auto">X</div>
        </div>
    </div>
</div>

<div id="rows" class="hide">
    {!! Form::label('limit_id[]', 'Limit', ['class' => 'limit-label']) !!}
    @foreach ($limitTypes as $limitKey => $limitName)
        {!! Form::select('limit_id[]', $limitData[$limitKey], null, ['class' => 'form-control limit ' . strtolower($limitKey) . '-select', 'placeholder' => 'Select ' . $limitName]) !!}
    @endforeach
</div>

<script>
    $(document).ready(function() {
        let $limitLabel = $('#rows').find('.limit-label');
        var $limitRow = $('#limitRow').find('.limit-row');
        var $rows = $('#rows');
        var debitableLimits = ("{{ implode(',', $debitableLimits) }}").split(',');
        var countableLimits = ("{{ implode(',', $countableLimits) }}").split(',');

        $('.limit-selectize').selectize();

        $('#add-limit').on('click', function(e) {
            e.preventDefault();
            var $clone = $limitRow.clone();
            $('#limits').append($clone);
            $clone.removeClass('hide');
            attachLimitTypeListener($clone.find('.limit-type'));
            $clone.find('.limit-type').selectize();
            attachRemoveListener($clone.find('.remove-limit'));
        });

        $('.limit-type').on('change', function() {
            cloneLimitId($(this));
        });

        // attach remove listener to all .remove-limit
        $('.remove-limit').each(function() {
            attachRemoveListener($(this));
        });

        function attachLimitTypeListener(node) {
            node.on('change', function(e) {
                cloneLimitId($(this));
            });
        }

        function attachRemoveListener(node) {
            node.on('click', function(e) {
                e.preventDefault();
                $(this).closest('.limit-row').remove();
            });
        }

        function cloneLimitId(node) {
            let val = node.val();
            let $limit = node.closest('.limit-row').find('.limit-select');

            let $clone = null;
            $clone = $rows.find('.' + val + '-select').clone();

            $limit.html('');
            $limit.append($limitLabel.clone());
            $limit.append($clone);

            // remove hide on debit/count if type is debitable/countable, otherwise hide it
            var debitable = debitableLimits.includes(val);
            var countable = countableLimits.includes(val);
            node.closest('.limit-row').find('.limit-delete').removeClass('col-md-1');
            node.closest('.limit-row').find('.limit-delete').removeClass('col-md-5');
            if (debitable || countable) {
                node.closest('.limit-row').find('.limit-modifiers').removeClass('hide');
                node.closest('.limit-row').find('.limit-delete').addClass('col-md-1');
                if (debitable) {
                    node.closest('.limit-row').find('.debit').removeClass('hide');
                } else {
                    node.closest('.limit-row').find('.debit').addClass('hide');
                }
                if (countable) {
                    node.closest('.limit-row').find('.quantity').removeClass('hide');
                } else {
                    node.closest('.limit-row').find('.quantity').addClass('hide');
                }
            } else {
                node.closest('.limit-row').find('.limit-modifiers').addClass('hide');
                node.closest('.limit-row').find('.limit-delete').addClass('col-md-5');
            }
        }
    });
</script>
