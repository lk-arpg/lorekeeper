@extends('admin.layout')

@section('admin-title')
    Log Viewer - {{ $name }}
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Logs' => 'admin/logs', $name => 'admin/logs/' . $name]) !!}


    <h1>{{ $name }}</h1>

    <code>
        @foreach ($log as $line)
            <span class="text-danger">{{ $line['line'] }}</span>
            <br>
            @if (isset($line['stacktrace']))
                <div class="text-right mb-2">
                    <a class="btn btn-secondary btn-sm ml-auto" data-toggle="collapse" href="#stacktrace-{{ $loop->index }}" role="button" aria-expanded="false">
                        View Stacktrace
                    </a>
                </div>
                <div class="collapse" id="stacktrace-{{ $loop->index }}">
                    <div class="card card-body">
                        @foreach ($line['stacktrace'] as $trace)
                            <span class="text-secondary">{{ $trace }}</span>
                        @endforeach
                    </div>
                </div>
                <hr>
            @endif
        @endforeach
    </code>
@endsection
