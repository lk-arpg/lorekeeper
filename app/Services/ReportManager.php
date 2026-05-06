<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Prompt\Prompt;
use App\Models\Report\Report;
use Illuminate\Support\Facades\DB;

class ReportManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Report Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of report data.
    |
    */

    /**
     * Creates a new report.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param bool                  $isClaim
     *
     * @return mixed
     */
    public function createReport($data, $user, $isClaim = false) {
        DB::beginTransaction();

        try {
            // 1. check that the prompt can be submitted at this time
            // 2. check that the characters selected exist (are visible too)
            // 3. check that the currencies selected can be attached to characters
            if (!Settings::get('is_reports_open')) {
                throw new \Exception('The prompt queue is closed for reports.');
            }
            if (!isset($data['is_br'])) {
                $data['is_br'] = 0;
            }
            // dd($data['error']);

            $report = Report::create([
                'user_id'    => $user->id,
                'url'        => $data['url'],
                'status'     => 'Pending',
                'comments'   => $data['comments'],
                'error_type' => $data['error'],
                'is_br'      => $data['is_br'],
            ]);

            return $this->commitReturn($report);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approves a report.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return mixed
     */
    public function assignReport($data, $user) {
        DB::beginTransaction();

        try {
            $report = Report::where('staff_id', null)->where('id', $data['id'])->first();
            if (!$report) {
                throw new \Exception('This has been assigned an admin');
            }

            $report->update([
                'staff_id' => $user->id,
                'status'   => 'Assigned',
            ]);

            Notifications::create('REPORT_ASSIGNED', $report->user, [
                'staff_url'  => $user->url,
                'staff_name' => $user->name,
                'report_id'  => $report->id,
            ]);

            if (!$this->logAdminAction($user, 'Report Assigned', 'Assigned themselves to report <a href="'.$report->viewurl.'">#'.$report->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($report);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Closes a report.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return mixed
     */
    public function closeReport($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['report'])) {
                $report = Report::where('status', 'Assigned')->where('id', $data['id'])->first();
            } elseif ($data['report']->status == 'Assigned') {
                $report = $data['report'];
            } else {
                $report = null;
            }
            if (!$report) {
                throw new \Exception('Invalid report.');
            }

            if (isset($data['staff_comments']) && $data['staff_comments']) {
                $data['parsed_staff_comments'] = parse($data['staff_comments']);
            } else {
                $data['parsed_staff_comments'] = null;
            }

            $report->update([
                'staff_comments'        => $data['staff_comments'],
                'parsed_staff_comments' => $data['parsed_staff_comments'],
                'staff_id'              => $user->id,
                'status'                => 'Closed',
            ]);

            Notifications::create('REPORT_CLOSED', $report->user, [
                'staff_url'  => $user->url,
                'staff_name' => $user->name,
                'report_id'  => $report->id,
            ]);

            if (!$this->logAdminAction($user, 'Report Closed', 'Closed report <a href="'.$report->viewurl.'">#'.$report->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($report);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
