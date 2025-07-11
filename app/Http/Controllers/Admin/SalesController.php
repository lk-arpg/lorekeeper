<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Sales\Sales;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller {
    /**
     * Shows the Sales index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.sales.sales', [
            'saleses' => Sales::orderBy('post_at', 'DESC')->paginate(20),
        ]);
    }

    /**
     * Shows the create Sales page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateSales() {
        return view('admin.sales.create_edit_sales', [
            'sales' => new Sales,
        ]);
    }

    /**
     * Shows the edit Sales page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditSales($id) {
        $sales = Sales::find($id);
        if (!$sales) {
            abort(404);
        }

        return view('admin.sales.create_edit_sales', [
            'sales' => $sales,
        ]);
    }

    /**
     * Shows character information.
     *
     * @param string $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterInfo($slug) {
        $character = Character::where('slug', $slug)->first();

        return view('home._character', [
            'character' => $character,
        ]);
    }

    /**
     * Creates or edits a Sales page.
     *
     * @param App\Services\SalesService $service
     * @param int|null                  $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditSales(Request $request, SalesService $service, $id = null) {
        $id ? $request->validate(Sales::$updateRules) : $request->validate(Sales::$createRules);
        $data = $request->only([
            'title', 'text', 'post_at', 'is_visible', 'bump', 'is_open', 'comments_open_at', 'image', 'remove_image',
            // Character information
            'slug', 'sale_type', 'price', 'starting_bid', 'min_increment', 'autobuy', 'end_point', 'minimum', 'description', 'link', 'character_is_open', 'new_entry', 'image_id',
        ]);
        if ($id && $service->updateSales(Sales::find($id), $data, Auth::user())) {
            flash('Sales updated successfully.')->success();
        } elseif (!$id && $sales = $service->createSales($data, Auth::user())) {
            flash('Sales created successfully.')->success();

            return redirect()->to('admin/sales/edit/'.$sales->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the Sales deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteSales($id) {
        $sales = Sales::find($id);

        return view('admin.sales._delete_sales', [
            'sales' => $sales,
        ]);
    }

    /**
     * Deletes a Sales page.
     *
     * @param App\Services\SalesService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteSales(Request $request, SalesService $service, $id) {
        if ($id && $service->deleteSales(Sales::find($id))) {
            flash('Sales deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/sales');
    }
}
