<?php

namespace App\Services;

use App;
use App\Models\AdminLog;
use App\Models\Currency\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\MessageBag;

abstract class Service {
    /*
    |--------------------------------------------------------------------------
    | Base Service
    |--------------------------------------------------------------------------
    |
    | Base service, setting up error handling.
    |
    */

    /**
     * Errors.
     *
     * @var Illuminate\Support\MessageBag
     */
    protected $errors = null;
    protected $cache = [];
    protected $user = null;

    /**
     * Default constructor.
     */
    public function __construct() {
        $this->callMethod('beforeConstruct');
        $this->resetErrors();
        $this->callMethod('afterConstruct');
    }

    /**
     * Return if an error exists.
     *
     * @return bool
     */
    public function hasErrors() {
        return $this->errors->count() > 0;
    }

    /**
     * Return if an error exists.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function hasError($key) {
        return $this->errors->has($key);
    }

    /**
     * Return errors.
     *
     * @return Illuminate\Support\MessageBag
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Return errors.
     *
     * @return array
     */
    public function getAllErrors() {
        return $this->errors->unique();
    }

    /**
     * Return error by key.
     *
     * @param mixed $key
     *
     * @return Illuminate\Support\MessageBag
     */
    public function getError($key) {
        return $this->errors->get($key);
    }

    /**
     * Empty the errors MessageBag.
     */
    public function resetErrors() {
        $this->errors = new MessageBag;
    }

    public function remember($key = null, $fn = null) {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        return $this->cache[$key] = $fn();
    }

    public function forget($key = null) {
        unset($this->cache[$key]);
    }

    public function setUser($user) {
        $this->user = $user;

        return $this;
    }

    public function user() {
        return $this->user ? $this->user : Auth::user();
    }

    // 1. Old image exists, want to move it to a new location.
    // 2. Given new image, want to upload it to new location.
    //    (old image may or may not exist)
    // 3. Nothing happens (no changes required)
    public function handleImage($image, $dir, $name, $oldName = null, $copy = false) {
        if (!$oldName && !$image) {
            return true;
        }

        if (!$image) {
            // Check if we're moving an old image, and move it if it does.
            if ($oldName) {
                return $this->moveImage($dir, $name, $oldName, $copy);
            }
        } else {
            // Don't want to leave a lot of random images lying around,
            // so move the old image first if it exists.
            if ($oldName) {
                $this->moveImage($dir, $name, $oldName, $copy);
            }

            // Then overwrite the old image.
            return $this->saveImage($image, $dir, $name, $copy);
        }

        return false;
    }

    public function deleteImage($dir, $name) {
        unlink($dir.'/'.$name);
    }

    /**
     * Creates an admin log entry after an action is performed.
     * If staff rewards are enabled, also checks for and grants any
     * applicable rewards.
     *
     * @param string $action
     * @param object $user
     * @param string $action
     * @param mixed  $action_details
     */
    public function logAdminAction($user, $action, $action_details) {
        // Double-check that the user is staff
        if ($user->isStaff) {
            // If staff rewards are enabled, check if the action
            // is eligible for a reward, and if so, grant it
            if (config('lorekeeper.extensions.staff_rewards.enabled')) {
                // Ensure that the user only receives rewards for the action once
                if (!AdminLog::where('user_id', $user->id)->where('action', $action)->where('action_details', $action_details)->exists()) {
                    // Fetch all configured actions
                    $actions = [];
                    foreach (glob('../config/lorekeeper/staff-reward-actions/*.php') as $a) {
                        $actions[basename($a, '.php')] = include $a;
                    }

                    // Cycle through and locate any keyed actions which
                    // correspond to the action currently being logged
                    $keyedActions = [];
                    foreach ($actions as $key=> $a) {
                        foreach ($a['actions'] as $act) {
                            if ($act == $action) {
                                $keyedActions[] = $key;
                            }
                        }
                    }

                    // Collect the configured reward(s) for performing
                    // this action
                    $reward = 0;
                    foreach ($keyedActions as $a) {
                        if (DB::table('staff_actions')->where('key', $a)->exists()) {
                            $reward += DB::table('staff_actions')->where('key', $a)->first()->value;
                        } else {
                            // If not configured, just supply 1
                            $reward += 1;
                        }
                    }

                    // Grant the calculated reward to the user
                    if ($reward) {
                        // Check that the currency exists, first
                        $currency = Currency::find(config('lorekeeper.extensions.staff_rewards.currency_id'));
                        if ($currency) {
                            if (!(new CurrencyManager)->creditCurrency(null, $user, 'Staff Reward', $action_details, $currency, $reward)) {
                                return false;
                            }
                        }
                    }
                }
            }

            $log = AdminLog::create([
                'user_id'        => $user->id,
                'action'         => $action,
                'action_details' => $action_details,
            ]);

            if ($log) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Calls a service method and injects the required dependencies.
     *
     * @param string $methodName
     *
     * @return mixed
     */
    protected function callMethod($methodName) {
        if (method_exists($this, $methodName)) {
            return App::call([$this, $methodName]);
        }
    }

    /**
     * Add an error to the MessageBag.
     *
     * @param string $key
     * @param string $value
     */
    protected function setError($key, $value) {
        $this->errors->add($key, $value);
    }

    /**
     * Add multiple errors to the message bag.
     *
     * @param Illuminate\Support\MessageBag $errors
     */
    protected function setErrors($errors) {
        $this->errors->merge($errors);
    }

    /**
     * Commits the current DB transaction and returns a value.
     *
     * @param mixed $return
     *
     * @return mixed $return
     */
    protected function commitReturn($return = true) {
        DB::commit();

        return $return;
    }

    /**
     * Rolls back the current DB transaction and returns a value.
     *
     * @param mixed $return
     *
     * @return mixed $return
     */
    protected function rollbackReturn($return = false) {
        DB::rollback();

        return $return;
    }

    /**
     * Returns the current field if it is numeric, otherwise searches for a field if it is an array or object.
     *
     * @param mixed  $data
     * @param string $field
     *
     * @return mixed
     */
    protected function getNumeric($data, $field = 'id') {
        if (is_numeric($data)) {
            return $data;
        } elseif (is_object($data)) {
            return $data->$field;
        } elseif (is_array($data)) {
            return $data[$field];
        } else {
            return 0;
        }
    }

    // Moves an old image within the same directory.
    private function moveImage($dir, $name, $oldName, $copy = false) {
        if ($copy) {
            File::copy($dir.'/'.$oldName, $dir.'/'.$name);
        } else {
            File::move($dir.'/'.$oldName, $dir.'/'.$name);
        }

        return true;
    }

    // Moves an uploaded image into a directory, checking if it exists.
    private function saveImage($image, $dir, $name, $copy = false) {
        if (!file_exists($dir)) {
            // Create the directory.
            if (!mkdir($dir, 0755, true)) {
                $this->setError('error', 'Failed to create image directory.');

                return false;
            }
            chmod($dir, 0755);
        }
        if ($copy) {
            File::copy($image, $dir.'/'.$name);
        } else {
            File::move($image, $dir.'/'.$name);
        }
        chmod($dir.'/'.$name, 0755);

        return true;
    }
}
