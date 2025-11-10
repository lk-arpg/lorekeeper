@extends('admin.layout')

@section('admin-title')
    {{ $limit->id ? 'Edit' : 'Create' }} Limit
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Limits' => 'admin/data/limits', ($limit->id ? 'Edit' : 'Create') . ' Limit' => $limit->id ? 'admin/data/limits/edit/' . $limit->id : 'admin/data/limits/create']) !!}

    <h1>{{ $limit->id ? 'Edit' : 'Create' }} Limit
        @if ($limit->id)
            <a href="#" class="btn btn-danger float-right delete-limit-button">Delete Limit</a>
        @endif
    </h1>

    {!! Form::open(['url' => $limit->id ? 'admin/data/limits/edit/' . $limit->id : 'admin/data/limits/create', 'id' => 'form']) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $limit->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $limit->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <hr />

    <h5>Evalutation</h5>
    <p>Enter the PHP code that will be evaluated to determine if the limit is met. The code should return a boolean <code>(true / false)</code> value.</p>
    <p>Laravel facades are accessible. For example, you can use <code>Auth::user()</code> to get the currently authenticated user.</p>
    <div class="mb-3" id="editor" style="height: 500px; width: 100%;"></div>

    {!! Form::hidden('evaluation', $limit->evaluation, ['id' => 'evaluation']) !!}

    <div class="text-right">
        {!! Form::submit($limit->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary', 'id' => 'submit']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.11.2/ace.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.11.2/mode-php.js"></script>
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.delete-limit-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/limits/delete') }}/{{ $limit->id }}", 'Delete Limit');
            });

            var editor = ace.edit("editor");
            editor.setTheme("ace/theme/monokai");
            editor.session.setMode("ace/mode/php");

            editor.setValue(`{!! $limit->evaluation ? $limit->evaluation : '<?php\n\n' !!}`);

            $('#submit').on('click', function(e) {
                $('#evaluation').val(editor.getValue());
            });
        });
    </script>
@endsection
