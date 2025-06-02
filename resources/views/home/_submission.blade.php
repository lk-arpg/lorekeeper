<tr>
    @if ($submission->prompt_id)
        <td>{!! $submission->prompt->displayName !!}</td>
    @endif
    <td class="text-break"><a href="{{ $submission->url }}">{{ $submission->url }}</a></td>
    <td>{!! format_date($submission->created_at) !!}</td>
    <td>
        <span class="badge badge-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Approved' ? 'success' : 'danger') }}">{{ $submission->status }}</span>
    </td>
    <td class="text-end"><a href="{{ $submission->viewUrl }}" class="btn btn-primary btn-sm">Details</a></td>
</tr>
