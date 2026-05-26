<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttachmentService;
use Illuminate\Http\Request;

class AttachmentController extends Controller {
    /**
     * Creates or edits an objects attachments.
     *
     * @param App\Services\AttachmentService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditAttachments(Request $request, AttachmentService $service) {
        $data = $request->only([
            'parent_model', 'parent_id', 'attachment_type', 'attachment_id', 'data',
        ]);
        if ($service->editAttachments($data['parent_model'], $data['parent_id'], $data)) {
            flash('Attachments updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
