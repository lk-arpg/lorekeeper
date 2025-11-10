@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Ownership History
@endsection

@section('profile-content')
    @if ($character->is_myo_slot)
        {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url, 'Ownership History' => $character->url . '/ownership']) !!}
    @else
        {!! breadcrumbs([
            $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
            $character->fullName => $character->url,
            'Ownership History' => $character->url . '/ownership',
        ]) !!}
    @endif

    @include('character._header', ['character' => $character])

    <h3>Ownership History</h3>

    {!! $logs->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-6 col-md">
                    <div class="logs-table-cell">Sender</div>
                </div>
                <div class="col-6 col-md">
                    <div class="logs-table-cell">Recipient</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="logs-table-cell">Log</div>
                </div>
                <div class="col-6 col-md">
                    <div class="logs-table-cell">Date</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($logs as $log)
                <div class="logs-table-row">
                    @include('user._ownership_log_row', ['log' => $log, 'user' => $character->user])
                </div>
            @endforeach
        </div>
    </div>
    {!! $logs->render() !!}
@endsection
