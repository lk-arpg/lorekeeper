<tr>
    <td>{!! $submission->queue->displayName !!}</td>
    <td class="text-break"><a href="{{ $submission->url }}">{{ $submission->url }}</a></td>
    <td>{!! format_date($submission->created_at) !!}</td>
    <td>
        <span class="badge badge-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Approved' ? 'success' : 'danger') }}">{{ $submission->status }}</span>
    </td>
    <td class="text-right"><a href="{{ $submission->viewUrl }}" class="btn btn-primary btn-sm">Details</a></td>
</tr>
