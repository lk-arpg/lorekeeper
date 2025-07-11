<?php

namespace App\Http\Controllers\Users;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Report\Report;
use App\Models\User\User;
use App\Services\ReportManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller {
    /**********************************************************************************************

        REPORTS

    **********************************************************************************************/

    /**
     * Shows the user's report log.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getReportsIndex(Request $request) {
        $reports = Report::where('user_id', Auth::user()->id);
        $type = $request->get('type');
        if (!$type) {
            $type = 'Pending';
        }

        $reports = $reports->where('status', ucfirst($type));

        return view('home.reports', [
            'reports' => $reports->orderBy('id', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the bug report log.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBugIndex(Request $request) {
        $reports = Report::where('is_br', 1);

        $data = $request->only(['url']);

        if (isset($data['url'])) {
            $reports->where('url', 'LIKE', '%'.$data['url'].'%');
        }

        return view('home.bug_report_index', [
            'reports' => $reports->orderBy('id', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the report page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getReport($id) {
        $report = Report::viewable(Auth::user() ?? null)->where('id', $id)->first();
        if (!$report) {
            abort(404);
        }

        return view('home.report', [
            'report' => $report,
            'user'   => $report->user,
        ]);
    }

    /**
     * Shows the submit report page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNewReport(Request $request) {
        $closed = !Settings::get('is_reports_open');

        return view('home.create_report', [
            'closed' => $closed,
        ]);
    }

    /**
     * Creates a new report.
     *
     * @param App\Services\ReportManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNewReport(Request $request, ReportManager $service) {
        $request->validate(Report::$createRules);
        $request['url'] = strip_tags($request['url']);

        if ($service->createReport($request->only(['url', 'comments', 'is_br', 'error']), Auth::user(), true)) {
            flash('Report submitted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('reports');
    }
}
