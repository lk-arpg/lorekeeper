@extends('home.layout')

@section('home-title')
    My Characters
@endsection

@section('home-content')
    {!! breadcrumbs(['My Characters' => 'characters']) !!}

    <h1>
        My Characters
    </h1>

    <p>This is a list of characters you own. Drag and drop to rearrange them.</p>

    <div id="sortable" class="row sortable">
        @foreach ($characters as $character)
            <div class="col-md-3 col-6 text-center mb-2" data-id="{{ $character->id }}">
                <div>
                    <a href="{{ $character->url }}"><img src="{{ $character->image->thumbnailUrl }}" class="img-thumbnail" alt="Thumbnail for {{ $character->fullName }}" /></a>
                </div>
                <div class="mt-1 h5">
                    {!! $character->displayName !!}
                </div>
            </div>
        @endforeach
    </div>
    {!! Form::open(['url' => 'characters/sort', 'class' => 'text-right']) !!}
    {!! Form::hidden('sort', null, ['id' => 'sortableOrder']) !!}
    {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}

    <div class="mobile-handle handle-clone badge badge-primary rounded-circle hide">
        <i class="fas fa-hand-point-up" aria-hidden="true"></i>
        <span class="sr-only">Drag Handle</span>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $("#sortable").sortable({
                characters: '.sort-item',
                placeholder: "sortable-placeholder col-md-3 col-6",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();

            function isTouch() {
                try {
                    document.createEvent("TouchEvent");
                    return true;
                } catch (e) {
                    return false;
                }
            }

            if (isTouch()) {
                $('#sortable').children().each(function() {
                    var $clone = $('.handle-clone').clone();
                    $(this).append($clone);
                    $clone.removeClass('hide handle-clone');
                });
                $("#sortable").sortable("option", "handle", ".mobile-handle");
            }
        });
    </script>
@endsection
