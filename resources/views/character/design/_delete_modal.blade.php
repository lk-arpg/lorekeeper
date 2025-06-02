@if ($request->user_id == Auth::user()->id)
    @if ($request->status == 'Draft')
        <p>This will delete the request and return all attached items/currency to you. </p>
        <p>Are you sure you want to delete this request?</p>
        {!! Form::open(['url' => 'designs/' . $request->id . '/delete', 'class' => 'text-end']) !!}
        {!! Form::submit('Delete Request', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    @else
        <p class="text-danger">This request has already been submitted to the queue and cannot be deleted.</p>
    @endif
@else
    <div>You cannot delete this request.</div>
@endif
