@extends('admin.layout')

@section('admin-title')
    Prompts
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Prompts' => 'admin/data/prompts']) !!}

    <h1>Prompts</h1>

    <p>This is a list of prompts users can submit to.</p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/prompt-categories') }}"><i class="fas fa-folder"></i> Prompt Categories</a>
        <a class="btn btn-primary" href="{{ url('admin/data/prompts/create') }}"><i class="fas fa-plus"></i> Create New Prompt</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end mb-3']) !!}
        <div class="form-group">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::select('prompt_category_id', $categories, Request::get('prompt_category_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::select('open_prompts', ['any' => 'Any Status', 'open' => 'Open Prompts', 'closed' => 'Closed Prompts'], Request::get('open_prompts') ?? 'any', ['class' => 'form-control selectize']) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::select(
                'sort',
                [
                    'alpha' => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                    'category' => 'Sort by Category',
                    'newest' => 'Newest First',
                    'oldest' => 'Oldest First',
                    'start' => 'Starts Earliest',
                    'start-reverse' => 'Starts Latest',
                    'end' => 'Ends Earliest',
                    'end-reverse' => 'Ends Latest',
                ],
                Request::get('sort') ?: 'category',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($prompts))
        <p>No prompts found.</p>
    @else
        {!! $prompts->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-4 col-md-1">
                        <div class="logs-table-cell">Active</div>
                    </div>
                    <div class="col-4 col-md-3">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-4 col-md-3">
                        <div class="logs-table-cell">Category</div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="logs-table-cell">Starts</div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="logs-table-cell">Ends</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($prompts as $prompt)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-2 col-md-1">
                                <div class="logs-table-cell">
                                    {!! $prompt->is_active ? '<i class="text-success fas fa-check"></i>' : '' !!}
                                </div>
                            </div>
                            <div class="col-5 col-md-3 text-truncate">
                                <div class="logs-table-cell">
                                    {{ $prompt->name }}
                                </div>
                            </div>
                            <div class="col-5 col-md-3">
                                <div class="logs-table-cell">
                                    {{ $prompt->category ? $prompt->category->name : '-' }}
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="logs-table-cell">
                                    {!! $prompt->start_at ? pretty_date($prompt->start_at) : '-' !!}
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="logs-table-cell">
                                    {!! $prompt->end_at ? pretty_date($prompt->end_at) : '-' !!}
                                </div>
                            </div>
                            <div class="col-3 col-md-1 text-right">
                                <div class="logs-table-cell">
                                    <a href="{{ url('admin/data/prompts/edit/' . $prompt->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {!! $prompts->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $prompts->total() }} result{{ $prompts->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
