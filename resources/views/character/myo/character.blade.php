@extends('character.layout', ['isMyo' => true])

@section('profile-title')
    {{ $character->fullName }}
@endsection

@section('profile-content')
    {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url]) !!}

    @include('character._header', ['character' => $character])

    {{-- Main Image --}}
    <div class="row mb-3">
        <div class="text-center col-md-7">
            <a href="{{ $character->image->imageUrl }}" data-lightbox="entry" data-bs-title="{{ $character->fullName }}">
                <img src="{{ $character->image->imageUrl }}" class="image" alt="{{ $character->fullName }}" />
            </a>
        </div>
        @include('character._image_info', ['image' => $character->image])
    </div>

    {{-- Info --}}
    <div class="card character-bio">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" id="statsTab" data-bs-toggle="tab" href="#stats" role="tab">Stats</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notesTab" data-bs-toggle="tab" href="#notes" role="tab">Description</a>
                </li>
                @if (Auth::check() && Auth::user()->hasPower('manage_characters'))
                    <li class="nav-item">
                        <a class="nav-link" id="settingsTab" data-bs-toggle="tab" href="#settings-all" role="tab"><i class="fas fa-cog"></i></a>
                    </li>
                @endif
            </ul>
        </div>
        <div class="card-body tab-content">
            <div class="tab-pane fade show active" id="stats">
                @include('character._tab_stats', ['character' => $character])
            </div>
            <div class="tab-pane fade" id="notes">
                @include('character._tab_notes', ['character' => $character])
            </div>
            @if (Auth::check() && Auth::user()->hasPower('manage_characters'))
                <div class="tab-pane fade" id="settings-all">
                    {!! Form::open(['url' => $character->is_myo_slot ? 'admin/myo/' . $character->id . '/settings' : 'admin/character/' . $character->slug . '/settings']) !!}
                    <div class="mb-3">
                        {!! Form::checkbox('is_visible', 1, $character->is_visible, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ms-3']) !!} {!! add_help('Turn this off to hide the character. Only mods with the Manage Masterlist power (that\'s you!) can view it - the owner will also not be able to see the character\'s page.') !!}
                    </div>
                    <div class="text-end">
                        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
                    </div>
                    {!! Form::close() !!}
                    <hr />
                    <div class="text-end">
                        <a href="#" class="btn btn-outline-danger btn-sm delete-character" data-id="{{ $character->id }}">Delete</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    @include('character._image_js')
@endsection
