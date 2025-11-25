@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Inventory
@endsection

@section('profile-content')
    @if ($character->is_myo_slot)
        {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url, 'Inventory' => $character->url . '/inventory']) !!}
    @else
        {!! breadcrumbs([
            $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
            $character->fullName => $character->url,
            'Inventory' => $character->url . '/inventory',
        ]) !!}
    @endif

    @include('character._header', ['character' => $character])

    <h3>
        @if (Auth::check() && Auth::user()->hasPower('edit_inventories'))
            <a href="#" class="float-right btn btn-outline-info btn-sm" id="grantButton" data-toggle="modal" data-target="#grantModal"><i class="fas fa-cog"></i> Admin</a>
        @endif
        Items
    </h3>

    <div class="text-right mb-3">
        <div class="btn-group">
            <button type="button" class="btn btn-secondary active def-view-button" data-toggle="tooltip" title="Default View" alt="Default View"><i class="fas fa-th"></i></button>
            <button type="button" class="btn btn-secondary sum-view-button" data-toggle="tooltip" title="Summarized View" alt="Summarized View"><i class="fas fa-bars"></i></button>
        </div>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('item_category_id', $categories->pluck('name', 'id'), Request::get('item_category_id'), ['class' => 'form-control', 'placeholder' => 'Any Category']) !!}
            </div>
            @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
                <div class="form-group ml-3 mb-3">
                    {!! Form::select('rarity_id', $rarities, Request::get('rarity_id'), ['class' => 'form-control', 'placeholder' => 'Any Rarity']) !!}
                </div>
                <div class="form-group ml-3 mb-3">
                    {!! Form::select('artist', $artists, Request::get('artist'), ['class' => 'form-control', 'placeholder' => 'Any Artist']) !!}
                </div>
            @endif
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    <div id="defView" class="hide">
        @foreach ($items as $categoryId => $categoryItems)
            <div class="card mb-3 inventory-category">
                <h5 class="card-header inventory-header">
                    {!! isset($categories[$categoryId]) ? '<a href="' . $categories[$categoryId]->searchUrl . '">' . $categories[$categoryId]->name . '</a>' : 'Miscellaneous' !!}
                    <a class="small inventory-collapse-toggle collapse-toggle" href="#categoryId_{!! isset($categories[$categoryId]) ? $categories[$categoryId]->id : 'miscellaneous' !!}" data-toggle="collapse">
                        Show
                    </a>
                </h5>
                <div class="card-body inventory-body collapse show" id="categoryId_{!! isset($categories[$categoryId]) ? $categories[$categoryId]->id : 'miscellaneous' !!}">
                    @foreach ($categoryItems->chunk(4) as $chunk)
                        <div class="row mb-3">
                            @foreach ($chunk as $itemId => $stack)
                                <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $stack->first()->pivot->id }}"
                                    data-name="{!! $stack->first()->category->can_name && $stack->first()->pivot->stack_name ? htmlentities($stack->first()->pivot->stack_name) . ' [' : null !!}{{ $character->name ? $character->name : $character->slug }}'s {{ $stack->first()->name }}{!! $stack->first()->category->can_name && $stack->first()->pivot->stack_name ? ']' : null !!}">
                                    <div class="mb-1">
                                        <a href="#" class="inventory-stack">
                                            <img src="{{ $stack->first()->imageUrl }}" alt="{{ $stack->first()->name }}" />
                                        </a>
                                    </div>
                                    <div class="{{ $stack->first()->category->can_name ? 'text-muted' : '' }}">
                                        <a href="#" class="inventory-stack inventory-stack-name">
                                            {{ $stack->first()->name }} x{{ $stack->sum('pivot.count') }}
                                        </a>
                                    </div>
                                    @if ($stack->first()->category->can_name && $stack->first()->pivot->stack_name)
                                        <div>
                                            <span class="inventory-stack inventory-stack-name badge badge-info" style="font-size:95%; margin:5px;">"{{ $stack->first()->pivot->stack_name }}"</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div id="sumView" class="hide">
        @foreach ($items as $categoryId => $categoryItems)
            <div class="card mb-2">
                <h5 class="card-header">
                    {!! isset($categories[$categoryId]) ? '<a href="' . $categories[$categoryId]->searchUrl . '">' . $categories[$categoryId]->name . '</a>' : 'Miscellaneous' !!}
                    <a class="small inventory-collapse-toggle collapse-toggle" href="#categoryId_{!! isset($categories[$categoryId]) ? $categories[$categoryId]->id : 'miscellaneous' !!}" data-toggle="collapse">
                        Show
                    </a>
                </h5>
                <div class="card-body p-2 collapse show row" id="categoryId_{!! isset($categories[$categoryId]) ? $categories[$categoryId]->id : 'miscellaneous' !!}">
                    @foreach ($categoryItems as $itemtype)
                        <div class="col-lg-3 col-sm-4 col-12">
                            @if ($itemtype->first()->has_image)
                                <img src="{{ $itemtype->first()->imageUrl }}" style="height: 25px;" alt="{{ $itemtype->first()->name }}" />
                            @endif
                            <a href="{{ $itemtype->first()->idUrl }}">{{ $itemtype->first()->name }}</a>
                            <ul class="mb-0">
                                @foreach ($itemtype as $item)
                                    <div data-id="{{ $item->pivot->id }}" data-name="{!! $item->category->can_name && $item->pivot->stack_name ? htmlentities($item->pivot->stack_name) . ' [' : null !!}{{ $character->name ? $character->name : $character->slug }}'s {{ $item->name }}{!! $item->category->can_name && $item->pivot->stack_name ? ']' : null !!}">
                                        <li>
                                            <a class="inventory-stack" href="#">
                                                Stack of x{{ $item->pivot->count }}.
                                                @if ($item->category->can_name && $item->pivot->stack_name)
                                                    <span class="text-info m-0" style="font-size:95%; margin:5px;" data-toggle="tooltip" data-placement="top" title='Named stack:<br />"{{ $item->pivot->stack_name }}"'>
                                                        &nbsp;<i class="fas fa-tag"></i>
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    </div>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <h3>Latest Activity</h3>
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Sender</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Recipient</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Character</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="logs-table-cell">Log</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Date</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($logs as $log)
                <div class="logs-table-row">
                    @include('user._item_log_row', ['log' => $log, 'owner' => $character])
                </div>
            @endforeach
        </div>
    </div>
    <div class="text-right">
        <a href="{{ url($character->url . '/item-logs') }}">View all...</a>
    </div>

    @if (Auth::check() && Auth::user()->hasPower('edit_inventories'))
        <div class="modal fade" id="grantModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">[ADMIN] Grant Items</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Note that granting items does not check against any category hold limits for characters.</p>
                        <div class="form-group">
                            {!! Form::open(['url' => 'admin/character/' . $character->slug . '/grant-items']) !!}

                            {!! Form::label('Item(s)') !!} {!! add_help('Must have at least 1 item and Quantity must be at least 1.') !!}
                            <div id="itemList">
                                <div class="d-flex mb-2">
                                    {!! Form::select('item_ids[]', $itemOptions, null, ['class' => 'form-control mr-2 default item-select', 'placeholder' => 'Select Item']) !!}
                                    {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
                                    <a href="#" class="remove-item btn btn-danger mb-2 disabled">×</a>
                                </div>
                            </div>
                            <div class="mb-2"><a href="#" class="btn btn-primary" id="add-item">Add Item</a></div>
                            <div class="item-row hide mb-2">
                                {!! Form::select('item_ids[]', $itemOptions, null, ['class' => 'form-control mr-2 item-select', 'placeholder' => 'Select Item']) !!}
                                {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
                                <a href="#" class="remove-item btn btn-danger mb-2">×</a>
                            </div>

                            <h5>Additional Data</h5>

                            <div class="form-group">
                                {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs and in the inventory description.') !!}
                                {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('notes', 'Notes (Optional)') !!} {!! add_help('Additional notes for the item. This will appear in the item\'s description, but not in the logs.') !!}
                                {!! Form::text('notes', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::checkbox('disallow_transfer', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                                {!! Form::label('disallow_transfer', 'Character-bound', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is on, the character\'s owner will not be able to transfer this item to their inventory. Items that disallow transfers by default will still not be transferrable.') !!}
                            </div>

                            <div class="text-right">
                                {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                            </div>

                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @include('widgets._inventory_select_js')
    @include('widgets._inventory_view_js')
    <script>
        $(document).ready(function() {
            $('.inventory-stack').on('click', function(e) {
                e.preventDefault();
                var $parent = $(this).parent().parent();
                loadModal("{{ url('items') }}/character/" + $parent.data('id'), $parent.data('name'));
            });

            $('.default.item-select').selectize();
            $('#add-item').on('click', function(e) {
                e.preventDefault();
                addItemRow();
            });
            $('.remove-item').on('click', function(e) {
                e.preventDefault();
                removeItemRow($(this));
            })

            function addItemRow() {
                var $rows = $("#itemList > div")
                if ($rows.length === 1) {
                    $rows.find('.remove-item').removeClass('disabled')
                }
                var $clone = $('.item-row').clone();
                $('#itemList').append($clone);
                $clone.removeClass('hide item-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-item').on('click', function(e) {
                    e.preventDefault();
                    removeItemRow($(this));
                })
                $clone.find('.item-select').selectize();
            }

            function removeItemRow($trigger) {
                $trigger.parent().remove();
                var $rows = $("#itemList > div")
                if ($rows.length === 1) {
                    $rows.find('.remove-item').addClass('disabled')
                }
            }
        });
    </script>
@endsection
