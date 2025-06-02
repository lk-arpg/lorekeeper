@if (config('lorekeeper.extensions.show_all_recent_submissions.enable') && config('lorekeeper.extensions.show_all_recent_submissions.section_on_front'))
    <div class="card my-2 text-center">
        <div class="card-header">
            <h5>Recent Gallery Submissions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @if (count($gallerySubmissions))
                    @foreach ($gallerySubmissions as $gallerySubmission)
                        <div class="col-md-3 col-6 profile-inventory-item">
                            @include('galleries._thumb', ['submission' => $gallerySubmission, 'gallery' => false])
                        </div>
                    @endforeach
                @else
                    <div class="col-12">No Gallery Submissions.</div>
                @endif
                <div class="col-12"><a class="float-end" href="gallery/all">View all Recent Submissions...</a></div>
            </div>
        </div>
    </div>
@endif
