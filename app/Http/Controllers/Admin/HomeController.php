<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Settings;
use Auth;

use App\Models\Submission\Submission;
use App\Models\Gallery\GallerySubmission;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Character\CharacterTransfer;
use App\Models\Trade;
use App\Models\Report\Report;

use App\Http\Controllers\Controller;
use App\Models\Queue\QueueSubmission;

class HomeController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        $openTransfersQueue = Settings::get('open_transfers_queue');
        $galleryRequireApproval = Settings::get('gallery_submissions_require_approval');
        $galleryCurrencyAwards = Settings::get('gallery_submissions_reward_currency');
        return view('admin.index', [
            'submissionCount' => Submission::where('status', 'Pending')->whereNotNull('prompt_id')->count(),
            'claimCount' => Submission::where('status', 'Pending')->whereNull('prompt_id')->count(),
            'designCount' => CharacterDesignUpdate::characters()->where('status', 'Pending')->count(),
            'myoCount' => CharacterDesignUpdate::myos()->where('status', 'Pending')->count(),
            'reportCount' => Report::where('status', 'Pending')->count(),
            'assignedReportCount' => Report::assignedToMe(Auth::user())->count(),
            'openTransfersQueue' => $openTransfersQueue,
            'transferCount' => $openTransfersQueue ? CharacterTransfer::active()->where('is_approved', 0)->count() : 0,
            'tradeCount' => $openTransfersQueue ? Trade::where('status', 'Pending')->count() : 0,
            'galleryRequireApproval' => $galleryRequireApproval,
            'galleryCurrencyAwards' => $galleryCurrencyAwards,
            'gallerySubmissionCount' => GallerySubmission::collaboratorApproved()->where('status', 'Pending')->count(),
            'galleryAwardCount' => GallerySubmission::requiresAward()->where('is_valued', 0)->count(),
            'queueCount'        => QueueSubmission::where('status', 'Pending')->whereNotNull('queue_id')->count(),
        ]);
    }
}
