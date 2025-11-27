<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller {
    /**
     * Creates or edits an objects rewards.
     *
     * @param App\Services\RewardService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postPopulateRewards(Request $request, RewardService $service) {
        $data = $request->only([
            'object_model', 'object_id',
            'rewardable_recipient', 'rewardable_type', 'rewardable_id', 'quantity',
            'data',
        ]);
        if ($service->populateRewards($data['object_model'], $data['object_id'], $data, Auth::user())) {
            flash('Rewards updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
