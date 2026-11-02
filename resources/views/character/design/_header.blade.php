<h1>
    Request (#{{ $request->id }}): {!! $request->character ? $request->character->displayName : 'Deleted Character [#' . $request->character_id . ']' !!}
    <span class="float-right badge badge-{{ $request->status == 'Draft' || $request->status == 'Pending' ? 'secondary' : ($request->status == 'Approved' ? 'success' : 'danger') }}">{{ $request->status }}
</h1>

@if (isset($request->staff_id))
    @if ($request->staff_comments && ($request->user_id == Auth::user()->id || Auth::user()->hasPower('manage_characters')))
        <h5 class="text-danger">Staff Comments ({!! $request->staff->displayName !!})</h5>
        <div class="card border-danger mb-3">
            <div class="card-body">{!! nl2br(htmlentities($request->staff_comments)) !!}</div>
        </div>
    @else
        <p>No staff comment was provided.</p>
    @endif
@endif

@if ($request->status != 'Draft' && Auth::user()->hasPower('manage_characters') && config('lorekeeper.extensions.design_update_voting'))
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="text-left">{{ $request->status == 'Pending' ? 'Vote' : 'Past Votes' }} on this {{ $request->update_type == 'MYO' ? 'MYO Submission' : 'Design Update' }}
                @if ($request->status == 'Pending')
                    <span class="text-right float-right">
                        <div class="row">
                            <div class="col-sm-6 text-center text-danger">
                                {{ $request->getVoteData()['reject'] }}/{{ Settings::get('design_votes_needed') }}
                                {!! Form::open(['url' => 'admin/designs/vote/' . $request->id . '/reject', 'id' => 'voteRejectForm']) !!}
                                <button class="btn {{ ($request->getVoteData()['raw']->get(Auth::user()->id)['vote'] ?? 0) == 1 ? 'btn-danger' : 'btn-outline-danger' }}" style="min-width:40px;" data-action="reject"><i
                                        class="fas fa-times"></i></button>
                                {!! Form::close() !!}
                            </div>
                            <div class="col-sm-6 text-center text-success">
                                {{ $request->getVoteData()['approve'] }}/{{ Settings::get('design_votes_needed') }}
                                {!! Form::open(['url' => 'admin/designs/vote/' . $request->id . '/approve', 'id' => 'voteApproveForm']) !!}
                                <button class="btn {{ ($request->getVoteData()['raw']->get(Auth::user()->id)['vote'] ?? 0) == 2 ? 'btn-success' : 'btn-outline-success' }}" style="min-width:40px;" data-action="approve"><i
                                        class="fas fa-check"></i></button>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </span>
                @endif
            </h5>
            <p>
                {{ $request->update_type == 'MYO' ? 'MYO Submissions' : 'Design updates' }} need {{ Settings::get('design_votes_needed') }} votes before they are considered approved. Note that this does not automatically process the submission
                in any case, only indicate a consensus.
            </p>
            <hr />
            @if (isset($request->vote_data) && $request->vote_data)
                <h4>Votes:</h4>
                <div class="row">
                    <div class="col-md">
                        <h5>Reject:</h5>
                        <ul>
                            @foreach ($request->getVoteData(1)['raw']->where('vote', 1) as $vote)
                                <li>
                                    {!! $vote['user']->displayName !!}{{ $vote['user']->id == Auth::user()->id ? ' (you)' : '' }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md">
                        <h5>Approve:</h5>
                        <ul>
                            @foreach ($request->getVoteData(1)['raw']->where('vote', 2) as $vote)
                                <li>
                                    {!! $vote['user']->displayName !!}{{ $vote['user']->id == Auth::user()->id ? ' (you)' : '' }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                <p>No votes have been cast yet!</p>
            @endif
        </div>
    </div>
@endif

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ set_active('designs/' . $request->id) }}" href="{{ url('designs/' . $request->id) }}">
            @if ($request->is_complete)
                <i class="text-success fas fa-check-circle fa-fw mr-2"></i>
            @endif Status
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ set_active('designs/' . $request->id . '/comments') }}" href="{{ url('designs/' . $request->id . '/comments') }}"><i
                class="text-{{ $request->has_comments ? 'success far fa-circle' : 'danger fas fa-times' }} fa-fw mr-2"></i> Comments</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ set_active('designs/' . $request->id . '/image') }}" href="{{ url('designs/' . $request->id . '/image') }}"><i class="text-{{ $request->has_image ? 'success far fa-circle' : 'danger fas fa-times' }} fa-fw mr-2"></i>
            Masterlist Image</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ set_active('designs/' . $request->id . '/addons') }}" href="{{ url('designs/' . $request->id . '/addons') }}"><i
                class="text-{{ $request->has_addons ? 'success far fa-circle' : 'danger fas fa-times' }} fa-fw mr-2"></i> Add-ons</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ set_active('designs/' . $request->id . '/traits') }}" href="{{ url('designs/' . $request->id . '/traits') }}"><i
                class="text-{{ $request->has_features ? 'success far fa-circle' : 'danger fas fa-times' }} fa-fw mr-2"></i> Traits</a>
    </li>
</ul>
