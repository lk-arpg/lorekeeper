@extends('admin.layout')

@section('admin-title')
    Report (#{{ $report->id }})
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Report Queue' => 'admin/reports/pending', 'Report (#' . $report->id . ')' => $report->viewUrl]) !!}

    @if ($report->status !== 'Closed')
        @if ($report->status == 'Assigned' && Auth::user()->id !== $report->staff_id)
            <div class="alert alert-danger">This report is not assigned to you.</div>
        @elseif($report->status == 'Pending')
            <div class="alert alert-warning">This report needs assigning.</div>
        @endif

        <h1>
            Report (#{{ $report->id }})
            <span class="float-right badge badge-{{ $report->status == 'Pending' ? 'secondary' : ($report->status == 'Closed' ? 'success' : 'danger') }}">{{ $report->status }}</span>
        </h1>
        <div class="mb-1">
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>User</h5>
                </div>
                <div class="col-md-10 col-8">{!! $report->user->displayName !!}</div>
            </div>
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>URL / Title</h5>
                </div>
                <div class="col-md-10 col-8"><a href="{{ $report->url }}">{{ $report->url }}</a></div>
            </div>
            @if ($report->is_br == 1)
                <div class="row">
                    <div class="col-md-2 col-4">
                        <h5>Bug Type</h5>
                    </div>
                    <div class="col-md-10 col-8">{{ ucfirst($report->error_type) . ($report->error_type != 'exploit' ? ' Error' : '') }}</div>
                </div>
            @endif
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>Submitted</h5>
                </div>
                <div class="col-md-10 col-8">{!! format_date($report->created_at) !!} ({{ $report->created_at->diffForHumans() }})</div>
            </div>
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>Assigned to</h5>
                </div>
                <div class="col-md-10 col-8">
                    @if ($report->staff != null)
                        {!! $report->staff->displayName !!}
                    @endif
                </div>
            </div>
        </div>

        <h2>Report Details</h2>
        <div class="card mb-3">
            <div class="card-body">{!! nl2br(htmlentities($report->comments)) !!}</div>
        </div>
        @if (Auth::check() && $report->staff_comments && ($report->user_id == Auth::user()->id || Auth::user()->hasPower('manage_reports')))
            <h2>Staff Comments ({!! $report->staff->displayName !!})</h2>
            <div class="card mb-3">
                <div class="card-body">
                    @if (isset($report->parsed_staff_comments))
                        {!! $report->parsed_staff_comments !!}
                    @else
                        {!! $report->staff_comments !!}
                    @endif
                </div>
            </div>
        @endif

        @if (($report->status == 'Assigned' && $report->user_id == Auth::user()->id) || Auth::user()->hasPower('manage_reports'))
            @comments(['type' => 'Staff-User', 'model' => $report, 'perPage' => 5])
        @endif

        {!! Form::open(['url' => url()->current(), 'id' => 'reportForm']) !!}
        @if ($report->status == 'Assigned' && Auth::user()->id == $report->staff_id)
            @if (Auth::user()->hasPower('manage_reports'))
                <div class="alert alert-warning">Please include a small paragraph on the solution and as many important details as you deem necessary, as the user will no longer be able to view the comments after the report is closed.</div>
            @endif
            <div class="form-group">
                {!! Form::label('staff_comments', 'Staff Comments (Optional)') !!}
                {!! Form::textarea('staff_comments', $report->staffComments, ['class' => 'form-control wysiwyg']) !!}
            </div>
        @endif
        <div class="text-right">
            @if ($report->staff_id == null)
                <a href="#" class="btn btn-danger mr-2" id="assignButton">Assign</a>
            @endif
            @if ($report->status == 'Assigned' && Auth::user()->id == $report->staff_id)
                <a href="#" class="btn btn-success" id="closalButton">Close</a>
            @endif
        </div>
        {!! Form::close() !!}

        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content hide" id="closalContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Closal</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will close the report.</p>
                        <div class="text-right">
                            <a href="#" id="closalSubmit" class="btn btn-success">Close</a>
                        </div>
                    </div>
                </div>
                <div class="modal-content hide" id="assignContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Assignment</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-left">This will assign yourself to the report.</p>
                        <div class="text-right">
                            <a href="#" id="assignSubmit" class="btn btn-danger">Assign</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-danger">This report has already been closed.</div>
        @include('home._report_content', ['report' => $report])
    @endif

@endsection

@if ($report->status !== 'Closed')
    @section('scripts')
        @parent
        @include('js._tinymce_wysiwyg')
        <script>
            $(document).ready(function() {
                $('#closalButton').on('click', function(e) {
                    e.preventDefault();
                    $('#closalContent').removeClass('hide');
                    $('#assignContent').addClass('hide');
                    $('#confirmationModal').modal('show');
                });

                $('#assignButton').on('click', function(e) {
                    e.preventDefault();
                    $('#assignContent').removeClass('hide');
                    $('#closalContent').addClass('hide');
                    $('#confirmationModal').modal('show');
                });

                $('#closalSubmit').on('click', function(e) {
                    e.preventDefault();
                    $('#reportForm').attr('action', '{{ url()->current() }}/close');
                    $('#reportForm').submit();
                });

                $('#assignSubmit').on('click', function(e) {
                    e.preventDefault();
                    $('#reportForm').attr('action', '{{ url()->current() }}/assign');
                    $('#reportForm').submit();
                });
            });
        </script>
    @endsection
@endif
