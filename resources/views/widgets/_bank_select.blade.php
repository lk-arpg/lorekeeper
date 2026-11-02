<h3>
    {{ $owner->logType == 'User' && $owner->id == Auth::user()->id ? 'Your' : ($owner->logType == 'Character' ? $owner->fullName : $owner->name) . '\'s' }} Bank
    <a class="small inventory-collapse-toggle collapse-toggle collapsed" href="#{{ strtolower($owner->logType) }}Bank-{{ $owner->id }}" data-toggle="collapse">Show</a>
</h3>
<div class="{{ isset($selected) && count($selected) ? '' : 'collapse' }}" id="{{ strtolower($owner->logType) }}Bank-{{ $owner->id }}">
    <div class="card mb-3">
        <div class="card-body">
            <div class="text-right mb-3">
                <a href="#" class="btn btn-outline-info add-currency-button" data-type="{{ strtolower($owner->logType) }}" data-id="{{ $owner->id }}">Add Currency</a>
            </div>
            <table class="table table-sm show currency-table">
                <thead>
                    <tr>
                        <th width="70%">Currency</th>
                        <th width="20%">Quantity</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody id="{{ strtolower($owner->logType) }}Body-{{ $owner->id }}">
                    @if ($selected)
                        <?php $currencySelect = $owner->getCurrencySelect(isset($isTransferrable) ? $isTransferrable : false); ?>
                        @foreach ($selected as $currencyId => $quantity)
                            <tr class="bank-row">
                                <td>{!! Form::select('currency_id[' . strtolower($owner->logType) . '-' . $owner->id . '][]', $currencySelect, $currencyId, ['class' => 'form-control selectize', 'placeholder' => 'Select Currency    ']) !!}</td>
                                <td>{!! Form::text('currency_quantity[' . strtolower($owner->logType) . '-' . $owner->id . '][]', $quantity, ['class' => 'form-control']) !!}</td>
                                <td class="text-right"><a href="#" class="btn btn-danger remove-currency-button">Remove</a></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
