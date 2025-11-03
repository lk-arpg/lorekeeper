@extends('admin.layout')

@section('admin-title')
    Dashboard
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Home' => 'admin']) !!}

    <h1>
        Admin Dashboard</h1>
    <div class="row">
        @if (Auth::user()->hasPower('manage_submissions'))
            <div class="col-sm-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Prompt Submissions @if ($submissionCount)
                                <span class="badge badge-primary">{{ $submissionCount }}</span>
                            @endif
                        </h5>
                        <p class="card-text">
                            @if ($submissionCount)
                                {{ $submissionCount }} submission{{ $submissionCount == 1 ? '' : 's' }} awaiting processing.
                            @else
                                The submission queue is clear. Hooray!
                            @endif
                        </p>
                        <div class="text-right">
                            <a href="{{ url('admin/submissions/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Claims @if ($claimCount)
                                <span class="badge badge-primary">{{ $claimCount }}</span>
                            @endif
                        </h5>
                        <p class="card-text">
                            @if ($claimCount)
                                {{ $claimCount }} claim{{ $claimCount == 1 ? '' : 's' }} awaiting processing.
                            @else
                                The claim queue is clear. Hooray!
                            @endif
                        </p>
                        <div class="text-right">
                            <a href="{{ url('admin/claims/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                        </div>
                    </div>
                </div>
            </div>



            @if (config('lorekeeper.extensions.queue_creator.expand_in_admin_index'))
                @foreach ($queues as $queue)
                    @if ($queue->staff_rank_id === null || in_array(Auth::user()->rank_id, $queue->staff_rank_id))
                        <div class="col-sm-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    @php $queueCount = $queue->submissions('pending')->count(); @endphp
                                    <h5 class="card-title">{{ $queue->name }} Submissions @if ($queueCount)
                                            <span class="badge badge-primary">{{ $queueCount }}</span>
                                        @endif
                                    </h5>
                                    <p class="card-text">
                                        @if ($queueCount)
                                            {{ $queueCount }} {{ $queue->name }} submission{{ $queueCount == 1 ? '' : 's' }} awaiting processing.
                                        @else
                                            The {{ $queue->name }} submission queue is clear. Hooray!
                                        @endif
                                    </p>
                                    <div class="text-right">
                                        <a href="{{ url('admin/queue-submissions/' . $queue->id . '/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="col-sm-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Queue Submissions @if ($queueCount)
                                    <span class="badge badge-primary">{{ $queueCount }}</span>
                                @endif
                            </h5>
                            <p class="card-text">
                                @if ($queueCount)
                                    {{ $queueCount }} custom submission{{ $queueCount == 1 ? '' : 's' }} awaiting processing.
                                @else
                                    The custom submission queue is clear. Hooray!
                                @endif
                            </p>
                            <div class="text-right">
                                <a href="{{ url('admin/queue-submissions/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif



        @endif
        @if (Auth::user()->hasPower('manage_characters'))
            <div class="col-sm-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Design Updates @if ($designCount)
                                <span class="badge badge-primary">{{ $designCount }}</span>
                            @endif
                        </h5>
                        <p class="card-text">
                            @if ($designCount)
                                {{ $designCount }} design update{{ $designCount == 1 ? '' : 's' }} awaiting processing.
                            @else
                                The design update approval queue is clear. Hooray!
                            @endif
                        </p>
                        <div class="text-right">
                            <a href="{{ url('admin/design-approvals/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">MYO Approvals @if ($myoCount)
                                <span class="badge badge-primary">{{ $myoCount }}</span>
                            @endif
                        </h5>
                        <p class="card-text">
                            @if ($myoCount)
                                {{ $myoCount }} MYO slot{{ $myoCount == 1 ? '' : 's' }} awaiting processing.
                            @else
                                The MYO slot approval queue is clear. Hooray!
                            @endif
                        </p>
                        <div class="text-right">
                            <a href="{{ url('admin/myo-approvals/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                        </div>
                    </div>
                </div>
            </div>
            @if ($openTransfersQueue)
                <div class="col-sm-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Character Transfers @if ($transferCount + $tradeCount)
                                    <span class="badge badge-primary">{{ $transferCount + $tradeCount }}</span>
                                @endif
                            </h5>
                            <p class="card-text">
                                @if ($transferCount + $tradeCount)
                                    {{ $transferCount + $tradeCount }} character transfer{{ $transferCount + $tradeCount == 1 ? '' : 's' }} and/or trade{{ $transferCount + $tradeCount == 1 ? '' : 's' }} awaiting processing.
                                @else
                                    The character transfer/trade queue is clear. Hooray!
                                @endif
                            </p>
                            <div class="text-right">
                                <a href="{{ url('admin/masterlist/transfers/incoming') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
        @if (Auth::user()->hasPower('manage_reports'))
            <div class="col-sm-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Reports @if ($reportCount || $assignedReportCount)
                                <span class="badge badge-primary">{{ $reportCount + $assignedReportCount }}</span>
                            @endif
                        </h5>
                        <p class="card-text">
                            @if ($reportCount || $assignedReportCount)
                                @if ($reportCount)
                                    {{ $reportCount }} report{{ $reportCount == 1 ? '' : 's' }} awaiting assignment.
                                @endif
                                {!! $reportCount && $assignedReportCount ? '<br/>' : '' !!}
                                @if ($assignedReportCount)
                                    {{ $assignedReportCount }} report{{ $assignedReportCount == 1 ? '' : 's' }} awaiting processing.
                                @endif
                            @else
                                The report queue is clear. Hooray!
                            @endif
                        </p>
                        <div class="text-right">
                            <a href="{{ url('admin/reports/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (!Auth::user()->hasPower('manage_submissions') && !Auth::user()->hasPower('manage_characters') && !Auth::user()->hasPower('manage_reports'))
            <div class="card p-4 col-12">
                <h5 class="card-title">You do not have a rank that allows you to access any queues.</h5>
                <p class="mb-1">
                    Refer to the sidebar for what you can access as a staff member.
                </p>
                <p class="mb-0">
                    If you believe this to be in error, contact your site administrator.
                </p>
            </div>
        @endif
        @if ($galleryRequireApproval)
            <div class="col-sm-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Gallery Submissions @if ($gallerySubmissionCount)
                                <span class="badge badge-primary">{{ $gallerySubmissionCount }}</span>
                            @endif
                        </h5>
                        <p class="card-text">
                            @if ($gallerySubmissionCount)
                                {{ $gallerySubmissionCount }} gallery submission{{ $gallerySubmissionCount == 1 ? '' : 's' }} awaiting processing.
                            @else
                                The gallery submission queue is clear. Hooray!
                            @endif
                        </p>
                        <div class="text-right">
                            <a href="{{ url('admin/gallery/submissions/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if ($galleryCurrencyAwards)
            <div class="col-sm-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Gallery Currency Awards @if ($galleryAwardCount)
                                <span class="badge badge-primary">{{ $galleryAwardCount }}</span>
                            @endif
                        </h5>
                        <p class="card-text">
                            @if ($galleryAwardCount)
                                {{ $galleryAwardCount }} gallery submission{{ $galleryAwardCount == 1 ? '' : 's' }} awaiting currency rewards.
                            @else
                                The gallery currency award queue is clear. Hooray!
                            @endif
                        </p>
                        <div class="text-right">
                            <a href="{{ url('admin/gallery/currency/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
