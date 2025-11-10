{!! Form::label('Subtypes (Optional)') !!}
{!! Form::select('subtype_ids', $subtypes, old('subtype_ids') ?: $image->subtypes()?->pluck('subtype_id')->toArray() ?? [], ['class' => 'form-control', 'id' => 'subtype', 'multiple']) !!}

<script>
    $('#subtype').selectize({
        maxItems: {{ config('lorekeeper.extensions.multiple_subtype_limit') }},
    });
</script>
