<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 26/03/19
 * Time: 12:09 م
 */

namespace Shamaseen\Laravel\Ratchet\Traits;

use Auth;
use Illuminate\Support\Collection;
use Shamaseen\Laravel\Ratchet\Objects\Clients\Client;
use Shamaseen\Laravel\Ratchet\Objects\Rooms\Room;
use Shamaseen\Laravel\Ratchet\Receiver;

/**
 * ###### Use this trait ONLY in classes extend WebSocketController ######
 * Trait RoomUtility
 * @package Shamaseen\Laravel\Ratchet\Traits
 * @property-read Receiver $receiver
 * @property $clients
 * @property $userAuthSocketMapper
 * @property Collection $request
 * @property $conn
 */
trait RoomUtility
{
    /**
     * @param $room_id
     * @return Room
     */
    function createRoom($room_id)
    {
        $this->receiver->rooms[$room_id] = new Room($room_id);
        return $this->receiver->rooms[$room_id];
    }

    function addMember($room_id)
    {
        /** @var Room $room */
        $room = $this->receiver->rooms[$room_id];
        /** @var Client $client */
        $client = $this->receiver->clients[$this->userAuthSocketMapper[Auth::id()]];
        $room->addMember($client);

        return $this;
    }

    /**
     * This function will automatically remove the room if no member still on it.
     * @param $room_id
     * @param bool $removeRoomIfEmpty
     * @param null $client
     * @return RoomUtility
     */
    function removeMember($room_id,$client = null,$removeRoomIfEmpty = false)
    {
        /** @var Room $room */
        $room = $this->receiver->rooms[$room_id];
        /** @var Client $client */
        $client = $client ?? $this->receiver->clients[$this->userAuthSocketMapper[Auth::id()]];
        $room->removeMember($client);

        if($removeRoomIfEmpty)
        {
            if (count($room->members) == 0) {
                unset($this->receiver->rooms[$room_id]);
            }
        }

        return $this;
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
                return $this->createRoom($room_id);
            }
            $this->error($this->request, $this->conn, 'Room is not exist');
            return false;
        }
        return true;
    }

    /**
     * @param $room_id
     * @param array $message
     */
    function sendToRoom($room_id, $message)
    {
        $this->validateRoom($room_id);
        /** @var Room $room */
        $room = $this->receiver->rooms[$room_id];

        foreach ($room->members as $member) {
            $this->sendToUser($member->id, $message);
        }
    }
}
