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
     * @throws \Illuminate\Validation\ValidationException
     */
    function enterRoom()
    {
        $this->validate($this->request,[
            'room_id'=>'required'
        ]);
        $room_id =$this->request->room_id;

        $this->validateRoom($room_id,true);

        $this->addMember($room_id);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    function exitRoom()
    {

        $this->validate($this->request,[
            'room_id'=>'required'
        ]);
        $room_id =$this->request->room_id;

        $this->validateRoom($room_id);

        $this->removeMember($room_id);
    }
}