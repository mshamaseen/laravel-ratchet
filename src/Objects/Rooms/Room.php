<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 26/03/19
 * Time: 10:46 ص
 */

namespace Shamaseen\Laravel\Ratchet\Objects\Rooms;

use Shamaseen\Laravel\Ratchet\Objects\Clients\Client;

class Room
{

    public $id;

    /**
     * @var Client[]
     */
    public $members = [];


    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param Client $client
     */
    function addMember($client)
    {
        $this->members[$client->id] = $client;
        array_push($client->rooms, $this->id);
    }

    /**
     * @param Client $client
     */
    function removeMember($client)
    {
        unset($this->members[$client->id]);
        unset($client->rooms[$this->id]);
    }

    /**
     * @param $client
     * @return bool
     */
    function hasMember($client)
    {
        if(array_key_exists($client->id,$this->members))
            return true;

        return false;
    }
}
