@php
    $attachments = hasAttachments($object) ? getAttachments($object) : null;

    $attachmentTypes = getAttachmentTypes();
    $attachmentData = getAttachmentData();
@endphp

<div class="card mb-3" id="attachment-card">
    <div class="card-body">
        <h3>Attachments</h3>

        <p>
            You can add attachments to this object by clicking "Add Attachment" and entering the attachment model and type.
            <br />Each attachment can include additional data fields (e.g., <code>description</code>), and you can add custom fields as needed.
        </p>
        {!! isset($info) ? '<p class="alert alert-info">' . $info . '</p>' : '' !!}

        {!! Form::open(['url' => 'admin/attachments']) !!}
        {!! Form::hidden('parent_model', get_class($object)) !!}
        {!! Form::hidden('parent_id', $object->id) !!}

        <div class="d-flex mb-2">
            <div class="btn btn-secondary ml-auto" id="add-attachment">Add Attachment</div>
        </div>

        <hr class="my-3" />

        <div id="attachments">
            @if ($attachments)
                <h5>Attachments for {!! $attachments->first()->parent->displayName ?? $object->displayName !!}</h5>
                @foreach ($attachments as $index => $attachment)
                    <div class="card mb-3 p-3 attachment-entry" data-index="{{ $index }}">
                        <div class="row">
                            <div class="col-md-5 form-group">
                                {!! Form::select('attachment_type[]', $attachmentTypes, $attachment->attachment_type, [
                                    'class' => 'form-control attachment-type',
                                    'placeholder' => 'Select Attachment Type',
                                ]) !!}
                            </div>
                            <div class="col-md-5 form-group attachment-select">
                                {!! Form::select('attachment_id[]', $attachmentData[$attachment->attachment_type], $attachment->attachment_id, [
                                    'class' => 'form-control attachment-selectize',
                                    'placeholder' => 'Select Attachment',
                                ]) !!}
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="btn btn-danger remove-attachment w-100 mb-4">Remove</div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <div class="d-flex">
                                <div class="h5 mr-auto">Data Fields</div>
                                <div class="btn btn-secondary btn-sm add-data-field" data-index="{{ $index }}">Add Field</div>
                            </div>
                            <div class="data-fields">
                                <div class="form-group">
                                    {!! Form::label("data[$index][description]", 'Description') !!}
                                    {!! Form::text("data[$index][description]", $attachment->description, ['class' => 'form-control', 'placeholder' => 'Optional Description']) !!}
                                </div>
                            </div>

                            @foreach ($attachment->data ?? [] as $key => $value)
                                @if ($key == 'description' || $key == 'parsed_description')
                                    @continue
                                @endif
                                <div class="row data-field">
                                    <div class="col-md-4 d-flex align-items-center form-group">
                                        <span class="ml-2 text-strong">{{ ucfirst($key) }}</span>
                                    </div>
                                    <div class="col-md-7 form-group">
                                        {!! Form::text("data[$index][$key]", $value, ['class' => 'form-control', 'placeholder' => ucfirst($key)]) !!}
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <div class="btn btn-danger btn-sm remove-data-field w-100 mb-3 p-2">X</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        @if ($attachments)
            <i class="fas fa-trash text-danger float-right mt-2 mx-2 fa-2x" data-toggle="tooltip" title="To delete attachments, remove all entries and click 'Edit Attachments'"></i>
        @endif
        {!! Form::submit(($attachments ? 'Edit' : 'Create') . ' Attachments', ['class' => 'btn btn-primary float-right']) !!}

        {!! Form::close() !!}
    </div>
</div>

<fieldset disabled>
    <div id="attachmentRowData" class="hide">
        @foreach ($attachmentTypes as $attachmentKey => $attachmentType)
            {!! Form::select('attachment_id[]', $attachmentData[$attachmentKey], null, ['class' => 'form-control ' . strtolower($attachmentKey) . '-select', 'placeholder' => 'Select ' . $attachmentType]) !!}
        @endforeach
    </div>

    <div id="attachment-template" class="hide">
        <div class="card mb-3 attachment-entry" data-index="__INDEX__">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5 form-group">
                        {!! Form::select('attachment_type[]', $attachmentTypes, null, [
                            'class' => 'form-control attachment-type',
                            'placeholder' => 'Select Attachment Type',
                        ]) !!}
                    </div>
                    <div class="col-md-5 form-group attachment-select"></div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="btn btn-danger remove-attachment w-100 mb-3 p-2">Remove</div>
                    </div>
                </div>

                <div class="mt-2">
                    <div class="d-flex">
                        <div class="h-5 mr-auto">Data Fields</div>
                        <div class="ml-auto btn btn-secondary btn-sm add-data-field" data-index="__INDEX__">Add Field</div>
                    </div>
                    <div class="data-fields">
                        <div class="form-group">
                            {!! Form::label('data[__INDEX__][description]', 'Description') !!}
                            {!! Form::text('data[__INDEX__][description]', null, [
                                'class' => 'form-control data-field attachment-text',
                                'data-name-template' => 'data[__INDEX__][description]',
                                'placeholder' => 'Optional Description',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="data-field-template" class="hide">
        <div class="form-group data-field-row">
            <div class="row">
                <div class="col-md-4">
                    {!! Form::text('data[__INDEX__][key]', null, ['class' => 'form-control data-key', 'placeholder' => 'Field name (e.g. notes)', 'data-name-template' => 'data[__INDEX__][key]']) !!}
                </div>
                <div class="col-md-7">
                    {!! Form::text('data[__INDEX__][value]', null, ['class' => 'form-control data-value', 'placeholder' => 'Field value', 'data-name-template' => 'data[__INDEX__][value]']) !!}
                </div>
                <div class="col-md-1 d-flex align-items-center">
                    <div class="btn btn-danger btn-sm remove-data-field w-100 p-2">X</div>
                </div>
            </div>
        </div>
    </div>
</fieldset>

<script>
    $(document).ready(function() {
        let nextIndex = {{ $attachments ? $attachments->count() : 0 }};

        $('#attachments .attachment-selectize').selectize({
            render: {
                option: customSelectizeRender,
                item: customSelectizeRender
            }
        });

        function bindAttachmentCard($card) {
            $card.find('.remove-attachment').on('click', function(e) {
                e.preventDefault();
                $(this).closest('.attachment-entry').remove();
            });

            $card.find('.remove-data-field').on('click', function(e) {
                e.preventDefault();
                $(this).closest('.data-field').remove();
            });

            $card.find('.add-data-field').on('click', function(e) {
                e.preventDefault();
                const index = $(this).attr('data-index');
                const $row = $('#data-field-template').children().first().clone();

                const $key = $row.find('.data-key');
                const $value = $row.find('.data-value');
                $key.on('input', function() {
                    const keyName = $(this).val().trim();
                    if (keyName.length) {
                        $value.attr('name', `data[${index}][${keyName}]`);
                    } else {
                        $value.removeAttr('name');
                    }
                });

                $row.find('.remove-data-field').on('click', function(e) {
                    e.preventDefault();
                    $(this).closest('.data-field-row').remove();
                });

                $(this).closest('.attachment-entry').find('.data-fields').append($row);
            });

            $card.find('.attachment-type').on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).closest('.attachment-entry').find('.attachment-select');

                var $clone = cloneAttachmentId(val);

                $cell.html('');
                $cell.append($clone);

                $clone.selectize({
                    render: {
                        option: customSelectizeRender,
                        item: customSelectizeRender
                    }
                });
            });
        }

        $('#attachments .attachment-entry').each(function() {
            bindAttachmentCard($(this));
        });

        $('#add-attachment').on('click', function(e) {
            e.preventDefault();
            let $template = $('#attachment-template').children().first().clone();
            $template.attr('data-index', nextIndex);
            $template.find('.add-data-field').attr('data-index', nextIndex);

            // Replace templated names
            $template.find('[data-name-template]').each(function() {
                const tmpl = $(this).attr('data-name-template');
                $(this).attr('name', tmpl.replace('__INDEX__', nextIndex));
                $(this).removeAttr('data-name-template');
            });

            $('#attachments').append($template);
            bindAttachmentCard($template);
            nextIndex++;
        });

        function cloneAttachmentId(val) {
            return $('#attachmentRowData').find('.' + val.toLowerCase() + '-select').clone();
        }

        function customSelectizeRender(item, escape) {
            item = JSON.parse(item.text);
            option_render = '<div class="option">';
            if (item['image_url']) {
                option_render += '<div class="d-inline mr-1"><img class="small-icon" alt="' + escape(item['name']) + '" src="' + escape(item['image_url']) + '"></div>';
            }
            return option_render += '<span>' + escape(item['name']) + '</span></div>';
        }
    });
</script>
