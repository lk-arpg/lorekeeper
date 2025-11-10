@if (!$stock)
    <div class="text-center">Invalid item selected.</div>
@else
    <div class="text-center mb-3">
        <div class="mb-1"><a href="{{ $stock->item->idUrl }}"><img class="img-fluid" src="{{ $stock->item->imageUrl }}" alt="{{ $stock->item->name }}" /></a></div>
        <div><a href="{{ $stock->item->idUrl }}"><strong>{{ $stock->item->name }}</strong></a></div>
        <div><strong>Cost: </strong> {!! $stock->displayCosts() ?? 'Free' !!}</div>
        @if ($stock->is_limited_stock)
            <div>Stock: {{ $stock->quantity }}</div>
        @endif
        @if ($stock->purchase_limit)
            <div class="text-danger">
                Max {{ $stock->purchase_limit }}
                @if ($stock->purchase_limit_timeframe !== 'lifetime')
                    {{ $stock->purchase_limit_timeframe }}
                @endif
                per user
            </div>
        @endif
        @if ($stock->disallow_transfer)
            <div class="text-danger">Cannot be transferred after purchase</div>
        @endif
    </div>

    @if ($stock->item->parsed_description)
        <div class="mb-2">
            <a data-toggle="collapse" href="#itemDescription" class="h5">Description <i class="fas fa-caret-down"></i></a>
            <div class="card collapse show mt-1" id="itemDescription">
                <div class="card-body">
                    {!! $stock->item->parsed_description !!}
                </div>
            </div>
        </div>
    @endif

    @if ($stock->is_timed_stock)
        <div class="alert alert-info text-center">
            This item is available for a limited time!
            <br />
            <i class="fas fa-clock"></i> <small>{!! $stock->displayTime() !!}</small>
        </div>
    @endif

    @if ($stock->shop->use_coupons)
        <div class="alert alert-success">You can use coupons in this store!</div>
        @if ($shop->allowed_coupons && count($shop->allowed_coupons))
            <div class="alert alert-info">You can use the following coupons: @foreach ($shop->allAllowedCoupons as $coupon)
                    {!! $coupon->displayName !!}{{ $loop->last ? '' : ',' }}
                @endforeach
            </div>
        @endif
    @endif

    @if (Auth::check())
        @if (($stock->is_fto && Auth::user()->settings->is_fto) || !$stock->is_fto)
            <h5>
                Purchase
                <span class="float-right">
                    In Inventory: {{ $userOwned }}
                </span>
            </h5>
            @if ($stock->is_limited_stock && $stock->quantity == 0)
                <div class="alert alert-warning mb-0">This item is out of stock.</div>
            @elseif ($purchaseLimitReached)
                <div class="alert alert-warning mb-0">
                    You have already purchased the limit of {{ $stock->purchase_limit }} of this item
                    @if ($stock->purchase_limit_timeframe !== 'lifetime')
                        within the {{ $stock->purchase_limit_timeframe }} reset
                    @endif.
                </div>
            @else
                @if ($stock->purchase_limit)
                    <div class="alert alert-warning mb-3">
                        You have purchased this item {{ $userPurchaseCount }} times
                        @if ($stock->purchase_limit_timeframe !== 'lifetime')
                            within the {{ $stock->purchase_limit_timeframe }} reset
                        @endif
                    </div>
                @endif
                {!! Form::open(['url' => 'shops/buy']) !!}
                {!! Form::hidden('shop_id', $shop->id) !!}
                {!! Form::hidden('stock_id', $stock->id) !!}

                @if ($stock->costs()->get()->pluck('group')->unique()->count() > 1)
                    <div class="form-group">
                        {!! Form::label('cost_group', 'Choose Purchase Method:') !!}
                        {!! Form::select('cost_group', $stock->costForm(), null, ['class' => 'form-control mb-3']) !!}
                    </div>
                @endif

                {!! Form::label('quantity', 'Quantity') !!}
                {!! Form::selectRange('quantity', 1, $quantityLimit, 1, ['class' => 'form-control mb-3']) !!}
                @if ($stock->use_user_bank && $stock->use_character_bank)
                    <p>
                        This item can be paid for with either your user account bank, or a character's bank. Please choose which you would like to use.
                        <br />
                        <strong>
                            Note: Only currencies are taken from character banks. Items and other costs are taken from user banks.
                        </strong>
                    </p>
                    <div class="form-group">
                        <div>
                            <label class="h5">{{ Form::radio('bank', 'user', true, ['class' => 'bank-select mr-1']) }} User Bank</label>
                        </div>
                        <div>
                            <label class="h5">{{ Form::radio('bank', 'character', false, ['class' => 'bank-select mr-1']) }} Character Bank</label>
                            <div class="card use-character-bank hide">
                                <div class="card-body">
                                    <p>Enter the code of the character you would like to use to purchase the item.</p>
                                    <div class="form-group">
                                        {!! Form::label('slug', 'Character Code') !!}
                                        {!! Form::select('slug', Auth::user()->characters()->get()->pluck('fullName', 'id'), ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($stock->use_user_bank)
                    <p>This item will be paid for using your user account bank.</p>
                    {!! Form::hidden('bank', 'user') !!}
                @elseif ($stock->use_character_bank)
                    <p>
                        This item must be paid for using a character's bank. Enter the code of the character whose bank you would like to use to purchase the item.
                        <br />
                        <strong>
                            Note: Only currencies are taken from character banks. Items and other costs are taken from user banks.
                        </strong>
                    </p>
                    {!! Form::hidden('bank', 'character') !!}
                    <div class="form-group">
                        {!! Form::label('slug', 'Character Code') !!}
                        {!! Form::text('slug', null, ['class' => 'form-control']) !!}
                    </div>
                @endif
                @if ($stock->costs()->where('cost_type', '!=', 'Currency')->count())
                    <div class="alert alert-warning">
                        This item has non-currency costs! These costs will be taken from your inventory automatically if not chosen from the list below.
                    </div>
                    @include('widgets._inventory_select', [
                        'user' => Auth::user(),
                        'inventory' => $inventory,
                        'selected' => [],
                        'page' => false,
                        'customHeaderClass' => 'h5',
                    ])
                @endif
                @if ($stock->shop->use_coupons && $userCoupons !== null)
                    @if (Settings::get('limited_stock_coupon_settings') == 0)
                        <p class="text-danger">Note that coupons cannot be used on limited stock items.</p>
                    @endif
                    <div class="form-group">
                        {!! Form::checkbox('use_coupon', 1, 0, ['class' => 'is-coupon-class form-control', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('use_coupon', 'Do you want to use a coupon?', ['class' => 'form-check-label  ml-3 mb-2']) !!}
                    </div>
                    <div class="br-form-group" style="display: none">
                        {!! Form::select('coupon', $userCoupons, null, ['class' => 'form-control mb-2', 'placeholder' => 'Select a Coupon to Use']) !!}
                    </div>
                @elseif($stock->shop->use_coupons && $userCoupons == null)
                    <div class="alert alert-danger">You do not own any coupons.</div>
                @endif
                <div class="text-right">
                    {!! Form::submit('Purchase', ['class' => 'btn btn-primary']) !!}
                </div>
                {!! Form::close() !!}
            @endif
        @else
            <div class="alert alert-danger">You must be a FTO to purchase this item.</div>
        @endif
    @else
        <div class="alert alert-danger">You must be logged in to purchase this item.</div>
    @endif
@endif

@if (Auth::check())
    @include('widgets._inventory_select_js')
    <script>
        var $useCharacterBank = $('.use-character-bank');
        $('.bank-select').on('click', function(e) {
            if ($('input[name=bank]:checked').val() == 'character')
                $useCharacterBank.removeClass('hide');
            else
                $useCharacterBank.addClass('hide');
        });

        $(document).ready(function() {
            $('.is-coupon-class').change(function(e) {
                console.log(this.checked)
                $('.br-form-group').css('display', this.checked ? 'block' : 'none')
            })
            $('.br-form-group').css('display', $('.is-restricted-class').prop('checked') ? 'block' : 'none')
        });
    </script>
@endif
