@extends('admin.layout')

@section('admin-title')
    Sub Masterlists
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Sub Masterlists' => 'admin/data/sublists', ($sublist->id ? 'Edit' : 'Create') . ' Sub Masterlist' => $sublist->id ? 'admin/data/sublists/edit/' . $sublist->id : 'admin/data/sublists/create']) !!}

    <h1>{{ $sublist->id ? 'Edit' : 'Create' }} Sub Masterlist
        @if ($sublist->id)
            <a href="#" class="btn btn-danger float-right delete-sublist-button">Delete Sub Masterlist</a>
        @endif
    </h1>

    {!! Form::open(['url' => $sublist->id ? 'admin/data/sublists/edit/' . $sublist->id : 'admin/data/sublists/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $sublist->name, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::label('Key') !!}
            {!! Form::text('key', $sublist->key, ['class' => 'form-control']) !!}
        </div>
    </div>

    <h3>Contents</h3>
    <p>Each category and species can only have ONE sublist. If you assign a sublist here, it will be removed from any other sublists. If you want a species shared across multiple lists, it is suggested you only use character categories. Likewise, if you
        want a category shared across multiple lists, it is suggested you only use species.</p>

    <div class="form-group">
        {!! Form::label('categories[]', 'Categories') !!}
        {!! Form::select('categories[]', $categories, $subCategories, ['id' => 'categoryList', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('species[]', 'Species') !!}
        {!! Form::select('species[]', $species, $subSpecies, ['id' => 'speciesList', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('show_main', 1, $sublist->id ? $sublist->show_main : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('show_main', 'Show on Main', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Turn on to include these characters in the main masterlist as well. Turn off to entirely seperate them into the sub masterlist.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($sublist->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-sublist-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/sublists/delete') }}/{{ $sublist->id }}", 'Delete Sub Masterlist');
            });
        });

        $(document).ready(function() {
            $('#categoryList').selectize({});
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

        $(document).ready(function() {
            $('#speciesList').selectize({});
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
