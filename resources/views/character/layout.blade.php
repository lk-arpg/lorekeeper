@extends('layouts.app')

@section('title')
    Character{!! View::hasSection('profile-title') ? ' :: ' . trim(View::getSection('profile-title')) : '' !!}
@endsection

@section('meta-img')
    {{ $character->image->content_warnings ? asset('images/content-warning.png') : $character->image->thumbnailUrl }}
@endsection

@section('sidebar')
    @include('character.' . ($isMyo ? 'myo.' : '') . '_sidebar')
@endsection

@section('content')
    @yield('profile-content')
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.bookmark-button').on('click', function(e) {
                e.preventDefault();
                var $this = $(this);
                loadModal($this.data('id') ? "{{ url('account/bookmarks/edit') }}" + '/' + $this.data('id') : "{{ url('account/bookmarks/create') }}?character_id=" + $this.data('character-id'), $this.data('id') ? 'Edit Bookmark' :
                    'Bookmark Character');
            });
        });
    </script>
@endsection
