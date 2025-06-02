<div class="{{ isset($compact) && !$compact ? 'card' : '' }} mt-3">
    <div class="{{ isset($compact) && !$compact ? 'card-body' : '' }}">
        {!! Form::open(['url' => 'comments/make/' . base64_encode(urlencode(get_class($model))) . '/' . $model->getKey()]) !!}
        <input type="hidden" name="type" value="{{ isset($type) ? $type : null }}" />
        <div class="mb-3">
            {!! Form::label('message', 'Enter your message here:') !!}
            {!! Form::textarea('message', null, ['class' => 'form-control ' . (config('lorekeeper.settings.wysiwyg_comments') ? 'comment-wysiwyg' : ''), 'rows' => 5, config('lorekeeper.settings.wysiwyg_comments') ? '' : 'required']) !!}
            @if (!config('lorekeeper.settings.wysiwyg_comments'))
                <small class="form-text text-muted"><a target="_blank" href="https://help.github.com/articles/basic-writing-and-formatting-syntax">Markdown</a> cheatsheet.</small>
            @endif
        </div>

        {!! Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-success text-uppercase']) !!}
        {!! Form::close() !!}
    </div>
</div>
<br />
