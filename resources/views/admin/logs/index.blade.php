@extends('admin.layout')

@section('admin-title')
    Log Viewer
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Logs' => 'admin/logs']) !!}

    <h1>Log Viewer</h1>

    <p>View your site logs without logging into the host console. Logs may not persist forever depending on your app's settings.</p>


    <table class="table table-sm">
        <thead>
            <tr>
                <th>Logs</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>
                        <a href="/admin/logs/{{ $log }}">{{ $log }}</a>
                    </td>
                    <td class="text-right">
                        <a href="/admin/logs/{{ $log }}" class="btn btn-outline-primary btn-sm move-log" data-name="{{ $log }} ">View</a>
                        <a href="#" class="btn btn-outline-danger btn-sm delete-log" data-name="{{ $log }}">Delete</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Delete Log</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    {{-- Delete a file --}}
                    {!! Form::open(['url' => 'admin/logs/delete', 'id' => 'deleteForm', 'class' => 'file-form']) !!}
                    <p>This will permanently delete <strong id="deleteFilename"></strong>. Are you sure?</p>
                    <div class="text-right">
                        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                    </div>
                    {!! Form::hidden('filename', '', ['class' => 'edit-filename']) !!}
                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.delete-log').on('click', function(e) {
                e.preventDefault();
                $('#deleteForm').find('.edit-filename').val($(this).data('name'));
                $('#deleteFilename').html($(this).data('name'));
                $('.file-form').addClass('hide');
                $('#deleteForm').removeClass('hide');
                $('#deleteModal').modal('show');
            });
        });
    </script>
@endsection
