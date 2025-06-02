@extends('home.layout')

@section('home-title')
    New Submission
@endsection

@section('home-content')
    @if ($isClaim)
        {!! breadcrumbs(['Claims' => 'claims', 'New Claim' => 'claims/new']) !!}
    @else
        {!! breadcrumbs(['Prompt Submissions' => 'submissions', 'New Submission' => 'submissions/new']) !!}
    @endif

    <h1>
        @if ($isClaim)
            Claims Closed
        @else
            {!! breadcrumbs(['Prompt Submissions' => 'submissions', 'New Submission' => 'submissions/new']) !!}
        @endif
    </h1>

    {!! Form::open(['url' => $isClaim ? 'claims/new' : 'submissions/new', 'id' => 'submissionForm']) !!}
    @if (!$isClaim)
        <div class="mb-3">
            {!! Form::label('prompt_id', 'Prompt') !!}
            {!! Form::select('prompt_id', $prompts, null, ['class' => 'form-control selectize', 'id' => 'prompt', 'placeholder' => '']) !!}
        </div>
    @endif
    <div class="mb-3">
        {!! Form::label('url', $isClaim ? 'URL' : 'Submission URL') !!}
        @if ($isClaim)
            {!! add_help('Enter a URL relevant to your claim (for example, a comment proving you may make this claim). This field cannot be left blank.') !!}
        @else
            {!! add_help('Enter the URL of your submission (whether uploaded to dA or some other hosting service). This field cannot be left blank.') !!}
        @endif
        {!! Form::text('url', null, ['class' => 'form-control', 'required']) !!}
    </div>
    <div class="mb-3">
        {!! Form::label('comments', 'Comments (Optional)') !!} {!! add_help('Enter a comment for your ' . ($isClaim ? 'claim' : 'submission') . ' (no HTML). This will be viewed by the mods when reviewing your ' . ($isClaim ? 'claim' : 'submission') . '.') !!}
        {!! Form::textarea('comments', null, ['class' => 'form-control']) !!}
    </div>

    <h2>Rewards</h2>
    @if ($isClaim)
        <p>Select the rewards you would like to claim.</p>
    @else
        <p>Note that any rewards added here are <u>in addition</u> to the default prompt rewards. If you do not require any additional rewards, you can leave this blank.</p>
    @endif
    @include('widgets._loot_select', ['loots' => null, 'showLootTables' => false])
    @if (!$isClaim)
        <div id="rewards" class="mb-3"></div>
    @endif

    <h2>Characters</h2>
    @if ($isClaim)
        <p>If there are character-specific rewards you would like to claim, attach them here. Otherwise, this section can be left blank.</p>
    @endif
    <div id="characters" class="mb-3">
    </div>
    <div class="text-end mb-3">
        <a href="#" class="btn btn-outline-info" id="addCharacter">Add Character</a>
    </div>

    <div class="text-end">
        <a href="#" class="btn btn-primary" id="submitButton">Submit</a>
    </div>
    {!! Form::close() !!}

    @include('widgets._character_select', [
        'characterCurrencies' => \App\Models\Currency\Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id'),
    ])
    @include('widgets._loot_select_row', ['showLootTables' => false])


    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm {{ $isClaim ? 'Claim' : 'Submission' }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will submit the form and put it into the {{ $isClaim ? 'claims' : 'prompt' }} approval queue. You will not be able to edit the contents after the {{ $isClaim ? 'claim' : 'submission' }} has been made. Click the Confirm
                        button to complete the {{ $isClaim ? 'claim' : 'submission' }}.</p>
                    <div class="text-end">
                        <a href="#" id="formSubmit" class="btn btn-primary">Confirm</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
