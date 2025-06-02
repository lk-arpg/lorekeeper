<p>Do you find yourself with too many identical variations of the same item? This tool will consolidate all of the same variant of item into one.</p>
<p>Please note the following:</p>
<ul>
    <li>This tool will go over all item stacks (variations) in your inventory. It does not include character inventories.</li>
    <li>Variations are considered identical if they have the same source and notes fields. These must be <i>exactly the same</i>.</li>
    <li>It cannot consolidate stacks that are partially held in trades and submissions.</li>
    <li>This operation is not reversible. You can, however, run it multiple times if necessary.</li>
</ul>
{!! Form::open(['url' => 'inventory/consolidate', 'class' => 'text-end']) !!}
{!! Form::submit('Consolidate', ['class' => 'btn btn-primary', 'name' => 'action']) !!}
{!! Form::close() !!}
