<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 26/03/19
 * Time: 12:09 م
 */

namespace Shamaseen\Laravel\Ratchet\Traits;

use Shamaseen\Laravel\Ratchet\Objects\Clients\Client;
use Shamaseen\Laravel\Ratchet\Objects\Rooms\Room;

/**
 * ###### Use this trait ONLY in classes extend WebSocketController ######
 * Trait RoomUtility
 * @package Shamaseen\Laravel\Ratchet\Traits
 * @property-read $receiver
 * @property $clients
 * @property $userAuthSocketMapper
 * @property $request
 * @property $conn
 * @property $rooms
 */
trait RoomUtility
{
    function addMember($room_id)
    {
        /** @var Room $room */
        $room = $this->receiver->rooms[$room_id];
        /** @var Client $client */
        $client = $this->receiver->clients[$this->userAuthSocketMapper[\Auth::id()]];
        $room->addMember($client);
        array_push($client->rooms, $room_id);
    }

    /**
     * This function will automatically remove the room if no member still on it.
     * @param $room_id
     */
    function removeMember($room_id)
    {
        /** @var Room $room */
        $room = $this->receiver->rooms[$room_id];
        /** @var Client $client */
        $client = $this->receiver->clients[$this->userAuthSocketMapper[\Auth::id()]];
        $room->removeMember($client);

        unset($client->rooms[$room_id]);
        if (count($room->members) == 0) {
            unset($this->receiver->rooms[$room_id]);
        }

    }

    /**
     * @param int $room_id
     * @param int $user_id
     * @return bool
     */
    function hasMember($room_id, $user_id)
    {
        /** @var Room $room */
        $room = $this->receiver->rooms[$room_id];
        $client = $this->clients[$this->userAuthSocketMapper[$user_id]];
        return $room->hasMember($client);
    }

    /**
     * @param $room_id
     * @param bool $createIfNotExist
     * @return bool|Room
     */
    function validateRoom($room_id, $createIfNotExist = false)
    {
        if (!array_key_exists($room_id, $this->receiver->rooms)) {
            if ($createIfNotExist) {
                $room = $this->receiver->rooms[$room_id] = new Room($room_id);
                return $room;
            }
            $this->error($this->request, $this->conn, 'Room is not exist');
        }
        return true;
    }

    /**
     * @param $room_id
     * @param $message
     */
    function sendToRoom($room_id, $message)
    {
        $this->validateRoom($room_id);
        /** @var Room $room */
        $room = $this->rooms[$room_id];

        if (!$this->hasMember($room_id, \Auth::id())) {
            $this->error($this->request, $this->conn, 'You can\'t send a message to room which you are not in !');
        }

        foreach ($room->members as $member) {
            $this->sendToUser($member->id, $message);
        }
    }
}