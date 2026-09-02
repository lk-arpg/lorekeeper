@extends('account.layout')

@section('account-title')
    Reorder Bookmarks
@endsection

@section('account-content')
    {!! breadcrumbs(['My Account' => Auth::user()->url, 'Character Bookmarks' => 'account/bookmarks?folder=' . $folderToken, 'Reorder' => 'account/bookmarks/reorder?folder=' . $folderToken]) !!}

    <h1>Reorder Bookmarks <small class="text-muted">{{ $folderName }}</small></h1>

    <p>Drag the <i class="fas fa-arrows-alt-v"></i> handle to reorder bookmarks in this folder, then click <strong>Save Order</strong>. Top entries appear first.</p>

    @if (!count($bookmarks))
        <div class="text-center mb-3">No bookmarks in this folder yet.</div>
        <div class="text-right"><a href="{{ url('account/bookmarks') }}?folder={{ $folderToken }}" class="btn btn-secondary">Back</a></div>
    @else
        {!! Form::open(['url' => 'account/bookmarks/sort']) !!}
        {!! Form::hidden('folder', $folderToken) !!}
        {!! Form::hidden('sort', null, ['id' => 'bookmarkSortOrder']) !!}

        <div class="text-right border-bottom pb-2 mb-3">
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        </div>

        <div class="list-group mb-3" id="bookmarkSortable">
            @foreach ($bookmarks as $bookmark)
                <div class="list-group-item p-2 sort-item" data-id="{{ $bookmark->id }}">
                    <div class="d-flex align-items-center">
                        <a class="fas fa-arrows-alt-v handle text-muted mr-3" href="#"></a>
                        <img src="{{ $bookmark->character->image->thumbnailUrl }}" class="img-thumbnail mr-3" alt="{{ $bookmark->character->fullName }}" style="max-width: 60px;" />
                        <div>
                            <strong>{!! $bookmark->character->displayName !!}</strong>
                            <div class="small text-muted">
                                {!! $bookmark->character->image->species_id ? $bookmark->character->image->species->displayName : 'No Species' !!} ・ {!! $bookmark->character->image->rarity_id ? $bookmark->character->image->rarity->displayName : 'No Rarity' !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-between border-top pt-2">
            <a href="{{ url('account/bookmarks') }}?folder={{ $folderToken }}" class="btn btn-secondary">Cancel</a>
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            var $sortable = $('#bookmarkSortable');
            var $order = $('#bookmarkSortOrder');

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
@endsection
