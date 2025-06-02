<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md mb-4 text-center">
                <a href="{{ $submission->url }}">@include('widgets._gallery_thumb', ['submission' => $submission])</a>
            </div>
            <div class="col-md text-center align-self-center">
                <h5>{!! $submission->displayName !!}</h5>
                @if (isset($submission->content_warning))
                    <p><span class="text-danger"><strong>Content Warning:</strong></span> {!! nl2br(htmlentities($submission->content_warning)) !!}</p>
                @endif
                @if (isset($queue) && $queue)
                    <span style="font-size:95%;" class="badge badge-{{ $submission->status == 'Accepted' ? 'success' : ($submission->status == 'Rejected' ? 'danger' : 'secondary') }}">{{ $submission->status }}</span> ・
                @endif
                In {!! $submission->gallery->displayName !!} ・ By {!! $submission->credits !!}<br />
                Submitted {!! pretty_date($submission->created_at) !!} ・ Last updated {!! pretty_date($submission->updated_at) !!}

                @if ($submission->status == 'Pending' && $submission->collaboratorApproval && Auth::user()->hasPower('manage_submissions'))
                    <div class="row mt-2">
                        <div class="col-6 text-end text-danger">
                            {{ $submission->getVoteData()['reject'] }}/{{ $submission->gallery->votes_required }}
                            {!! Form::open(['url' => 'admin/gallery/edit/' . $submission->id . '/reject', 'id' => 'voteRejectForm']) !!}
                            <button class="btn {{ ($submission->getVoteData()['raw']->get(Auth::user()->id)['vote'] ?? 0) == 1 ? 'btn-danger' : 'btn-outline-danger' }}" style="min-width:40px;" data-action="reject"><i
                                    class="fas fa-times"></i></button>
                            {!! Form::close() !!}
                        </div>
                        <div class="col-6 text-start text-success">
                            {{ $submission->getVoteData()['approve'] }}/{{ $submission->gallery->votes_required }}
                            {!! Form::open(['url' => 'admin/gallery/edit/' . $submission->id . '/accept', 'id' => 'voteApproveForm']) !!}
                            <button class="btn {{ ($submission->getVoteData()['raw']->get(Auth::user()->id)['vote'] ?? 0) == 2 ? 'btn-success' : 'btn-outline-success' }}" style="min-width:40px;" data-action="approve"><i
                                    class="fas fa-check"></i></button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                @endif

                @if (isset($queue) && $queue)
                    <h6 class="mt-2">{{ $submission->comments->where('type', 'Staff-User')->count() }}
                        {{ Auth::user()->hasPower('manage_submissions') ? 'Staff↔User Comment' . ($submission->comments->where('type', 'Staff-User')->count() != 1 ? 's' : '') . ' ・ ' : 'Staff Comment' }}
                        {{ Auth::user()->hasPower('manage_submissions') ? $submission->comments->where('type', 'Staff-Staff')->count() . ' Staff↔Staff Comment' . ($submission->comments->where('type', 'Staff-Staff')->count() != 1 ? 's' : '') : '' }}
                    </h6>
                    <h6 class="mt-2"><a href="{{ $submission->queueUrl }}">Detailed Log</a></h6>
                @endif
            </div>
        </div>
    </div>
</div>
