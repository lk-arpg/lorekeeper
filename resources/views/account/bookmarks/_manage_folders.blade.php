<p>Folders are private to your account. Deleting a folder does not delete its bookmarks; they revert to <strong>Uncategorized</strong>.</p>

@if ($folders->count())
    <div class="list-group mb-2" id="folderSortable">
        @foreach ($folders as $folder)
            <div class="list-group-item p-2 sort-item" data-id="{{ $folder->id }}">
                <div class="d-flex align-items-center">
                    <a class="fas fa-arrows-alt-v handle text-muted mr-2" href="#"></a>
                    {!! Form::open(['url' => 'account/bookmarks/folders/edit/' . $folder->id, 'class' => 'form-inline flex-grow-1']) !!}
                    {!! Form::text('name', $folder->name, ['class' => 'form-control form-control-sm flex-grow-1 mr-2', 'maxLength' => 50]) !!}
                    {!! Form::submit('Rename', ['class' => 'btn btn-sm btn-primary']) !!}
                    {!! Form::close() !!}
                    {!! Form::open([
                        'url' => 'account/bookmarks/folders/delete/' . $folder->id,
                        'class' => 'ml-2',
                        'onsubmit' => 'return confirm(' . json_encode('Delete folder "' . $folder->name . '"? Its ' . $folder->bookmarks_count . ' bookmark(s) will move to Uncategorized.') . ');',
                    ]) !!}
                    {!! Form::submit('Delete', ['class' => 'btn btn-sm btn-danger']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        @endforeach
    </div>
    {!! Form::open(['url' => 'account/bookmarks/folders/sort', 'class' => 'text-right border-top pt-2 mb-3']) !!}
    {!! Form::hidden('sort', null, ['id' => 'folderSortOrder']) !!}
    {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}
@else
    <p>You don't have any folders yet.</p>
@endif

<h5>New Folder</h5>
{!! Form::open(['url' => 'account/bookmarks/folders/new', 'class' => 'form-inline']) !!}
{!! Form::text('name', null, ['class' => 'form-control flex-grow-1 mr-2', 'placeholder' => 'Folder name', 'maxLength' => 50]) !!}
{!! Form::submit('Add Folder', ['class' => 'btn btn-primary']) !!}
{!! Form::close() !!}

<script>
    $(function() {
        var $sortable = $('#folderSortable');
        var $order = $('#folderSortOrder');

        function updateOrder() {
            $order.val($sortable.sortable('toArray', {
                attribute: 'data-id'
            }));
        }

        $sortable.find('.handle').on('click', function(e) {
            e.preventDefault();
        });
        $sortable.sortable({
            items: '.sort-item',
            handle: '.handle',
            placeholder: 'sortable-placeholder',
            stop: updateOrder,
            create: updateOrder
        });
        $sortable.disableSelection();
    });
</script>
