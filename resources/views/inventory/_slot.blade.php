@if ($tag->is_active)
    <li class="list-group-item">
        <a class="card-title h5 collapse-title" data-toggle="collapse" href="#openSlotForm"> Use Slot</a>
        <div id="openSlotForm" class="collapse">
            {!! Form::hidden('tag', $tag->tag) !!}
            <p>This action is not reversible. Are you sure you want to use this item?</p>
            <div class="text-right">
                {!! Form::button('Open', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'act', 'type' => 'submit']) !!}
            </div>
        </div>
    </li>
@endif
