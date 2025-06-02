@if ($log->character && $log->character->is_visible == 1)
    <div class="row flex-wrap">
        <div class="col-6 col-md">
            <div class="logs-table-cell">
                <i
                    class="{{ !$user || $log->recipient_id == $user->id ? 'in' : 'out' }}flow bg-{{ !$user || $log->recipient_id == $user->id ? 'success' : 'danger' }} fas {{ !$user || $log->recipient_id == $user->id ? 'fa-arrow-up' : 'fa-arrow-down' }} me-2"></i>
                {!! $log->sender ? $log->sender->displayName : $log->displaySenderAlias !!}
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="logs-table-cell">
                {!! $log->recipient ? $log->recipient->displayName : $log->displayRecipientAlias !!}
            </div>
        </div>
        @if (isset($showCharacter))
            <div class="col-6 col-md">
                <div class="logs-table-cell">
                    {!! $log->character ? $log->character->displayName : '---' !!}
                </div>
            </div>
        @endif
        <div class="col-6 col-md-4">
            <div class="logs-table-cell">
                {!! $log->log !!}
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="logs-table-cell">
                {!! pretty_date($log->created_at) !!}
            </div>
        </div>
    </div>
@endif
