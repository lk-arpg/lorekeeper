{!! Form::label('Subtypes (Optional)') !!} @if ($isMyo)
    {!! add_help(
        'This will lock the slot into a particular subtype. Leave it blank if you would like to give the user a choice, or not select a subtype. The subtype must match the species selected above, and if no species is specified, the subtype will not be applied.',
    ) !!}
@endif
{!! Form::select('subtype_ids[]', $subtypes, null, ['class' => 'form-control', 'id' => 'subtype', 'placeholder' => 'Select Subtypes', 'multiple']) !!}
