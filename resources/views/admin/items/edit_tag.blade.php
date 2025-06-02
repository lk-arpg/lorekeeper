@extends('admin.layout')

@section('admin-title')
    Edit Item Tag
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Items' => 'admin/data/items', 'Edit Item' => 'admin/data/items/edit/' . $item->id, 'Edit Tag Settings - ' . $tag->tag => 'admin/data/items/tag/' . $item->id . '/' . $tag->tag]) !!}

    <h1>
        Edit Tag Settings - {!! $tag->displayTag !!}
        <a href="#" class="btn btn-outline-danger float-end delete-tag-button">Delete Tag</a>
    </h1>

    <p>Edit the parameters for this item tag on this item. Note that for the item tag to take effect (e.g. become a usable item), you will need to turn on the Active toggle. (Conversely, you can turn it off to prevent users from using it while preserving
        the old settings for future use.)</p>

    @if (View::exists('admin.items.tags.' . $tag->tag . '_pre'))
        @include('admin.items.tags.' . $tag->tag . '_pre', ['item' => $item, 'tag' => $tag])
    @endif
    {!! Form::open(['url' => 'admin/data/items/tag/' . $item->id . '/' . $tag->tag]) !!}

    @if (View::exists('admin.items.tags.' . $tag->tag))
        @include('admin.items.tags.' . $tag->tag, ['item' => $item, 'tag' => $tag])
    @endif

    {!! Form::checkbox('is_active', 1, $tag->is_active, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Active', ['class' => 'form-check-label ms-3']) !!}

    <div class="text-end">
        {!! Form::submit('Edit Tag Settings', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
    @if (View::exists('admin.items.tags.' . $tag->tag . '_post'))
        @include('admin.items.tags.' . $tag->tag . '_post', ['item' => $item, 'tag' => $tag])
    @endif
@endsection

@section('scripts')
    @parent
    @if (View::exists('js.admin_items.' . $tag->tag))
        @include('js.admin_items.' . $tag->tag, ['item' => $item, 'tag' => $tag])
    @endif
    <script>
        $(document).ready(function() {
            $('.delete-tag-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/items/delete-tag') }}/{{ $item->id }}/{{ $tag->tag }}", 'Delete Tag');
            });
        });
    </script>
@endsection
