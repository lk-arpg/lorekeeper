@extends('admin.layout')

@section('admin-title')
    Staff Reward Settings
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Staff Reward Settings' => 'admin/staff-reward-settings']) !!}

    @if (!config('lorekeeper.extensions.staff_rewards.enabled'))
        <div class="alert alert-danger">
            Staff rewards are currently disabled. Enable them in the Lorekeeper configuration files to use this feature.
        </div>
    @endif

    <h1>Staff Reward Settings</h1>

    <p>This is a list of staff actions that are configured to receive rewards. Staff members will be granted the value configured in {!! isset($currency) && $currency ? $currency->displayName : '(Invalid Currency - Configure ID in config/lorekeeper/extensions.php)' !!} when they perform the relevant action(s). Set an action's value to 0 to disable rewards for it.
    </p>

    @if (!count($settings))
        <p>No settings found.</p>
    @else
        {!! $settings->render() !!}
        <div class="mb-4 logs-table setting-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Description</div>
                    </div>
                    <div class="col-md-6">
                        <div class="logs-table-cell">Value</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($settings as $setting)
                    <div class="row">
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">{{ $setting->name }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">{{ $setting->description }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">
                                {!! Form::open(['url' => 'admin/staff-reward-settings/' . $setting->key, 'class' => 'd-flex justify-content-end']) !!}
                                <div class="mb-3 me-3 mb-3">
                                    {!! Form::text('value', $setting->value, ['class' => 'form-control']) !!}
                                </div>
                                <div class="mb-3 mb-3">
                                    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
        {!! $settings->render() !!}
    @endif

@endsection
