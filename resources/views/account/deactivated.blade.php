@extends('layouts.app')

@section('title')
    Deactivated
@endsection

@section('content')
    {!! breadcrumbs(['Deactivated' => 'banned']) !!}

    <h1>Deactivated</h1>

    <p>Your account has been deactivated as of {!! format_date(Auth::user()->settings->deactivated_at) !!}. {{ Auth::user()->settings->deactivate_reason ? 'The following reason was given:' : '' }}</p>

    @if (Auth::user()->settings->deactivate_reason)
        <div class="alert alert-danger">
            <div class="alert-header">{!! Auth::user()->deactivater->displayName !!}:</div>
            {!! nl2br(htmlentities(Auth::user()->settings->deactivate_reason)) !!}
        </div>
    @endif

    <p>As such, you may not continue to to use site features. Items, currencies, characters and any other assets attached to your account cannot be transferred to another user, nor can another user transfer any assets to your account. Any pending
        submissions have also been removed from the submission queue. </p>

    @if (Auth::user()->is_deactivated)
        <div class="text-end">
            <a href="#" class="btn btn-outline-danger reactivate-button">Reactivate Account</a>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.reactivate-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('reactivate') }}", 'Reactivate Account');
            });
        });
    </script>
@endsection
