@php
    $attachments = getAttachments($object);
    if (!isset($style)) {
        $style = null;
    }
@endphp
<div class="row">
    @foreach ($attachments as $attachment)
        @if ($style && View::exists('widgets.attachments._' . $style))
            @include('widgets.attachments._' . $style, ['attachment' => $attachment])
        @else
            @include('widgets.attachments._card', ['attachment' => $attachment])
        @endif
    @endforeach
</div>
