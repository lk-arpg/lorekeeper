@if (!$alias)
    <p>Invalid alias selected.</p>
@elseif($alias->is_primary)
    <p>As this is your primary alias, you cannot hide it.</p>
@else
    <p>This will {{ !$alias->is_visible ? 'un' : '' }}hide the alias <strong>{!! $alias->displayAlias !!}</strong>. </p>
    @if ($alias->is_visible)
        <p>Logged-out users and logged-in users will not be able to see that this alias is associated with your account. Note that staff may be able to view your aliases regardless.</p>
    @else
        <p>Logged-out users and logged-in users will be able to view a list of your aliases from your profile page.</p>
    @endif
    {!! Form::open(['url' => 'account/hide-alias/' . $alias->id, 'class' => 'text-end']) !!}
    {!! Form::submit((!$alias->is_visible ? 'Unhide' : 'Hide') . ' Alias', ['class' => 'btn btn-secondary']) !!}
    {!! Form::close() !!}
@endif
