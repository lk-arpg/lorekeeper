<div id="bankRowData" class="hide">
    <table class="table table-sm">
        @foreach ($owners as $owner)
            @if ($owner)
                <tbody id="{{ strtolower($owner->logType) }}Row-{{ $owner->id }}">
                    <tr class="bank-row">
                        <td>{!! Form::select('currency_id[' . strtolower($owner->logType) . '-' . $owner->id . '][]', $owner->getCurrencySelect(isset($isTransferrable) ? $isTransferrable : false), null, [
                            'class' => 'form-control selectize',
                            'placeholder' => 'Select Currency    ',
                        ]) !!}</td>
                        <td>{!! Form::text('currency_quantity[' . strtolower($owner->logType) . '-' . $owner->id . '][]', 0, ['class' => 'form-control']) !!}</td>
                        <td class="text-end"><a href="#" class="btn btn-danger remove-currency-button">Remove</a></td>
                    </tr>
                </tbody>
            @endif
        @endforeach
    </table>
</div>
