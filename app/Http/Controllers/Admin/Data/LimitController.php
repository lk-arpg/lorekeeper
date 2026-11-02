<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Limit\DynamicLimit;
use App\Services\LimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LimitController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Limit Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of limits.
    |
    */

    /**
     * Shows the limit index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.limits.limits', [
            'limits' => DynamicLimit::get(),
        ]);
    }

    /**
     * Shows the create limit page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateLimit() {
        return view('admin.limits.create_edit_limit', [
            'limit' => new DynamicLimit,
        ]);
    }

    /**
     * Shows the edit limit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLimit($id) {
        $limit = DynamicLimit::find($id);
        if (!$limit) {
            abort(404);
        }

        return view('admin.limits.create_edit_limit', [
            'limit' => $limit,
        ]);
    }

    /**
     * Creates or edits a limit.
     *
     * @param App\Services\LimitService $service
     * @param int|null                  $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditLimit(Request $request, LimitService $service, $id = null) {
        $data = $request->only([
            'name', 'description', 'evaluation',
        ]);
        if ($id && $service->updateLimit(DynamicLimit::find($id), $data, Auth::user())) {
            flash('Limit updated successfully.')->success();
        } elseif (!$id && $limit = $service->createLimit($data, Auth::user())) {
            flash('Limit created successfully.')->success();

            return redirect()->to('admin/data/limits/edit/'.$limit->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the limit deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteLimit($id) {
        $limit = DynamicLimit::find($id);

        return view('admin.limits._delete_limit', [
            'limit' => $limit,
        ]);
    }

    /**
     * Deletes a limit.
     *
     * @param App\Services\LimitService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteLimit(Request $request, LimitService $service, $id) {
        if ($id && $service->deleteLimit(DynamicLimit::find($id))) {
            flash('Limit deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/limits');
    }

    /**
     * Sorts limits.
     *
     * @param App\Services\LimitService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortLimit(Request $request, LimitService $service) {
        if ($service->sortLimit($request->get('sort'))) {
            flash('Limit order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
