@php
    if (!isset($trade)) {
        $trade = null;
    }
@endphp

@include('widgets._inventory_select', [
    'user' => $user,
    'page' => $page,
    'selected' => $trade ? $trade->getInventory($user) : $selectedItems ?? [],
    'inventory' => $inventory,
    'categories' => $categories,
    'fieldPrefix' => isset($fieldPrefix) ? $fieldPrefix : null,
    'fieldName' => $trade ? ($user->id == $trade->recipient_id ? 'recipient_stack_id[]' : null) : (Auth::user()->id != $user->id ? 'recipient_stack_id[]' : null),
])

@include('widgets._user_character_select', [
    'readOnly' => true,
    'selected' => $trade ? $trade->getCharacters($user) : $selectedCharacters ?? [],
    'categories' => $characterCategories,
    'characters' => $characters,
    'fieldPrefix' => isset($fieldPrefix) ? $fieldPrefix : null,
    'fieldName' => $trade ? ($user->id == $trade->recipient_id ? 'stack_character_id[]' : null) : (Auth::user()->id != $user->id ? 'recipient_character_id[]' : null),
    'customSize' => 4,
])

@include('widgets._bank_select', [
    'owner' => $user,
    'selected' => $trade ? $trade->getCurrencies($user) : [],
    'isTransferrable' => true,
    'fieldPrefix' => isset($fieldPrefix) ? $fieldPrefix : null,
])

@include('widgets._bank_select_row', [
    'owners' => [$user],
    'isTransferrable' => true,
])

{{-- JS --}}

@include('widgets._inventory_select_js', [
    'readOnly' => true,
    'fieldPrefix' => isset($fieldPrefix) ? $fieldPrefix : null,
])

@include('widgets._user_character_select_js', [
    'readOnly' => true,
    'fieldPrefix' => isset($fieldPrefix) ? $fieldPrefix : null,
])
