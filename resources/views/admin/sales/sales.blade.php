@extends('admin.layout')

@section('admin-title')
    Sales
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Sales' => 'admin/sales']) !!}

    <h1>Sales</h1>

    <p>You can create new sales posts here. Creating a sales post alerts every user that there is a new post, unless the post is marked as not viewable (see the post creation page for details).</p>

    <div class="text-end mb-3"><a class="btn btn-primary" href="{{ url('admin/sales/create') }}"><i class="fas fa-plus"></i> Create New Sales Post</a></div>
    @if (!count($saleses))
        <p>No sales found.</p>
    @else
        {!! $saleses->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-5">
                        <div class="logs-table-cell">Title</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Posted At</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Last Edited</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($saleses as $sales)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-5">
                                <div class="logs-table-cell">
                                    @if (!$sales->is_visible)
                                        @if ($sales->post_at)
                                            <i class="fas fa-clock me-1" data-bs-toggle="tooltip" title="This post is scheduled to be posted in the future."></i>
                                        @else
                                            <i class="fas fa-eye-slash me-1" data-bs-toggle="tooltip" title="This post is hidden."></i>
                                        @endif
                                    @endif
                                    <a href="{{ $sales->url }}">{{ $sales->title }}</a>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell">{!! pretty_date($sales->post_at ?: $sales->created_at) !!}</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell">{!! pretty_date($sales->updated_at) !!}</div>
                            </div>
                            <div class="col-12 col-md-1 text-end">
                                <div class="logs-table-cell"><a href="{{ url('admin/sales/edit/' . $sales->id) }}" class="btn btn-primary py-0 px-2 w-100">Edit</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $saleses->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $saleses->total() }} result{{ $saleses->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection
