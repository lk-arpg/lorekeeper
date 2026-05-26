<div class="col-md-4 mb-3">
    <div class="card">
        <div class="card-body text-center">
            @if ($attachment->attachment->imageUrl)
                <div>
                    <img src="{{ $attachment->attachment->imageUrl }}" alt="Attachment Image" class="img-fluid mb-3" />
                </div>
            @endif
            <div class="h5">{!! $attachment->attachment->displayName ?? $attachment->attachment->name !!}</div>
            <div>{!! $attachment->parsed_description !!}</div>
            {{-- display custom things here --}}
        </div>
    </div>
</div>
