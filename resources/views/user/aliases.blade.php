@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Aliases
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Aliases' => $user->url . '/aliases']) !!}

    <h1>
        {!! $user->displayName !!}'s Aliases
    </h1>

    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-4">
                    <div class="logs-table-cell">Alias</div>
                </div>
                <div class="col-3">
                    <div class="logs-table-cell">Site</div>
                </div>
                <div class="col-5">
                    <div class="logs-table-cell"></div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($aliases as $alias)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-4">
                            <div class="logs-table-cell">{!! $alias->displayAlias !!}</div>
                        </div>
                        <div class="col-3">
                            <div class="logs-table-cell"><i class="{{ $alias->config['icon'] }} fa-fw me-1"></i> {{ $alias->config['full_name'] }}</div>
                        </div>
                        <div class="col-5">
                            <div class="logs-table-cell">
                                @if ($alias->is_primary_alias)
                                    <span class="badge text-bg-success">Primary</span>
                                @endif
                                @if (!$alias->is_visible)
                                    <i class="fas fa-eye-slash" data-bs-toggle="tooltip" title="This alias is hidden from public view."></i>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
