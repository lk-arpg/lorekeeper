@extends('admin.layout')

@section('admin-title')
    Currencies
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Currencies' => 'admin/data/currencies']) !!}

    <h1>
        Currencies</h1>

    <p>This is a list of currencies that can be earned by users and/or characters. While they're collectively called "currencies", they can be used to track activity counts, event-only reward points, etc. and are not necessarily transferrable and/or can
        be spent. More information can be found on the creating/editing pages.</p>

    <p>The order of currencies as displayed on user and character profiles can be edited from the <strong><a href="{{ url('admin/data/currencies/sort') }}">Sort Currencies</a></strong> page.</p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/currency-categories') }}"><i class="fas fa-folder"></i> Currency Categories</a>
        <a class="btn btn-primary" href="{{ url('admin/data/currencies/create') }}"><i class="fas fa-plus"></i> Create New Currency</a>
    </div>

    {!! $currencies->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-12 col-md-3">
                    <div class="logs-table-cell">Name</div>
                </div>
                <div class="col-4 col-md-3">
                    <div class="logs-table-cell">Category</div>
                </div>
                <div class="col-4 col-md-3">
                    <div class="logs-table-cell">Displays As</div>
                </div>
                <div class="col-3 col-md-2">
                    <div class="logs-table-cell">Attaches To</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($currencies as $currency)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-12 col-md-3">
                            <div class="logs-table-cell">
                                @if (!$currency->is_visible)
                                    <i class="fas fa-eye-slash mr-1"></i>
                                @endif
                                {{ $currency->name }} @if ($currency->abbreviation)
                                    ({{ $currency->abbreviation }})
                                @endif
                            </div>
                        </div>
                        <div class="col-3 col-md-3">
                            <div class="logs-table-cell">{!! $currency->category ? $currency->category->displayName : '' !!}</div>
                        </div>
                        <div class="col-3 col-md-3">
                            <div class="logs-table-cell">{!! $currency->display(100) !!}</div>
                        </div>
                        <div class="col-3 col-md-2">
                            <div class="logs-table-cell">{{ $currency->is_user_owned ? 'User' : '' }} {{ $currency->is_character_owned && $currency->is_user_owned ? '+' : '' }} {{ $currency->is_character_owned ? 'Character' : '' }}</div>
                        </div>
                        <div class="col-3 col-md-1">
                            <div class="logs-table-cell"><a href="{{ url('admin/data/currencies/edit/' . $currency->id) }}" class="btn btn-primary">Edit</a></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $currencies->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $currencies->total() }} result{{ $currencies->total() == 1 ? '' : 's' }} found.</div>
@endsection
