@extends('admin.layout')

@section('admin-title')
    Dynamic Limits
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Limits' => 'admin/data/limits']) !!}

    <h1>Dynamic Limits</h1>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/limits/create') }}"><i class="fas fa-plus"></i> Create New Limit</a></div>
    @if (!count($limits))
        <p>No limits found.</p>
    @else
        <table class="table table-sm limit-table">
            <tbody>
                @foreach ($limits as $limit)
                    <tr>
                        <td>
                            {!! $limit->name !!}
                        </td>
                        <td>
                            {!! Str::limit($limit->description, 100) !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/limits/edit/' . $limit->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

@endsection
