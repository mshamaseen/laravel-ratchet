<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 26/03/19
 * Time: 10:57 ص
 */

namespace Shamaseen\Laravel\Ratchet\Controllers;


use Shamaseen\Laravel\Ratchet\Traits\RoomUtility;

class RoomController extends WebSocketController
{
    use RoomUtility;

    /**
     * @param $room_id
     */
    function enterRoom($room_id)
    {
        $this->checkForRoom($room_id,true);

        $this->addMember($room_id);
    }

    /**
     * @param $room_id
     */
    function exitRoom($room_id)
    {
        $this->checkForRoom($room_id);

        $this->removeMember($room_id);
    }
}