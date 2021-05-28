<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 24/03/19
 * Time: 07:43 م
 */

namespace Shamaseen\Laravel\Ratchet\Controllers;


use Illuminate\Support\Collection;
use Ratchet\ConnectionInterface;
use Shamaseen\Laravel\Ratchet\Objects\Clients\Client;
use Shamaseen\Laravel\Ratchet\Objects\Rooms\Room;
use Shamaseen\Laravel\Ratchet\Receiver;
use Shamaseen\Laravel\Ratchet\Traits\Validation;
use Shamaseen\Laravel\Ratchet\Traits\WebSocketMessagesManager;

/**
 * Class WebSocketController
 * @package App\WebSockets\Controllers
 */
class WebSocketController
{
    use WebSocketMessagesManager,Validation;

    /**
     * @var Client[]
     */
    public $clients;

    /**
     * @var Client
     */
    public $client = null;

    /**
     * Auth to resourceId mapper
     * @var array
     */
    public $userAuthSocketMapper;

    /**
     * Ratchet ConnectionInterface
     * @var ConnectionInterface
     */
    public $conn;

    /**
     * The main receiver of websocket event, here we can change property for all connection.
     * @var Receiver
     */
    public $receiver;

    /**
     * The rooms array
     * @var Room[]
     */
    public $rooms;

    /**
     * @var Collection
     */
    public $request;

    /**
     * @var array
     */
    public $route;

    public function __construct()
    {

    }

    /**
     * @desc return the client class
     * @return Client
     */
    function getClient()
    {
        if($this->client)
            return  $this->client;

        //client is offline
        if(!$this->conn)
            return null;

        $this->client = $this->clients[$this->conn->resourceId];
        return $this->client;
    }

    /**
     * @param string|int $id - laravel Auth id
     */
    function loginAs($id)
    {
        if(isset($this->receiver->userAuthSocketMapper[$id]))
        {
            $this->client = $this->clients[$this->receiver->userAuthSocketMapper[$id]];
            $this->conn = $this->client->conn;
        }

        $user = Receiver::getUserModel()::findOrFail($id);
        \Auth::setUser($user);
    }
}
