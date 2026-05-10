<?php

namespace App\Services;

use App;
use App\Models\AdminLog;
use App\Models\Currency\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\MessageBag;
use Intervention\Image\ImageManagerStatic as Image;

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

    /**********************************************************************************************

        PUBLIC IMAGE HANDLING METHODS

    **********************************************************************************************/

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
                $this->deleteImage($dir, $oldName);
            }

            // Then save the new image.
            return $this->saveImage($image, $dir, $name, $copy);
        }

        return false;
    }

    /**
     * Delete an image file.
     *
     * @param string $dir
     * @param string $name
     */
    public function deleteImage($dir, $name) {
        if (empty($name) || empty($dir)) {
            return false;
        }

        $fullPath = $dir.'/'.$name;

        if (file_exists($fullPath)) {
            try {
                unlink($fullPath);

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Apply watermark to an image.
     *
     * @param mixed  $image            Image instance from Intervention Image
     * @param string $watermarkPath    Path to watermark image file
     * @param bool   $resizeWatermark  Whether to resize watermark
     * @param float  $watermarkPercent Percentage of image to use for watermark size
     *
     * @return mixed Image instance with watermark applied
     */
    public function applyWatermark($image, $watermarkPath = 'images/watermark.png', $resizeWatermark = false, $watermarkPercent = 0.9) {
        if (!file_exists($watermarkPath)) { // stop silently failing if watermark file doesn't exist
            return $image;
        }

        try {
            // ensure matching drivers for watermark and image to prevent errors
            $this->configureImageDriver($image->basePath());
            $watermark = Image::make($watermarkPath);

            if ($resizeWatermark) {
                $imageWidth = $image->width();
                $imageHeight = $image->height();
                $wmWidth = $watermark->width();
                $wmHeight = $watermark->height();

                // calculate max size based on image dimensions and watermark percent
                $maxSize = max($imageWidth, $imageHeight) * $watermarkPercent;

                if ($wmWidth > $wmHeight) {
                    // watermark is landscape
                    $watermark->resize($maxSize, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                } else {
                    // watermark is portrait
                    $watermark->resize(null, $maxSize, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }
            }

            $image->insert($watermark, 'center');

            return $image;
        } catch (\Throwable $e) {
            return $image; // Return unmodified image on error
        }
    }

    /**
     * Configure image driver for large images.
     *
     * @param string $imagePath Path to image file
     */
    public function configureImageDriver($imagePath) {
        try {
            $imageProperties = getimagesize($imagePath);
            if ($imageProperties && ($imageProperties[0] > 2000 || $imageProperties[1] > 2000)) {
                Config::set('image.driver', 'imagick');
            }
        } catch (\Exception $e) {
            // continue with default driver on error
        }
    }

    /**
     * Add background fill to image if it doesn't support transparency.
     *
     * @param mixed  $image           Image instance
     * @param string $backgroundColor Hex color code
     *
     * @return mixed Image with background
     */
    public function addImageBackground($image, $backgroundColor) {
        if (!$backgroundColor) {
            return $image;
        }

        try {
            $canvas = Image::canvas($image->width(), $image->height(), $backgroundColor);
            $image = $canvas->insert($image, 'center');

            return $image;
        } catch (\Exception $e) {
            return $image; // return unmodified image on error
        }
    }

    /**
     * Resize image with aspect ratio constraints.
     *
     * @param mixed  $image        Image instance from Intervention Image
     * @param int    $maxDimension Maximum dimension (width or height)
     * @param string $target       'shorter' or 'longer' - which dimension to target
     * @param bool   $upsize       Whether to upsize smaller images
     *
     * @return mixed Image instance with applied resizing
     */
    public function resizeImage($image, $maxDimension, $target = 'shorter', $upsize = false) {
        if ($maxDimension <= 0) {
            return $image;
        }

        try {
            $imageWidth = $image->width();
            $imageHeight = $image->height();

            $isLandscape = $imageWidth > $imageHeight;

            // decide whether width should be constrained based on target and image orientation
            $constrainWidth = ($target == 'shorter') ? !$isLandscape : $isLandscape;

            $width = $constrainWidth ? $maxDimension : null;
            $height = $constrainWidth ? null : $maxDimension;

            $image->resize($width, $height, function ($constraint) use ($upsize) {
                $constraint->aspectRatio();
                if ($upsize) {
                    $constraint->upsize();
                }
            });

            return $image;
        } catch (\Exception $e) {
            return $image; // Return unmodified image on error
        }
    }

    /**
     * Makes an image square by applying a background fill to the shorter dimension.
     *
     * @param mixed $image Image instance from Intervention Image
     *
     * @return mixed Image instance with applied resizing
     */
    public function makeImageSquare($image) {
        try {
            $imageWidth = $image->width();
            $imageHeight = $image->height();

            if ($imageWidth > $imageHeight) {
                // Landscape
                $canvas = Image::canvas($image->width(), $image->width());
                $image = $canvas->insert($image, 'center');
            } else {
                // Portrait
                $canvas = Image::canvas($image->height(), $image->height());
                $image = $canvas->insert($image, 'center');
            }

            return $image;
        } catch (\Exception $e) {
            return $image; // return unmodified image on error
        }
    }

    /**********************************************************************************************

        END PUBLIC IMAGE HANDLING METHODS

    **********************************************************************************************/

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

    /**********************************************************************************************

        PRIVATE IMAGE HANDLING METHODS

    **********************************************************************************************/

    /**
     * Moves an old image within the same directory.
     *
     * @param string $dir
     * @param string $newName
     * @param string $oldName
     * @param bool   $copy
     *
     * @return bool
     */
    private function moveImage($dir, $newName, $oldName, $copy = false) {
        $oldPath = $dir.'/'.$oldName;
        $newPath = $dir.'/'.$newName;

        if (!file_exists($oldPath)) {
            return false;
        }

        try {
            if ($copy) {
                File::copy($oldPath, $newPath);
            } else {
                File::move($oldPath, $newPath);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Moves an uploaded image into a directory, checking if it exists.
     *
     * @param mixed  $image
     * @param string $dir
     * @param string $name
     * @param bool   $copy
     *
     * @return bool
     */
    private function saveImage($image, $dir, $name, $copy = false) {
        if (!file_exists($dir)) {
            // Create the directory.
            if (!mkdir($dir, 0755, true)) {
                $this->setError('error', 'Failed to create image directory.');

                return false;
            }
            chmod($dir, 0755);
        }

        try {
            if ($copy) {
                File::copy($image, $dir.'/'.$name);
            } else {
                File::move($image, $dir.'/'.$name);
            }
            chmod($dir.'/'.$name, 0755);

            return true;
        } catch (\Exception $e) {
            $this->setError('error', 'Failed to save image: '.$e->getMessage());

            return false;
        }
    }
}
