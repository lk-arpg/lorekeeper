<div class="card h-100">
    <div class="m-1">
        <div class="row">
            <div class="col-md-6 text-center align-self-center">
                <a href="{{ $character->character->url }}"><img src="{{ $loop->count == 1 ? $character->image->imageUrl : $character->image->thumbnailUrl }}" class="mw-100 img-thumbnail" alt="{{ $character->fullName }}" /></a>
            </div>
            <div class="col-md text-center">
                <div class="mt-2">
                    <h5>
                        {{ $character->displayType }}: <a href="{{ $character->character->url }}">{!! $character->character->slug !!}</a> ・ <span
                            class="{{ $character->is_open && $character->sales->is_open ? 'text-success' : '' }}">[{{ $character->is_open && $character->sales->is_open ? 'Open' : 'Closed' }}]</span><br />
                        <small>
                            {!! $character->image->species->displayName !!} ・ {!! $character->image->rarity->displayName !!}<br />
                        </small>
                    </h5>

                    @if ($loop->count == 1)
                        <div class="mb-2">
                            @if (config('lorekeeper.extensions.traits_by_category'))
                                <div>
                                    @php
                                        $traitgroup = $character->image->features()->get()->groupBy('feature_category_id');
                                    @endphp
                                    @if ($character->image->features()->count())
                                        @foreach ($traitgroup as $key => $group)
                                            <div>
                                                @if ($group->count() > 1)
                                                    <div>
                                                        <strong>{!! $key ? $group->first()->feature->category->displayName : 'Miscellaneous' !!}:</strong>
                                                        @foreach ($group as $feature)
                                                            {!! $feature->feature->displayName !!}@if ($feature->data)
                                                                ({{ $feature->data }})
                                                            @endif{{ !$loop->last ? ', ' : '' }}
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <strong>{!! $key ? $group->first()->feature->category->displayName : 'Miscellaneous' !!}:</strong>
                                                    {!! $group->first()->feature->displayName !!}
                                                    @if ($group->first()->data)
                                                        ({{ $group->first()->data }})
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div>No traits listed.</div>
                                    @endif
                                </div>
                            @else
                                <div>
                                    <?php $features = $character->image->features()->with('feature.category')->get(); ?>
                                    @if ($features->count())
                                        @foreach ($features as $feature)
                                            <div>
                                                @if ($feature->feature->feature_category_id)
                                                    <strong>{!! $feature->feature->category->displayName !!}:</strong>
                                                    @endif {!! $feature->feature->displayName !!} @if ($feature->data)
                                                        ({{ $feature->data }})
                                                    @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div>No traits listed.</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <h6>
                        <div class="mb-2">
                            Design:
                            @foreach ($character->image->designers as $designer)
                                {!! $designer->displayLink() !!}{{ !$loop->last ? ', ' : '' }}
                            @endforeach ・
                            Art:
                            @foreach ($character->image->artists as $artist)
                                {!! $artist->displayLink() !!}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </div>

                        {!! $character->price !!}
                        {!! isset($character->link) || isset($character->data['end_point']) ? '<br/>' : '' !!}
                        @if (isset($character->data['end_point']))
                            {{ $character->data['end_point'] }}
                        @endif
                        {{ isset($character->link) && (!isset($character->sales->comments_open_at) || (Auth::check() && Auth::user()->hasPower('edit_pages')) || $character->sales->comments_open_at < Carbon\Carbon::now()) && isset($character->data['end_point']) ? ' ・ ' : '' }}
                        @if (isset($character->link) && (!isset($character->sales->comments_open_at) || (Auth::check() && Auth::user()->hasPower('edit_pages')) || $character->sales->comments_open_at < Carbon\Carbon::now()))
                            <a href="{{ $character->link }}">{{ $character->typeLink }}</a>
                        @endif
                    </h6>

                    <p>{!! $character->description !!}</p>
                </div>
            </div>
        </div>
    </div>
</div>
