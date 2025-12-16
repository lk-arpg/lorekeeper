<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller {

    /**
     * Gets reward type dropdown select based on input
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postRewardTypes(Request $request) {
        $data = $request->only('recipient', 'prefix', 'type', 'showData');
        $isCharacter = $data['recipient'] == 'Character' ? true : false;

        return view('widgets._loot_reward_types', [
            'prefix' => $data['prefix'],
            'type' => $data['type'],
            'rewardTypes' => getRewardTypes($data['showData'], $isCharacter),
        ]);
    }
}