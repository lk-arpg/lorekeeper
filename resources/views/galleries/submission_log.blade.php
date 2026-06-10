@extends('galleries.layout')

@section('gallery-title')
    {{ $submission->title }} Log
@endsection

@section('gallery-content')
    {!! breadcrumbs(['gallery' => 'gallery', $submission->gallery->displayName => 'gallery/' . $submission->gallery->id, $submission->title => 'gallery/view/' . $submission->id, 'Log Details' => 'gallery/queue/' . $submission->id]) !!}

    <h1>Log Details
        <span class="float-right badge badge-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Accepted' ? 'success' : 'danger') }}">
            {{ $submission->collaboratorApproval ? $submission->status : 'Pending Collaborator Approval' }}
        </span>
    </h1>

    @include('galleries._queue_submission', ['key' => 0])

    <div class="row">
        <div class="col-md-7">
            @if ($submission->gallery->criteria)
                <div class="card mb-4">
                    <div class="card-header h5">
                        Award Info
                        <a class="small inventory-collapse-toggle collapse-toggle {{ $submission->status == 'Accepted' ? '' : 'collapsed' }}" href="#currencyForm" data-toggle="collapse">
                            Show
                        </a>
                    </div>
                    <div class="card-body collapse {{ $submission->status == 'Accepted' ? 'show' : '' }}" id="currencyForm">
                        @if ($submission->status == 'Accepted')
                            @if (!$submission->is_valued)
                                @if (Auth::user()->hasPower('manage_submissions'))
                                    {!! Form::open(['url' => 'admin/gallery/edit/' . $submission->id . '/value']) !!}
                                    <div class="h5">Contributor Rewards</div>
                                    <p>Adjust the criteria submitted and other options as needed for what the submitter, collaborators, and/or participants, should receive.</p>
                                    @if (isset($submission->data['criterion']))
                                        <div class="card mb-3">
                                            <div class="card-header h3">
                                                Rewards for {!! $submission->collaborators->count() ? implode(', ', $submission->collaborators->pluck('user.displayName')->toArray()) : $submission->user->displayName !!}
                                            </div>
                                            <div class="card-body">
                                                <fieldset disabled>
                                                    @foreach ($submission->data['criterion'] as $key => $criterionData)
                                                        <div class="card p-3 mb-2">
                                                            @php $criterion = \App\Models\Criteria\Criterion::where('id', $criterionData['id'])->first() @endphp
                                                            <div class="h3">{!! $criterion->displayName !!}</div>
                                                            @include('criteria._minimum_requirements', [
                                                                'criterion' => $criterion,
                                                                'values' => $criterionData,
                                                                'minRequirements' => $submission->gallery->criteria->where('criterion_id', $criterionData['id'])->first()->minRequirements,
                                                                'title' => 'Selections',
                                                                'limitByMinReq' => true,
                                                                'id' => $key,
                                                                'criterion_currency' => isset($criterionData['criterion_currency_id']) ? $criterionData['criterion_currency_id'] : $criterion->currency_id,
                                                            ])
                                                        </div>
                                                    @endforeach
                                                </fieldset>
                                                {{--  --}}
                                            </div>
                                        </div>
                                        @if ($submission->gallery->criteria->count() > 0 && isset($criteria))
                                            <div class="card mb-3">
                                                <div class="card-header h2">
                                                    Edit Criteria Rewards
                                                    <button class="btn  btn-outline-info float-right add-calc" type="button">Add Criterion</a>
                                                </div>
                                                <div class="card-body">
                                                    <div id="criteria">
                                                        @foreach ($submission->data['criterion'] ?? [] as $key => $criterionData)
                                                            <div class="card p-3 mb-2">
                                                                @php $criterion = \App\Models\Criteria\Criterion::where('id', $criterionData['id'])->first() @endphp
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div class="h3">{!! $criterion->displayName !!}</div>
                                                                    <div class="text-right btn btn-danger delete-calc">
                                                                        <i class="fas fa-trash"></i>
                                                                    </div>
                                                                </div>
                                                                {!! Form::hidden('criterion[' . $key . '][id]', $criterionData['id'], ['class' => 'criterion-select']) !!}
                                                                @include('criteria._minimum_requirements', [
                                                                    'criterion' => $criterion,
                                                                    'values' => $criterionData,
                                                                    'minRequirements' => $submission->gallery->criteria->where('criterion_id', $criterionData['id'])->first()->minRequirements ?? null,
                                                                    'title' => 'Selections',
                                                                    'limitByMinReq' => true,
                                                                    'id' => $key,
                                                                    'criterion_currency' => isset($criterionData['criterion_currency_id']) ? $criterionData['criterion_currency_id'] : $criterion->currency_id,
                                                                ])
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <div class="alert alert-secondary">
                                            This submission didn't have any criteria specified for rewards for contributors.
                                            Hitting submit will confirm this and clear it from the queue.
                                        </div>
                                        <div class="alert alert-info">
                                            If you add criteria and hit submit, it will award the submission based on the criteria you added.
                                            If you want to mark the submission as accepted without awarding anything, simply hit submit without adding any criteria.
                                        </div>
                                        @if ($submission->gallery->criteria->count() > 0 && isset($criteria))
                                            <div class="card mb-3">
                                                <div class="card-header h2">
                                                    Add Criteria Rewards
                                                    <button class="btn  btn-outline-info float-right add-calc" type="button">Add Criterion</a>
                                                </div>
                                                <div class="card-body">
                                                    <p>Criteria can be used in addition to or in replacement of rewards. They take input on what you are turning in for the prompt in order to calculate your final reward.</p>
                                                    <p>Criteria may populate in with pre-selected minimum requirements for this prompt. </p>
                                                    <div id="criteria"></div>
                                                    <div class="mb-4"></div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                    @if ($submission->participants->count())
                                        <div class="card mb-3">
                                            <div class="card-header h3">Participant Rewards</div>
                                            <div class="card-body">
                                                <p>If there are participants attached to the submission, you can add currency rewards for them here.</p>
                                                @foreach ($submission->participants as $key => $participant)
                                                    <div class="row mb-2">
                                                        <div class="col-md">{!! Form::label($participant->user->name . ' (' . $participant->displayType . ')') !!}</div>
                                                        <div class="col-md-3 btn btn-outline-primary add-currency" data-id="{{ $participant->user->id }}">Add Currency</div>
                                                    </div>
                                                    <div class="participant-rewards-{{ $participant->user->id }}"></div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        {!! Form::checkbox('ineligible', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-onstyle' => 'danger']) !!}
                                        {!! Form::label('ineligible', 'Inelegible / Award No Currency', ['class' => 'form-check-label ml-3']) !!} {!! add_help('When on, this will mark the submission as valued, but will not award currency to any of the users listed.') !!}
                                    </div>
                                    <div class="text-right">
                                        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                                    </div>
                                    {!! Form::close() !!}
                                @else
                                    <p>This submission hasn't been evaluated yet. You'll receive a notification once it has!</p>
                                @endif
                            @else
                                @if (isset($submission->data['staff']))
                                    <p><strong>Processed By:</strong> {!! App\Models\User\User::find($submission->data['staff'])->displayName !!}</p>
                                @endif
                                @if (isset($submission->data['ineligible']) && $submission->data['ineligible'] == 1)
                                    <p>This submission has been evaluated as ineligible for rewards.</p>
                                @else
                                    @if (isset($totals) && count($totals) > 0)
                                        @php
                                            $shouldDivide = Settings::get('gallery_rewards_divided') && $collaboratorsCount;
                                        @endphp
                                        @foreach ($totals as $total)
                                            <h4>{{ $total['name'] }} Criterion</h4>
                                            <div class="row">
                                                @if (!$submission->collaborators->count() || $submission->collaborators->where('user_id', $submission->user_id)->first() == null)
                                                    <div class="col-md-4 mb-3">
                                                        {!! $submission->user->displayName !!}: {!! $total['currency']->display($total['value'] / ($shouldDivide ? $submission->collaborators->count() : 1)) !!}
                                                    </div>
                                                @endif
                                                @if ($submission->collaborators->count())
                                                    <div class="col-md-4 mb-3">
                                                        <h5>Collaborators</h5>
                                                        @foreach ($submission->collaborators as $collaborator)
                                                            {!! $collaborator->user->displayName !!} ({{ $collaborator->data ?? 'No extra details' }}): {!! $total['currency']->display($total['value'] / ($shouldDivide ? $submission->collaborators->count() : 1)) !!}
                                                            <br />
                                                        @endforeach
                                                    </div>
                                                @endif
                                                {{-- TODO: --}}
                                                @if ($submission->participants->count())
                                                    <div class="col-md-4 mb-3">
                                                        <h5>Participants</h5>
                                                        @foreach ($submission->participants as $participant)
                                                            {!! $participant->user->displayName !!} ({{ $participant->displayType }}): {!! $total['currency']->display($total['value'] / ($shouldDivide ? $submission->collaborators->count() : 1)) !!}
                                                            <br />
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <p>This submission didn't have any criteria specified for rewards.</p>
                                    @endif
                                    @if (isset($participantTotals) && count($participantTotals) > 0)
                                        <hr />
                                        <h4>Participant Rewards</h4>
                                        @foreach ($submission->participants as $participant)
                                            <h5>{!! $participant->user->displayName !!} ({{ $participant->displayType }})</h5>
                                            @if (isset($participantTotals[$participant->user_id]) && count($participantTotals[$participant->user_id]) > 0)
                                                <div class="d-flex">
                                                    {!! implode(
                                                        ', ',
                                                        array_map(function ($obj) {
                                                            return $obj['currency']->display($obj['value']);
                                                        }, $participantTotals[$participant->user_id]),
                                                    ) !!}
                                                </div>
                                            @else
                                                <p>No rewards were specified for this participant.</p>
                                            @endif
                                        @endforeach
                                    @endif
                                @endif
                            @endif
                        @else
                            <p>This submission is not eligible for currency awards{{ $submission->status == 'Pending' ? ' yet-- it must be accepted first' : '' }}.</p>
                        @endif
                        @if (isset($totals) && count($totals) > 0)
                            <hr />
                            <div id="totals">
                                @include('galleries._submission_totals', ['totals' => $totals, 'collaboratorsCount' => $collaboratorsCount])
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Staff Comments</h4> {!! Auth::user()->hasPower('staff_comments') ? '(Visible to ' . $submission->credits . ')' : '' !!}
                </div>
                <div class="card-body">
                    @if (isset($submission->parsed_staff_comments))
                        <h5>Staff Comments (Old):</h5>
                        {!! $submission->parsed_staff_comments !!}
                        <hr />
                    @endif
                    <!-- Staff-User Comments -->
                    <div class="w-100">
                        @comments(['model' => $submission, 'type' => 'Staff-User', 'perPage' => 5])
                    </div>
                </div>
            </div>
        </div>
        @if (Auth::user()->hasPower('manage_submissions') && $submission->collaboratorApproval)
            <div class="col-12 col-md-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>[Admin] Vote Info</h5>
                    </div>
                    <div class="card-body">
                        @if ($submission->getVoteData()['raw']->count())
                            @foreach ($submission->getVoteData(1)['raw'] as $vote)
                                <li>
                                    {!! $vote['user']->displayName !!} {{ $vote['user']->id == Auth::user()->id ? '(you)' : '' }}: <span {!! $vote['vote'] == 2 ? 'class="text-success">Accept' : 'class="text-danger">Reject' !!}</span>
                                </li>
                            @endforeach
                        @else
                            <p>No votes have been cast yet!</p>
                        @endif
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>[Admin] Staff Comments</h5> (Only visible to staff)
                    </div>
                    <div class="card-body">
                        <!-- Staff-User Comments -->
                        <div class="container">
                            @comments(['model' => $submission, 'type' => 'Staff-Staff', 'perPage' => 5, 'commentType' => 'staff'])
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div id="copy-calc" class="card p-3 mb-2 pl-0 hide">
        @if (isset($criteria))
            @include('criteria._criterion_selector', ['criteria' => $criteria])
        @endif
    </div>
    {{-- 
    {!! Form::number(
        'value[participant][' . $participant->user->id . ']',
        isset($submission->data['total'])
            ? ($participant->type == 'Comm'
                ? round(($submission->characters->count() ? round($submission->data['total'] * $submission->characters->count()) : $submission->data['total']) / ($submission->collaborators->count() ? $submission->collaborators->count() : '1') / 2)
                : 0)
            : 0,
        ['class' => 'form-control'],
    ) !!} --}}

    <div id="copy-calc-participant" class="row hide">
        <div class="col-md-5 form-group">
            {!! Form::select('value[participant][#][currency_id][]', $currencies, null, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-5 form-group">
            {!! Form::number('value[participant][#][quantity][]', 1, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-2 text-right">
            <button class="btn btn-danger remove-currency" type="button">X</button>
        </div>
    </div>

@endsection
@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            // participants
            $('.add-currency').on('click', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $clone = $(`#copy-calc-participant`).clone();
                $clone.removeClass('hide');
                $clone.find('select').attr('name', `value[participant][${id}][currency_id][]`);
                $clone.find('input').attr('name', `value[participant][${id}][quantity][]`);
                $clone.find('.remove-currency').on('click', function(e) {
                    e.preventDefault();
                    $(this).closest('.row').remove();
                });
                $(`.participant-rewards-${id}`).append($clone);
            });
            $('.remove-currency').on('click', function(e) {
                e.preventDefault();
                $(this).closest('.row').remove();
            });
            // criteria
            $('input[name*=criterion]').on('change input', () => {
                const disabledInputs = $('input[name*=criterion]').filter('[disabled]');
                disabledInputs.prop('disabled', false);
                formObj = {};
                let formData = $('input[name*=criterion]').closest('form').serializeArray();
                disabledInputs.prop('disabled', true);
                formObj['_token'] = formData[0].value;
                formData.forEach((item) => formObj[item.name] = item.value);
                $(`#totals`).load('{{ url('/gallery/queue/totals/' . $submission->id) }}', formObj);
            });

            $('.add-calc').on('click', function(e) {
                e.preventDefault();
                var clone = $('#copy-calc').clone();
                clone.removeClass('hide');
                var input = clone.find('[name*=criterion]');
                var count = $('.criterion-select').length;
                input.attr('name', input.attr('name').replace('#', count))
                clone.find('.criterion-select').on('change', loadForm);
                clone.find('.delete-calc').on('click', deleteCriterion);
                clone.removeAttr('id');
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
                var formId = $(this).attr('name').split('[')[1].replace(']', '');

                if (id) {
                    var form = $(this).closest('.card').find('.form');
                    form.load("{{ url('criteria/gallery') }}/" + id + "/{{ $submission->gallery->id }}/" + formId, (response, status, xhr) => {
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
