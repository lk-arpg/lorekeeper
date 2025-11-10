@extends('admin.layout')

@section('admin-title')
    {{ $item->id ? 'Edit' : 'Create' }} Item
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Items' => 'admin/data/items', ($item->id ? 'Edit' : 'Create') . ' Item' => $item->id ? 'admin/data/items/edit/' . $item->id : 'admin/data/items/create']) !!}

    <h1>{{ $item->id ? 'Edit' : 'Create' }} Item
        @if ($item->id)
            <a href="#" class="btn btn-outline-danger float-right delete-item-button">Delete Item</a>
        @endif
    </h1>

    {!! Form::open(['url' => $item->id ? 'admin/data/items/edit/' . $item->id : 'admin/data/items/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $item->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($item->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md form-group">
            {!! Form::label('Item Category (Optional)') !!}
            {!! Form::select('item_category_id', $categories, $item->item_category_id, ['class' => 'form-control']) !!}
        </div>
        @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
            <div class="col-md form-group">
                {!! Form::label('Item Rarity (Optional)') !!}
                {!! Form::select('rarity_id', $rarities, $item && $item->rarityId ? $item->rarityId : '', ['class' => 'form-control', 'placeholder' => 'Select a Rarity']) !!}
            </div>
        @endif
    </div>

    @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('Reference Link (Optional)') !!} {!! add_help('An optional link to an additional reference') !!}
                {!! Form::text('reference_url', $item->reference_url, ['class' => 'form-control']) !!}
            </div>
            <div class="col-md">
                {!! Form::label('Item Artist (Optional)') !!} {!! add_help('Provide the artist\'s username if they are on site or, failing that, a link.') !!}
                <div class="row">
                    <div class="col-md form-group">
                        {!! Form::select('artist_id', $userOptions, $item && $item->artist_id ? $item->artist_id : null, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select a User']) !!}
                    </div>
                    <div class="col-md form-group">
                        {!! Form::text('artist_url', $item && $item->artist_url ? $item->artist_url : '', ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $item->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
        <div class="form-group">
            {!! Form::label('Uses (Optional)') !!} {!! add_help('A short description of the item\'s use(s). Supports raw HTML if need be, but keep it brief.') !!}
            {!! Form::text('uses', $item && $item->uses ? $item->uses : '', ['class' => 'form-control']) !!}
        </div>
    @endif

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('allow_transfer', 1, $item->id ? $item->allow_transfer : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('allow_transfer', 'Allow User → User Transfer', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to transfer this item to other users. Non-account-bound items can be account-bound when granted to users directly.') !!}
        </div>
        @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
            <div class="col-md form-group">
                {!! Form::checkbox('is_released', 1, $item->id ? $item->is_released : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_released', 'Is Released', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to view information for the item/it will be hidden from view. This is overridden by the item being owned at any point by anyone on the site.') !!}
            </div>
        @endif
        <div class="col-md form-group">
            {!! Form::checkbox('is_deletable', 1, $item->id ? $item->is_deletable : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_deletable', 'Can Be Deleted', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to delete this item from user or character inventories.') !!}
        </div>
    </div>

    @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
        <h3>Availability Information</h3>
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('release', 'Source (Optional)') !!} {!! add_help('The original and/or general source of the item. Should be brief.') !!}
                {!! Form::text('release', $item && $item->source ? $item->source : '', ['class' => 'form-control']) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('prompts[]', 'Drop Location(s) (Optional)') !!} {!! add_help('You can select up to 10 prompts at once.') !!}
                {!! Form::select('prompts[]', $prompts, $item && isset($item->data['prompts']) ? $item->data['prompts'] : '', ['id' => 'promptsList', 'class' => 'form-control', 'multiple']) !!}
            </div>
        </div>
    @endif

    @if (config('lorekeeper.extensions.item_entry_expansion.resale_function'))
        <h3>Resale Information</h3>
        <p>The currency and amount users will be able to sell this item from their inventory for. If quantity is not set, the item will be unable to be sold.</p>
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('currency_id', 'Currency') !!}
                {!! Form::select('currency_id', $userCurrencies, isset($item->data['resell']) && App\Models\Currency\Currency::where('id', $item->resell->flip()->pop())->first() ? $item->resell->flip()->pop() : null, ['class' => 'form-control']) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('currency_quantity', 'Quantity') !!}
                {!! Form::text('currency_quantity', isset($item->data['resell']) ? $item->resell->pop() : null, ['class' => 'form-control']) !!}
            </div>
        </div>
    @endif

    <div class="text-right">
        {!! Form::submit($item->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($item->id)
        <h3>Item Tags</h3>
        <p>Item tags indicate extra functionality for the item. Click on the edit button to edit the specific item tag's data.</p>
        @if (count($item->tags))
            <table class="table">
                <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Active?</th>
                        <th></th>
                    </tr>
                </thead>
                @foreach ($item->tags as $tag)
                    <tr>
                        <td>{!! $tag->displayTag !!}</td>
                        <td class="{{ $tag->is_active ? 'text-success' : 'text-danger' }}">{{ $tag->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-right"><a href="{{ url('admin/data/items/tag/' . $item->id . '/' . $tag->tag) }}" class="btn btn-outline-primary">Edit</a></td>
                    </tr>
                @endforeach
            </table>
        @else
            <p>No item tags attached to this item.</p>
        @endif
        <div class="text-right">
            <a href="{{ url('admin/data/items/tag/' . $item->id) }}" class="btn btn-outline-primary">Add a Tag</a>
        </div>

        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._item_entry', ['imageUrl' => $item->imageUrl, 'name' => $item->displayName, 'description' => $item->parsed_description, 'searchUrl' => $item->searchUrl])
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('#promptsList').selectize({
                maxItems: 10
            });

            $('.delete-item-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/items/delete') }}/{{ $item->id }}", 'Delete Item');
            });
        });
    </script>
@endsection
