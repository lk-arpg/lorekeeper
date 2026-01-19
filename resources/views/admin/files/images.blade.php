@extends('admin.layout')

@section('admin-title')
    Site Images
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Site Images & CSS' => 'admin/images']) !!}

    <h1>Site Images</h1>

    <p>Upload images to replace the current site images. The specifications for each image are noted in the descriptions for each image. (Maximum size of an image is {{ min(ini_get('upload_max_filesize'), ini_get('post_max_size')) }}B.)</p>

    @foreach ($images as $key => $image)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex flex-column flex-sm-row">
                    <div class="mr-2" style="width: 200px;"><img src="{{ asset('images/' . $image['filename'] . '?v=' . filemtime(public_path('images/' . $image['filename']))) }}" class="mw-100" alt="Site image: {{ $image['name'] }}" /></div>
                    <div style="width: 100%;">
                        <h3 class="card-heading">{{ $image['name'] }} <a href="{{ asset('images/' . $image['filename']) }}" class="btn btn-info btn-sm float-right">View Current</a></h3>
                        <p>{{ $image['description'] }}</p>
                        {!! Form::open(['url' => 'admin/images/upload', 'files' => true]) !!}
                        <div class="d-flex">
                            <div class="custom-file">
                                {!! Form::label('file', 'Choose file...', ['class' => 'custom-file-label']) !!}
                                {!! Form::file('file', ['class' => 'custom-file-input']) !!}
                            </div>
                            {!! Form::submit('Upload', ['class' => 'ml-1 btn btn-primary']) !!}
                        </div>
                        {!! Form::hidden('key', $key) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <h1>Site CSS</h1>

    <p>A custom CSS file can be uploaded here. This will be added to the page after the inclusion of other CSS files, and reuploading the file will replace the original. Making changes to the site code is recommended for large changes to the layout, but
        this upload can be used for simple updates.</p>
    <div class="card mb-3">
        <div class="card-body">
            <div>
                <h3 class="card-heading">CSS <a href="{{ asset('css/custom.css') }}" class="btn btn-info btn-sm float-right">View Current</a></h3>
                {!! Form::open(['url' => 'admin/images/upload/css', 'files' => true]) !!}
                <div class="d-flex">
                    <div class="custom-file">
                        {!! Form::label('file', 'Choose CSS...', ['class' => 'custom-file-label']) !!}
                        {!! Form::file('file', ['class' => 'custom-file-input']) !!}
                    </div>
                    {!! Form::submit('Upload', ['class' => 'ml-1 btn btn-primary']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection
