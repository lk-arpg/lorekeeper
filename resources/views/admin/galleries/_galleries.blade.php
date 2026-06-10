<div class="{{ isset($gallery->parent_id) && $gallery->parent ? 'col-12 column mw-100 pr-0' : '' }} pt-2" style="flex-basis: 100%;">
    <div class="row flex-wrap">
        <div class="col-6 col-md-1">
            <div class="logs-table-cell">
                {!! $gallery->submissions_open ? '<i class="text-success fas fa-check"></i>' : '-' !!}
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="logs-table-cell">
                <h6>
                    @if (isset($gallery->parent_id) && $gallery->parent)
                        <i class="fas fa-caret-right"></i>
                    @endif
                    {!! $gallery->displayName !!}
                </h6>
            </div>
        </div>
        <div class="col-6 col-md-1">
            <div class="logs-table-cell">
                {!! $gallery->criteria->count() > 0 ? '<i class="text-success fas fa-check"></i>' : '-' !!}
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="logs-table-cell">
                {!! Settings::get('gallery_submissions_require_approval') ? ($gallery->votes_required ? $gallery->votes_required : '-') : '' !!}
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="logs-table-cell">
                {!! $gallery->start_at ? pretty_date($gallery->start_at) : '-' !!}
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="logs-table-cell">
                {!! $gallery->end_at ? pretty_date($gallery->end_at) : '-' !!}
            </div>
        </div>
        <div class="col-6 col-md-2 text-right">
            <div class="logs-table-cell">
                <a href="{{ url('admin/data/galleries/edit/' . $gallery->id) }}" class="btn btn-primary">Edit</a>
            </div>
        </div>
    </div>

    @if ($gallery->children->count() > 0)
        @foreach ($gallery->children as $child)
            <div class="logs-table-row">
                @include('admin.galleries._galleries', ['gallery' => $child])
            </div>
        @endforeach
    @endif

</div>
