<div class="row world-entry">
    @if ($prompt->has_image)
        <div class="col-md-3 world-entry-image"><a href="{{ $prompt->imageUrl }}" data-lightbox="entry" data-title="{{ $prompt->name }}"><img src="{{ $prompt->imageUrl }}" class="world-entry-image" alt="{{ $prompt->name }}" /></a></div>
    @endif
    <div class="{{ $prompt->has_image ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Prompt" :object="$prompt" />
        <div class="mb-3">
            @if (isset($isPage))
                <h1 class="mb-0">{!! $prompt->name !!} <a href="{{ $prompt->idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a></h1>
            @else
                <h2 class="mb-0"><a href="{{ $prompt->idUrl }}">{!! $prompt->name !!}</a></h2>
            @endif
            @if ($prompt->prompt_category_id)
                <div><strong>Category: </strong>{!! $prompt->category->displayName !!}</div>
            @endif
            @if ($prompt->start_at && $prompt->start_at->isFuture())
                <div><strong>Starts: </strong>{!! format_date($prompt->start_at) !!} ({{ $prompt->start_at->diffForHumans() }})</div>
            @endif
            @if ($prompt->end_at)
                <div><strong>Ends: </strong>{!! format_date($prompt->end_at) !!} ({{ $prompt->end_at->diffForHumans() }})</div>
            @endif
        </div>
        <div class="world-entry-text">
            <p>{{ $prompt->summary }}</p>
            <h3 class="mb-3"><a data-toggle="collapse" href="#prompt-{{ $prompt->id }}" @if (isset($isPage)) aria-expanded="true" @endif>Details <i class="fas fa-angle-down"></i></a></h3>
            <div class="collapse @if (isset($isPage)) show @endif" id="prompt-{{ $prompt->id }}">
                @if ($prompt->parsed_description)
                    {!! $prompt->parsed_description !!}
                @else
                    <p>No further details.</p>
                @endif
                @if ($prompt->hide_submissions == 1 && isset($prompt->end_at) && $prompt->end_at > Carbon\Carbon::now())
                    <p class="text-info">Submissions to this prompt are hidden until this prompt ends.</p>
                @elseif($prompt->hide_submissions == 2)
                    <p class="text-info">Submissions to this prompt are hidden.</p>
                @endif
            </div>
            <h3>Rewards</h3>
            @if (!count($prompt->rewards))
                No rewards.
            @else
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th width="70%">Reward</th>
                            <th width="30%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prompt->rewards as $reward)
                            <tr>
                                <td>{!! $reward->reward->displayName !!}</td>
                                <td>{{ $reward->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if (count(getLimits($prompt)))
                <hr />
                @include('widgets._limits', [
                    'object' => $prompt,
                    'hideUnlock' => true,
                ])
            @endif
        </div>
        <div class="text-right">
            @if ($prompt->end_at && $prompt->end_at->isPast())
                <span class="text-secondary">This prompt has ended.</span>
            @elseif($prompt->start_at && $prompt->start_at->isFuture())
                <span class="text-secondary">This prompt is not open for submissions yet.</span>
            @else
                <a href="{{ url('submissions/new?prompt_id=' . $prompt->id) }}" class="btn btn-primary">Submit Prompt</a>
            @endunless
    </div>
</div>
</div>
