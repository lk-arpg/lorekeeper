@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Images
@endsection

@section('profile-content')
    {!! breadcrumbs([
        $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
        $character->fullName => $character->url,
        'Images' => $character->url . '/images',
    ]) !!}

    @include('character._header', ['character' => $character])

    <div class="tab-content">
        @foreach ($character->images($user)->with('features.feature')->with('species')->with('rarity')->get() as $image)
            <div class="tab-pane fade {{ $image->id == $character->character_image_id ? 'show active' : '' }}" id="image-{{ $image->id }}">
                <div class="row mb-3">
                    <div class="col-md-7">
                        <div class="text-center">
                            <a href="{{ $image->canViewFull(Auth::user() ?? null) && file_exists(public_path($image->imageDirectory . '/' . $image->fullsizeFileName)) ? $image->fullsizeUrl : $image->imageUrl }}" data-lightbox="entry"
                                data-title="{{ $character->fullName }} [#{{ $image->id }}] {{ $image->canViewFull(Auth::user() ?? null) && file_exists(public_path($image->imageDirectory . '/' . $image->fullsizeFileName)) ? ' : Full-size Image' : '' }}">
                                <img src="{{ $image->canViewFull(Auth::user() ?? null) && file_exists(public_path($image->imageDirectory . '/' . $image->fullsizeFileName)) ? $image->fullsizeUrl : $image->imageUrl }}"
                                    class="image {{ $character->image->showContentWarnings(Auth::user() ?? null) ? 'content-warning' : '' }}" alt="{{ $image->character->fullName }}" />
                            </a>
                        </div>
                        @if ($image->canViewFull(Auth::user() ?? null) && file_exists(public_path($image->imageDirectory . '/' . $image->fullsizeFileName)))
                            <div class="text-right">You are viewing the full-size image. <a href="{{ $image->imageUrl }}">View watermarked image</a>?</div>
                        @endif
                    </div>
                    @include('character._image_info', ['image' => $image])
                </div>
            </div>
        @endforeach
    </div>
    <?php $canManage = Auth::check() && Auth::user()->hasPower('manage_characters'); ?>
    <h3>
        Images
        @if ($canManage)
            <a href="{{ url('admin/character/' . $character->slug . '/image') }}" class="float-right btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Add Image</a>
        @endif
    </h3>

    <ul class="row nav image-nav mb-2" @if ($canManage) id="sortable" @endif>
        @foreach ($character->images($user)->get() as $image)
            <li class="col-md-3 col-6 text-center nav-item sort-item" data-id="{{ $image->id }}">
                <a id="thumbnail-{{ $image->id }}" data-toggle="tab" href="#image-{{ $image->id }}" role="tab" class="{{ $image->id == $character->character_image_id ? 'active' : '' }}"><img src="{{ $image->thumbnailUrl }}"
                        class="img-thumbnail {{ $character->image->showContentWarnings(Auth::user() ?? null) ? 'content-warning' : '' }}" alt="Thumbnail for {{ $image->character->fullName }}" /></a>
            </li>
        @endforeach
    </ul>
    @if ($canManage)
        {!! Form::open(['url' => 'admin/character/' . $character->slug . '/images/sort', 'class' => 'text-right']) !!}
        {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}

        <div class="mobile-handle handle-clone badge badge-primary rounded-circle hide">
            <i class="fas fa-hand-point-up" aria-hidden="true"></i>
            <span class="sr-only">Drag Handle</span>
        </div>
    @endif
@endsection
@section('scripts')
    @parent
    @include('character._image_js')
    @if ($canManage)
        <script>
            $(document).ready(function() {
                $("#sortable").sortable({
                    characters: '.sort-item',
                    placeholder: "sortable-placeholder",
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
    @endif
@endsection
