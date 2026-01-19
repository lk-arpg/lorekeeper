<?php
namespace App\Services\Queue;

use App\Models\Queue\QueueLog;
use App\Services\Service;
use Auth;
use DB;

class VanillaService extends Service
{

    /**
     * Retrieves any data that should be used in the queue type editing form on the admin side
     *
     * @return array
     */
    public function getEditData()
    {

        return [

        ];
    }

    /**
     * Retrieves any data that should be used in the queue type on the user side
     *
     * @return array
     */
    public function getActData($queue)
    {
        return [
        ];
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getData($data)
    {
        return $data;
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param  object  $queue
     * @param  array   $data
     * @return bool
     */
    public function updateData($queue, $data)
    {


        return [
        ];
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function submit($queue, $data, $user)
    {
        DB::beginTransaction();

        try {

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
