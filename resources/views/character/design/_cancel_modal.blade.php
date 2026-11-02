@if ($request->user_id == Auth::user()->id)
    <p>This will cancel the pending design approval request and will send it back to your drafts. <br>
        You will need to resubmit in order for it to be in the queue again.</p>
    <p>
        Note that you will lose your place in the queue if you do this.
    </p>
    <p>Are you sure you want to cancel this request?</p>
    {!! Form::open(['url' => 'designs/' . $request->id . '/cancel', 'class' => 'text-right']) !!}
    {!! Form::submit('Cancel Request', ['class' => 'btn btn-primary']) !!}
@else
    <div>You cannot cancel this request.</div>
@endif
