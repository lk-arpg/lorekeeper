@if (!$alias)
    <p>Invalid alias selected.</p>
@elseif($alias->is_primary)
    <p>This is already your primary alias!</p>
@elseif(!$alias->canMakePrimary)
    <p>This alias cannot be made your primary alias.</p>
@else
    <p>This will make <strong>{!! $alias->displayAlias !!}</strong> your primary alias. Are you sure?</p>
    @if (!$alias->is_visible)
        <p class="text-danger">This alias is currently hidden from the public. Setting it as your primary alias will make it visible to everyone!</p>
    @endif
    {!! Form::open(['url' => 'account/make-primary/' . $alias->id, 'class' => 'text-end']) !!}
    {!! Form::submit('Make Primary Alias', ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}
@endif
