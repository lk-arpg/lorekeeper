<p>This will reject the design approval request, which closes the request and disallows the user from editing it further. Any attached items and/or currency will be returned.</p>
{!! Form::open(['url' => 'admin/designs/edit/' . $request->id . '/reject']) !!}
<div class="mb-3">
    {!! Form::label('staff_comments', 'Comment') !!} {!! add_help('Enter a comment for the user. They will see this on their request page.') !!}
    {!! Form::textarea('staff_comments', $request->staff_comments, ['class' => 'form-control']) !!}
</div>
<div class="text-end">
    {!! Form::submit('Reject Request', ['class' => 'btn btn-danger']) !!}
</div>
{!! Form::close() !!}
