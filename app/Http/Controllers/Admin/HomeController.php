<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Character\CharacterTransfer;
use App\Models\Currency\Currency;
use App\Models\Gallery\GallerySubmission;
use App\Models\Report\Report;
use App\Models\Submission\Submission;
use App\Models\Trade\Trade;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller {
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        $openTransfersQueue = Settings::get('open_transfers_queue');
        $openTradesQueue = Settings::get('open_trades_queue');
        $galleryRequireApproval = Settings::get('gallery_submissions_require_approval');
        $galleryCurrencyAwards = Settings::get('gallery_submissions_reward_currency');

        return view('admin.index', [
            'submissionCount'        => Submission::where('status', 'Pending')->whereNotNull('prompt_id')->count(),
            'claimCount'             => Submission::where('status', 'Pending')->whereNull('prompt_id')->count(),
            'designCount'            => CharacterDesignUpdate::characters()->where('status', 'Pending')->count(),
            'myoCount'               => CharacterDesignUpdate::myos()->where('status', 'Pending')->count(),
            'reportCount'            => Report::where('status', 'Pending')->count(),
            'assignedReportCount'    => Report::assignedToMe(Auth::user())->count(),
            'openTransfersQueue'     => $openTransfersQueue,
            'openTradesQueue'        => $openTradesQueue,
            'transferCount'          => $openTransfersQueue ? CharacterTransfer::active()->where('is_approved', 0)->count() : 0,
            'tradeCount'             => $openTransfersQueue ? Trade::where('status', 'Pending')->count() : 0,
            'galleryRequireApproval' => $galleryRequireApproval,
            'galleryCurrencyAwards'  => $galleryCurrencyAwards,
            'gallerySubmissionCount' => GallerySubmission::collaboratorApproved()->where('status', 'Pending')->count(),
            'galleryAwardCount'      => GallerySubmission::requiresAward()->where('is_valued', 0)->count(),
        ]);
    }

    /**
     * Show admin logs.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLogs(Request $request) {
        // get all staff users so we can search by them
        // have to check rank relation
        $staff = User::whereHas('rank', function ($query) {
            // check rank id = 1 OR the rank has existing relation powers
            $query->where('id', 1)->orWhereHas('powers');
        })->get()->pluck('name', 'id');

        $query = AdminLog::query();

        $data = $request->only(['user_id', 'action']);
        if (isset($data['user_id']) && $data['user_id'] != '') {
            $query->where('user_id', $data['user_id']);
        }
        if (isset($data['action']) && $data['action'] != '') {
            $query->where('action', $data['action']);
        }

        $query->orderBy('created_at', 'DESC');

        return view('admin.logs', [
            'logs'    => $query->paginate(20)->appends($request->query()),
            'staff'   => $staff,
            'actions' => AdminLog::pluck('action', 'action')->unique(),
        ]);
    }

    /**
     * Shows the staff reward settings index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStaffRewardSettings() {
        return view('admin.staff_reward_settings', [
            'currency' => Currency::find(config('lorekeeper.extensions.staff_rewards.currency_id')),
            'settings' => DB::table('staff_actions')->orderBy('key')->paginate(20),
        ]);
    }

    /**
     * Edits a staff reward setting.
     *
     * @param string $key
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditStaffRewardSetting(Request $request, $key) {
        if (DB::table('staff_actions')->where('key', $key)->update(['value' => $request->get('value')])) {
            flash('Setting updated successfully.')->success();
        } else {
            flash('Invalid setting selected.')->success();
        }

        return redirect()->back();
    }
}
