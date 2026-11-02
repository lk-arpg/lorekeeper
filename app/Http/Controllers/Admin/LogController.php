<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileManager;
use Illuminate\Http\Request;

class LogController extends Controller {
    /**
     * Shows the Logs index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        $logsDirectory = storage_path().'/logs';

        if ($logsDirectory && !file_exists($logsDirectory)) {
            abort(404);
        }
        $logs = scandir($logsDirectory);
        $logList = [];
        foreach ($logs as $log) {
            if (is_file($logsDirectory.'/'.$log) && str_contains($log, '.log')) {
                $logList[] = $log;
            }
        }

        return view('admin.logs.index', [
            'logs' => array_reverse($logList),
        ]);
    }

    /**
     * Shows a specific log.
     *
     * @param string $name
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLog($name) {
        $logsDirectory = storage_path().'/logs';
        if (is_file($logsDirectory.'/'.$name)) {
            $log = file($logsDirectory.'/'.$name, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

            if (str_starts_with($name, 'laravel')) {
                $logLines = $this->parseLaravelLog($log);
            } else {
                $logLines = $this->parseLog($log);
            }

            return view('admin.logs.log', [
                'name' => $name,
                'log'  => array_reverse($logLines),
            ]);
        }
        abort(404);
    }

    /**
     * Deletes a log in the log directory.
     *
     * @param App\Services\FileManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteLog(Request $request, FileManager $service) {
        $request->validate(['filename' => 'required']);
        $name = $request->get('filename');

        if ($service->deleteFile(storage_path().'/logs/'.$name)) {
            flash('Log deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Parse laravel logs to build collapsible stacktraces in the view.
     * This probably won't work with custom formatted logs.
     *
     * @param mixed $log
     */
    private function parseLaravelLog($log) {
        $index = 0;
        $logLines = [];
        $stacktrace = [];
        foreach ($log as $line) {
            $line = trim($line);
            if (str_contains($line, '{main}')) {
                if (count($stacktrace) > 0) {
                    $logLines[$index]['stacktrace'] = $stacktrace;
                    $stacktrace = [];
                    $index += 1;
                }
            } elseif (str_ends_with($line, '"}') && strlen($line) > 2) {
                $logLines[$index]['line'] = $line;
                $index += 1;
            } elseif (str_starts_with($line, '#')) {
                $stacktrace[] = $line;
            } else {
                // some log lines are sorta useless we cut them out.
                if (!str_contains($line, '[stacktrace]') && !str_contains($line, 'Stack trace') && strlen($line) > 2) {
                    $logLines[$index]['line'] = $line;
                }
            }
        }

        return $logLines;
    }

    /**
     * Simply parse all lines without creating stacktraces for collapse views.
     * Should work for any logs.
     *
     * @param mixed $log
     */
    private function parseLog($log) {
        $index = 0;
        $logLines = [];
        foreach ($log as $line) {
            $logLines[$index]['line'] = $line;
            $index += 1;
        }

        return $logLines;
    }
}
