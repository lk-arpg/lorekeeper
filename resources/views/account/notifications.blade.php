@extends('account.layout')

@section('account-title')
    Notifications
@endsection

@section('account-content')
    {!! breadcrumbs(['My Account' => Auth::user()->url, 'Notifications' => 'notifications']) !!}

    <h1>Notifications</h1>

    <div class="text-end mb-3">
        {!! Form::open(['url' => 'notifications/clear', 'id' => 'clearForm']) !!}
        <a href="#" class="btn btn-primary" id="clearButton">Clear All</a>
        {!! Form::close() !!}
    </div>
    {!! $notifications->render() !!}

    @foreach ($notifications->pluck('notification_type_id')->unique() as $type)
        <div class="card mb-4">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <span class="float-end h5 mb-2">
                        {!! Form::open(['url' => 'notifications/clear/' . $type]) !!}
                        <span class="badge text-bg-primary">
                            {{ $notifications->where('notification_type_id', $type)->count() }}
                        </span>
                        {!! Form::submit('x clear', ['class' => 'badge btn-primary', 'style' => 'display:inline; border: 0;']) !!}
                        {!! Form::close() !!}
                    </span>
                    <a class="card-title h5 collapse-title mb-2" href="#{{ str_replace(' ', '_', config('lorekeeper.notifications.' . $type . '.name')) }}" data-bs-toggle="collapse">{{ config('lorekeeper.notifications.' . $type . '.name') }}
                    </a>
                    <div id="{{ str_replace(' ', '_', config('lorekeeper.notifications.' . $type . '.name')) }}" class="collapse {{ $notifications->where('notification_type_id', $type)->count() < 5 ? 'show' : '' }} mt-2">
                        <table class="table notifications-table">
                            <thead>
                                <tr>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notifications->where('notification_type_id', $type) as $notification)
                                    <tr class="{{ $notification->is_unread ? 'unread' : '' }}">
                                        <td>{!! $notification->message !!}</td>
                                        <td>{!! format_date($notification->created_at) !!}</td>
                                        <td class="text-end"><a href="#" data-id="{{ $notification->id }}" class="clear-notification"><i class="fas fa-times" aria-hidden="true"></i></a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </li>
            </ul>
        </div>
    @endforeach
    @if (!count($notifications))
        <div class="text-center">No notifications.</div>
    @endif

    {!! $notifications->render() !!}

    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Clear All Notifications</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will clear <strong>all</strong> of your notifications. Are you certain you wish to do so?</p>
                    <div class="text-end">
                        <a href="#" id="clearSubmit" class="btn btn-primary">Clear All</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    <script>
        $(document).ready(function() {

            var $confirmationModal = $('#confirmationModal');
            var $clearButton = $('#clearButton');
            var $clearSubmit = $('#clearSubmit');
            var $clearForm = $('#clearForm');

            $clearButton.on('click', function(e) {
                e.preventDefault();
                $confirmationModal.modal('show');
            });

            $clearSubmit.on('click', function(e) {
                e.preventDefault();
                $clearForm.submit();
            });

            $('.clear-notification').on('click', function(e) {
                e.preventDefault();
                var $row = $(this).parent().parent();
                $.get("{{ url('notifications/delete') }}/" + $(this).data('id'), function(data) {
                    console.log($(this));
                    $row.fadeOut(300, function() {
                        $(this).remove();
                    });
                });
            });

        });
    </script>
@endsection
