@extends('admin.layout')

@section('admin-title')
    Default Criteria
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Criteria' => 'admin/data/criteria', 'Default Criteria' => 'admin/data/criteria-defaults']) !!}

    <div class="text-right mb-3">
        <a class="btn btn-secondary" href="{{ url('admin/data/criteria') }}"><i class="fas fa-folder"></i> Back</a>
        <a class="btn btn-primary" href="{{ url('admin/data/criteria-defaults/create') }}"><i class="fas fa-plus"></i> Create New Default</a>
    </div>

    <h2>Default Criteria</h2>
    <p>
        These are default criteria groups that you can auto-populate into prompts and galleries. When a group is toggled on, it will be added to the prompt or gallery with the pre-determined values that you set. You can have as many default groups as you
        want, and they can even contain the same criteria as another group-- just with different preset values.
    </p>

    <div>
        @foreach ($defaults as $default)
            <div class="card p-3 mb-2 pl-0">
                <div class="row">
                    <div class="col-md-4">
                        <h4 class="pb-0 mb-0">
                            {{ $default->name }}
                        </h4>
                        <span class="text-secondary">{{ $default->summary }}</span>
                    </div>
                    <div class="col-md-6">
                        <h5 class="pb-0 mb-0">
                            Criteria:
                        </h5>
                        <ul>
                            @foreach ($default->criteria as $criterion)
                                <li>{{ $criterion->criterion->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-2 text-right">
                        <a href="{{ url('admin/data/criteria-defaults/edit/' . $default->id) }}" class="btn btn-info text-white mr-2"><i class="fas fa-pencil-alt"></i></a>
                        <button class="btn btn-danger delete-button" data-id="{{ $default->id }}"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection



@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-button').on('click', function(e) {
                e.preventDefault();
                var id = $(this).attr('data-id');
                loadModal("{{ url('admin/data/criteria-defaults/delete') }}/" + id, 'Delete Default');
            });
        });
    </script>
@endsection
