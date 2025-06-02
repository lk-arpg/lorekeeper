@extends('character.design.layout')

@section('design-title')
    Request (#{{ $request->id }}) :: Comments
@endsection

@section('design-content')
    {!! breadcrumbs(['Design Approvals' => 'designs', 'Request (#' . $request->id . ')' => 'designs/' . $request->id, 'Comments' => 'designs/' . $request->id . '/comments']) !!}

    @include('character.design._header', ['request' => $request])

    <h2>Comments</h2>

    @if ($request->status == 'Draft' && $request->user_id == Auth::user()->id)
        <p>Enter an optional comment about your submission (e.g. calculations) that staff will consider when reviewing your request. If you don't have a comment, click the Save button once to mark this section complete regardless.</p>
        {!! Form::open(['url' => 'designs/' . $request->id . '/comments']) !!}
        <div class="mb-3">
            {!! Form::label('Comments (Optional)') !!}
            {!! Form::textarea('comments', $request->comments, ['class' => 'form-control']) !!}
        </div>
        <div class="text-end">
            {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @else
        <div class="card">
            <div class="card-body">
                {!! nl2br(htmlentities($request->comments)) !!}
            </div>
        </div>
    @endif
@endsection
