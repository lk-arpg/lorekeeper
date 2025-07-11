@if (isset($submission->content_warning))
    <img class="img-thumbnail" src="{{ asset('images/content-warning.png') }}" alt="Content Warning" />
@elseif(isset($submission->hash))
    <img class="img-thumbnail" src="{{ $submission->thumbnailUrl }}" alt="Submission thumbnail" />
@else
    <div class="mx-auto img-thumbnail text-left" style="height:{{ config('lorekeeper.settings.masterlist_thumbnails.height') + 8 }}px; width:{{ config('lorekeeper.settings.masterlist_thumbnails.width') + 4 }}px;">
        <span class="badge-primary px-2 py-1" style="border-radius:0 0 .5em 0; position:absolute; z-index:5;">Literature</span>
        <div class="container-{{ $submission->id }} parsed-text pb-2 pr-2"
            style="height:{{ config('lorekeeper.settings.masterlist_thumbnails.height') }}px; width:{{ config('lorekeeper.settings.masterlist_thumbnails.width') }}px; overflow:hidden; max-width:fit-content;">
            <div class="content-{{ $submission->id }} text-body">{!! $submission->excerpt !!}</div>
        </div>
    </div>
    <style>
        .content-{{ $submission->id }} {
            transition-duration: {{ strlen(substr($submission->parsed_text, 0, 500)) / 1000 }}s;
        }

        .content-{{ $submission->id }}:hover,
        .content-{{ $submission->id }}:focus-within {
            transform: translateY(calc({{ config('lorekeeper.settings.masterlist_thumbnails.height') }}px - 100%));
            transition-duration: {{ strlen(substr($submission->parsed_text, 0, 500)) / 100 }}s;
        }
    </style>
@endif
