@php
    if (!isset($stock)) {
        $stock = null;
    }
    $item_id = $stock ? ($stock->isCategory ? $stock->item_id . '-category' : ($stock->isRandom ? 'random' : $stock->item_id ?? null)) : null;
@endphp
{!! Form::label('Select Stock:') !!}
@if ($stock && $stock->isRandom)
    <span class="alert alert-info">
        This Stock Is Currently Random.
    </span>
@endif
{!! Form::select('item_id', $items, $item_id, ['class' => 'form-control item-selectize']) !!}

<script>
    $(document).ready(function() {
        $('.item-selectize').selectize();
    });
</script>
