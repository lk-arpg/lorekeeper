<h4>Populate Default Criteria</h4>
<p>You can populate this {{ $type }} with the selected defaults.</p>
<p>By turning on a default, when you edit the {{ $type }} it will add that criterion group with the pre-determined values that you set. You can have as many default groups as you want, and they can even contain the same criteria as another
    group-- just with different preset values.</p>
<p><strong>Note:</strong> Toggling on a default will not remove any existing criteria, so if you toggle on a default that contains the same criterion as another default, it will simply add another instance of that criterion to the
    {{ $type }}.</p>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> This will only populate the criteria with the default values when you edit the {{ $type }}. The toggle <strong>does not</strong> remain active after you edit the {{ $type }}.
</div>
@php
    $defaults = \App\Models\Criteria\CriterionDefault::orderBy('name')->get();
@endphp
<div class="row">
    @if (count($defaults))
        @foreach ($defaults as $default)
            <div class="col-md-4 form-group">
                {!! Form::checkbox('default_criteria[' . $default->id . ']', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('default_criteria[' . $default->id . ']', $default->name, ['class' => 'form-check-label ml-3']) !!} {!! add_help('Toggle on to populate this criterion set.') !!}
            </div>
        @endforeach
    @else
        <div class="col-md">
            <div class="alert alert-info w-100">
                No default criteria have been created yet. You can create them under the "Criteria Rewards" section of the admin panel.
            </div>
        </div>
    @endif
</div>
