@extends('galleries.layout')

@section('gallery-title')
    {{ $submission->title }} Log
@endsection

@section('gallery-content')
    {!! breadcrumbs(['gallery' => 'gallery', $submission->gallery->displayName => 'gallery/' . $submission->gallery->id, $submission->title => 'gallery/view/' . $submission->id, 'Log Details' => 'gallery/queue/' . $submission->id]) !!}

    <h1>Log Details
        <span
            class="float-right badge badge-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Accepted' ? 'success' : 'danger') }}">{{ $submission->collaboratorApproval ? $submission->status : 'Pending Collaborator Approval' }}</span>
    </h1>

    @include('galleries._queue_submission', ['key' => 0])

    <div class="row">
        <div class="col col-md">
            @if (Settings::get('gallery_submissions_reward_currency') && $submission->gallery->currency_enabled)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>{!! $currency->displayName !!} Award Info <a class="small inventory-collapse-toggle collapse-toggle {{ $submission->status == 'Accepted' ? '' : 'collapsed' }}" href="#currencyForm" data-toggle="collapse">Show</a></h5>
                    </div>
                    <div class="card-body collapse {{ $submission->status == 'Accepted' ? 'show' : '' }}" id="currencyForm">
                        @if ($submission->status == 'Accepted')
                            @if (!$submission->is_valued)
                                @if (Auth::user()->hasPower('manage_submissions'))
                                    <p>Enter in the amount of {{ $currency->name }} that {{ $submission->collaborators->count() ? 'each collaborator' : 'the submitting user' }}{{ $submission->participants->count() ? ' and any participants' : '' }}
                                        should receive. The suggested amount has been pre-filled for you based on the provided form responses, but this is only a guideline based on user input and should be verified and any adjustments made as necessary.
                                    </p>
                                    {!! Form::open(['url' => 'admin/gallery/edit/' . $submission->id . '/value']) !!}
                                    @if (!$submission->collaborators->count() || $submission->collaborators->where('user_id', $submission->user_id)->first() == null)
                                        <div class="form-group">
                                            {!! Form::label($submission->user->name) !!}:
                                            {!! Form::number(
                                                'value[submitted][' . $submission->user->id . ']',
                                                isset($submission->data['total'])
                                                    ? round(($submission->characters->count() ? round($submission->data['total'] * $submission->characters->count()) : $submission->data['total']) / ($submission->collaborators->count() ? $submission->collaborators->count() : '1'))
                                                    : 0,
                                                ['class' => 'form-control'],
                                            ) !!}
                                        </div>
                                    @endif
                                    @if ($submission->collaborators->count())
                                        @foreach ($submission->collaborators as $key => $collaborator)
                                            <div class="form-group">
                                                {!! Form::label($collaborator->user->name . ' (' . $collaborator->data . ')') !!}:
                                                {!! Form::number(
                                                    'value[collaborator][' . $collaborator->user->id . ']',
                                                    isset($submission->data['total'])
                                                        ? round(($submission->characters->count() ? round($submission->data['total'] * $submission->characters->count()) : $submission->data['total']) / ($submission->collaborators->count() ? $submission->collaborators->count() : '1'))
                                                        : 0,
                                                    ['class' => 'form-control'],
                                                ) !!}
                                            </div>
                                        @endforeach
                                    @endif
                                    @if ($submission->participants->count())
                                        @foreach ($submission->participants as $key => $participant)
                                            <div class="form-group">
                                                {!! Form::label($participant->user->name . ' (' . $participant->displayType . ')') !!}:
                                                {!! Form::number(
                                                    'value[participant][' . $participant->user->id . ']',
                                                    isset($submission->data['total'])
                                                        ? ($participant->type == 'Comm'
                                                            ? round(($submission->characters->count() ? round($submission->data['total'] * $submission->characters->count()) : $submission->data['total']) / ($submission->collaborators->count() ? $submission->collaborators->count() : '1') / 2)
                                                            : 0)
                                                        : 0,
                                                    ['class' => 'form-control'],
                                                ) !!}
                                            </div>
                                        @endforeach
                                    @endif
                                    <div class="form-group">
                                        {!! Form::checkbox('ineligible', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-onstyle' => 'danger']) !!}
                                        {!! Form::label('ineligible', 'Inelegible/Award No Currency', ['class' => 'form-check-label ml-3']) !!} {!! add_help('When on, this will mark the submission as valued, but will not award currency to any of the users listed.') !!}
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
                                    <p>This submission has been evaluated as ineligible for {{ $currency->name }} rewards.</p>
                                @else
                                    <p>{{ $currency->name }} has been awarded for this submission.</p>
                                    <div class="row">
                                        @if (isset($submission->data['value']['submitted']))
                                            <div class="col-md-4">
                                                {!! $submission->user->displayName !!}: {!! $currency->display($submission->data['value']['submitted'][$submission->user->id]) !!}
                                            </div>
                                        @endif
                                        @if ($submission->collaborators->count())
                                            <div class="col-md-4">
                                                @foreach ($submission->collaborators as $collaborator)
                                                    {!! $collaborator->user->displayName !!} ({{ $collaborator->data }}): {!! $currency->display($submission->data['value']['collaborator'][$collaborator->user->id]) !!}
                                                    <br />
                                                @endforeach
                                            </div>
                                        @endif
                                        @if ($submission->participants->count())
                                            <div class="col-md-4">
                                                @foreach ($submission->participants as $participant)
                                                    {!! $participant->user->displayName !!} ({{ $participant->displayType }}): {!! $currency->display($submission->data['value']['participant'][$participant->user->id]) !!}
                                                    <br />
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        @else
                            <p>This submission is not eligible for currency awards{{ $submission->status == 'Pending' ? ' yet-- it must be accepted first' : '' }}.</p>
                        @endif
                        <hr />
                        @if (isset($submission->data['total']))
                            <h6>Form Responses:</h6>
                            <div class="row mb-2">
                                @foreach ($submission->data['currencyData'] as $key => $data)
                                    <div class="col-md-3 text-center">
                                        @if (isset($data) && isset(config('lorekeeper.group_currency_form')[$key]))
                                            <strong>{{ config('lorekeeper.group_currency_form')[$key]['name'] }}:</strong><br />
                                            @if (config('lorekeeper.group_currency_form')[$key]['type'] == 'choice')
                                                @if (isset(config('lorekeeper.group_currency_form')[$key]['multiple']) && config('lorekeeper.group_currency_form')[$key]['multiple'] == 'true')
                                                    @foreach ($data as $answer)
                                                        {{ config('lorekeeper.group_currency_form')[$key]['choices'][$answer] ?? '-' }}<br />
                                                    @endforeach
                                                @else
                                                    {{ config('lorekeeper.group_currency_form')[$key]['choices'][$data] }}
                                                @endif
                                            @else
                                                {{ config('lorekeeper.group_currency_form')[$key]['type'] == 'checkbox' ? (config('lorekeeper.group_currency_form')[$key]['value'] == $data ? 'True' : 'False') : $data }}
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @if (Auth::user()->hasPower('manage_submissions') && isset($submission->data['total']))
                                <h6>[Admin]</h6>
                                <p class="text-center">
                                    <strong>Calculated Total:</strong> {{ $submission->data['total'] }}
                                    @if ($submission->characters->count())
                                        ・ <strong> Times {{ $submission->characters->count() }} Characters:</strong> {{ round($submission->data['total'] * $submission->characters->count()) }}
                                    @endif
                                    @if ($submission->collaborators->count())
                                        <br /><strong>Divided by {{ $submission->collaborators->count() }} Collaborators:</strong> {{ round($submission->data['total'] / $submission->collaborators->count()) }}
                                        @if ($submission->characters->count())
                                            ・ <strong> Times {{ $submission->characters->count() }} Characters:</strong> {{ round(round($submission->data['total'] * $submission->characters->count()) / $submission->collaborators->count()) }}
                                        @endif
                                    @endif
                                    <br />For a suggested {!! $currency->display(
                                        round(($submission->characters->count() ? round($submission->data['total'] * $submission->characters->count()) : $submission->data['total']) / ($submission->collaborators->count() ? $submission->collaborators->count() : '1')),
                                    ) !!}{{ $submission->collaborators->count() ? ' per collaborator' : '' }}
                                </p>
                            @endif
                        @else
                            <p>This submission does not have form data associated with it.</p>
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
                    <div class="container">
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

@endsection
