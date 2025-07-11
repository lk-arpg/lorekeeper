@if ($submission->status == 'Draft')
    {!! Form::open(['url' => $isClaim ? 'claims/edit' : 'submissions/edit', 'id' => 'submissionForm']) !!}
@else
    {!! Form::open(['url' => $isClaim ? 'claims/new' : 'submissions/new', 'id' => 'submissionForm']) !!}
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

@if (!$isClaim)
    <div class="form-group">
        {!! Form::label('prompt_id', 'Prompt') !!}
        {!! Form::select('prompt_id', $prompts, isset($submission->prompt_id) ? $submission->prompt_id : old('prompt_id') ?? Request::get('prompt_id'), ['class' => 'form-control selectize', 'id' => 'prompt', 'placeholder' => '']) !!}
    </div>
@endif

<div class="row">
    <div class="col-md-{{ config('lorekeeper.settings.allow_gallery_submissions_on_prompts') && !$isClaim ? '6' : '12' }}">
        <div class="form-group">
            {!! Form::label('url', $isClaim ? 'URL (Optional)' : 'Submission URL ' . (config('lorekeeper.settings.allow_gallery_submissions_on_prompts') ? ' / Title' : '') . '(Optional)') !!}
            @if ($isClaim)
                {!! add_help('Enter a URL relevant to your claim (for example, a comment proving you may make this claim).') !!}
            @else
                {!! add_help(
                    'Enter the URL of your submission (whether uploaded to dA or some other hosting service).' .
                        (config('lorekeeper.settings.allow_gallery_submissions_on_prompts') ? ' Alternatively, if you are submitting a gallery link, you can enter the title of your submission here.' : ''),
                ) !!}
            @endif
            {!! Form::text('url', isset($submission->url) ? $submission->url : old('url') ?? Request::get('url'), ['class' => 'form-control', 'required']) !!}
        </div>
    </div>
    @if (config('lorekeeper.settings.allow_gallery_submissions_on_prompts') && !$isClaim)
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('gallery_submission_id', 'Gallery URL (Optional)') !!}
                {!! add_help('Select the gallery submission this prompt is for.') !!}
                {!! Form::select('gallery_submission_id', $userGallerySubmissions, $submission->data['gallery_submission_id'] ?? (old('gallery_submission_id') ?? Request::get('gallery_submission_id')), [
                    'class' => 'form-control selectize',
                    'id' => 'gallery_submission_id',
                    'placeholder' => 'Select Your Gallery Submission',
                ]) !!}
            </div>
        </div>
    @endif
</div>

<div class="form-group">
    {!! Form::label('comments', 'Comments (Optional)') !!} {!! add_help('Enter a comment for your ' . ($isClaim ? 'claim' : 'submission') . ' (no HTML). This will be viewed by the mods when reviewing your ' . ($isClaim ? 'claim' : 'submission') . '.') !!}
    {!! Form::textarea('comments', isset($submission->comments) ? $submission->comments : old('comments') ?? Request::get('comments'), ['class' => 'form-control']) !!}
</div>

@if ($submission->prompt_id)
    <div class="mb-3">
        @include('home._prompt', ['prompt' => $submission->prompt, 'staffView' => false])
    </div>
@endif

<div class="card mb-3">
    <div class="card-header h2">
        Rewards
    </div>
    <div class="card-body">
        @if ($isClaim)
            <p>Select the rewards you would like to claim.</p>
        @else
            <p>Note that any rewards added here are <u>in addition</u> to the default prompt rewards. If you do not require any additional rewards, you can leave this blank.</p>
        @endif

        {{-- previous input --}}
        @if (old('rewardable_type'))
            @php
                $loots = [];
                foreach (old('rewardable_type') as $key => $type) {
                    if (!isset(old('rewardable_id')[$key])) {
                        continue;
                    }
                    $loots[] = (object) [
                        'rewardable_type' => $type,
                        'rewardable_id' => old('rewardable_id')[$key],
                        'quantity' => old('quantity')[$key] ?? 1,
                    ];
                }
            @endphp
        @endif
        @if ($isClaim)
            @include('widgets._loot_select', ['loots' => $submission->id ? $submission->rewards : $loots ?? null, 'showLootTables' => false, 'showRaffles' => true])
        @else
            @include('widgets._loot_select', ['loots' => $submission->id ? $submission->rewards : $loots ?? null, 'showLootTables' => false, 'showRaffles' => false])
        @endif

        @if (!$isClaim)
            <div id="rewards" class="mb-3"></div>
        @endif
    </div>
</div>

<div class="card mb-3">
    <div class="card-header h2">
        <a href="#" class="btn btn-outline-info float-right" id="addCharacter">Add Character</a>
        Characters
    </div>
    <div class="card-body" style="clear:both;">
        @if ($isClaim)
            <p>If there are character-specific rewards you would like to claim, attach them here. Otherwise, this section can be left blank.</p>
        @endif
        <div id="characters" class="mb-3">
            @foreach ($submission->characters as $character)
                @include('widgets._character_select_entry', [
                    'characterCurrencies' => $characterCurrencies,
                    'items' => $items,
                    'tables' => [],
                    'showTables' => false,
                    'character' => $character,
                    'expanded_rewards' => $expanded_rewards,
                ])
            @endforeach
            @if (old('slug') && !$submission->id)
                @php
                    session()->forget('_old_input.character_rewardable_type');
                    session()->forget('_old_input.character_rewardable_id');
                    session()->forget('_old_input.character_rewardable_quantity');
                @endphp
                @foreach (array_unique(old('slug')) as $slug)
                    @include('widgets._character_select_entry', ['character' => \App\Models\Character\Character::where('slug', $slug)->first()])
                @endforeach
            @endif
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header h2">
        Add-Ons
    </div>
    <div class="card-body">
        <p>If your {{ $isClaim ? 'claim' : 'submission' }} consumes items, attach them here. Otherwise, this section can be left blank. These items will be removed from your inventory but refunded if your {{ $isClaim ? 'claim' : 'submission' }} is
            rejected.</p>
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
    </div>
</div>

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

@include('widgets._character_select', ['characterCurrencies' => $characterCurrencies, 'showLootTables' => false])
@if ($isClaim)
    @include('widgets._loot_select_row', ['items' => $items, 'currencies' => $currencies, 'showLootTables' => false, 'showRaffles' => true])
@else
    @include('widgets._loot_select_row', ['items' => $items, 'currencies' => $currencies, 'showLootTables' => false, 'showRaffles' => false])
@endif
