@extends('home.trades.listings.layout')

@section('trade-title')
    Listing (#{{ $listing->id }})
@endsection

@section('trade-content')
    {!! breadcrumbs(['Trades' => 'trades/open', 'Listings' => 'trades/listings', 'Listing (#' . $listing->id . ')' => 'trades/listings/' . $listing->id]) !!}

    <h1>
        {!! $listing->displayName !!}
        <span class="float-right badge badge-{{ $listing->isActive ? 'success' : 'secondary' }}">{{ $listing->isActive ? 'Active' : 'Expired' }}</span>
        <a class="float-right mr-2" href="{{ url('reports/new?url=') . $listing->url }}">
            <i class="fas fa-exclamation-triangle" data-toggle="tooltip" title="Click here to report this trade listing." style="opacity: 50%;"></i>
        </a>
    </h1>

    <div class="mb-1">
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>User</h5>
            </div>
            <div class="col-md-10 col-8">{!! $listing->user->displayName !!}</div>
        </div>
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>Created</h5>
            </div>
            <div class="col-md-10 col-8">{!! format_date($listing->created_at) !!} ({{ $listing->created_at->diffForHumans() }})</div>
        </div>
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>Last Updated</h5>
            </div>
            <div class="col-md-10 col-8">{!! format_date($listing->updated_at) !!} ({{ $listing->updated_at->diffForHumans() }})</div>
        </div>
        <h3>
            @if ($listing->isActive && (Auth::user()->id == $listing->user->id || Auth::user()->hasPower('manage_submissions')))
                {!! Form::open(['url' => url()->current(), 'id' => 'expireForm']) !!}
                <a href="#" id="expireButton" class="float-right btn btn-outline-danger btn-sm"> Mark Expired</a>
                {!! Form::close() !!}
                <a href="{{ url('trades/listings/' . $listing->id . '/edit') }}" class="float-right mr-2 btn btn-outline-info btn-sm">Edit</a>
            @endif
        </h3>
        <div>
            <div>
                <h5>Comments</h5>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    @if ($listing->comments)
                        {!! nl2br(htmlentities($listing->comments)) !!}
                    @else
                        No comment given.
                    @endif
                </div>
            </div>
            <div>
                <h5>Preferred Method(s) of Contact</h5>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    {!! nl2br(htmlentities($listing->contact)) !!}
                </div>
            </div>
        </div>
    </div>

    <h2>Seeking & Offering</h2>
    <div class="row">
        <div class="col-lg-6">
            @include('home.trades.listings._seeking', ['user' => $listing->user, 'data' => $seekingData, 'listing' => $listing, 'type' => 'seeking'])
        </div>

        <div class="col-lg-6">
            @include('home.trades.listings._offer', ['user' => $listing->user, 'data' => $offeringData, 'listing' => $listing, 'type' => 'offering'])
        </div>
    </div>

    @if (Auth::check() && Auth::user()->id != $listing->user_id)
        <div class="alert alert-dark text-center">
            <a href="{{ url('trades/proposal?recipient_id=' . $listing->user_id) }}" class="btn btn-outline-light">
                <i class="fas fa-handshake"></i>
                Propose Trade
            </a>
        </div>
    @endif

    <hr />
    <div class="container">
        @comments(['model' => $listing, 'perPage' => 5])
    </div>

    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">
                        Confirm Expiry
                    </span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will mark the trade listing as expired and remove it from active view.</p>
                    <a href="#" id="expireSubmit" class="float-right btn btn-danger">Mark Expired</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    @if ($listing->isActive)
        <script>
            $(document).ready(function() {
                var $confirmationModal = $('#confirmationModal');
                var $expireForm = $('#expireForm');

                var $expireButton = $('#expireButton');
                var $expireSubmit = $('#expireSubmit');

                $expireButton.on('click', function(e) {
                    e.preventDefault();
                    $confirmationModal.modal('show');
                });

                $expireSubmit.on('click', function(e) {
                    e.preventDefault();
                    $expireForm.attr('action', '{{ url()->current() }}/expire');
                    $expireForm.submit();
                });
            });
        </script>
    @endif
@endsection
