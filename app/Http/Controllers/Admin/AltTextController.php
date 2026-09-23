<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AltTextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AltTextController extends Controller {
    /**
     * Creates or edits an object's alt text.
     *
     * @param App\Services\AltTextService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditAltText(Request $request, AltTextService $service) {
        $data = $request->only([
            'object_model', 'object_id', 'alt_text', 'text_key',
        ]);
        if ($service->createEditAltText($data['object_model'], $data['object_id'], $data, Auth::user())) {
            flash('Alt text updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
