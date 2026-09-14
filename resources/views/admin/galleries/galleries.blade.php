@extends('admin.layout')

@section('admin-title')
    Galleries
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Galleries' => 'admin/data/galleries']) !!}

    <h1>Galleries</h1>

    <p>This is a list of galleries that art and literature can be submitted to.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/galleries/create') }}"><i class="fas fa-plus"></i> Create New Gallery</a></div>

    @if (!count($galleries))
        <p>No galleries found.</p>
    @else
        {!! $galleries->render() !!}

        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-6 col-md-1">
                        <div class="logs-table-cell">Open</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-6 col-md-1">
                        <div class="logs-table-cell">Rewards</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">{{ Settings::get('gallery_submissions_require_approval') ? 'Votes Needed' : '' }}</div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="logs-table-cell">Start</div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="logs-table-cell">End</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($galleries as $gallery)
                    <div class="logs-table-row">
                        @include('admin.galleries._galleries', ['gallery' => $gallery])
                    </div>
                @endforeach
            </div>
        </div>

        {!! $galleries->render() !!}
    @endif

@endsection
