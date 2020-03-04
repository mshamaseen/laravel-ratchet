<?php
/**
 * Created by PhpStorm.
 * User: hammo
 * Date: 11/13/2018
 * Time: 11:22 AM
 */

namespace Shamaseen\Laravel\Ratchet\Objects\Clients;


use Ratchet\ConnectionInterface;
use Shamaseen\Laravel\Ratchet\Traits\WebSocketMessagesManager;

/**
 * this class represent ratchet client
 * Class Client
 * @package App\WebSockets\Clients
 */
class Client
{

    use WebSocketMessagesManager;
    /**
     * this id is the laravel auth id
     * @var null|int $id
     */
    public $id = null;

    /** @var ConnectionInterface $conn */
    public $conn = null;

    /**
     * @var array
     */
    public $rooms = [];

    public $onCloseRoutes = [];

    public $session = null;

    /**
     * @description A custom attributes which the user can use to add attributes about this client, it work only until the client close his connection
     * @var array
     */
    public $customAttributes = [];

    /**
     * @param string $route
     * @return Client
     */
    function onClose(string $route)
    {
       array_push($this->onCloseRoutes,$route);

        return $this;
    }

    function removeOnCloseRoute(string $route)
    {
        if (($key = array_search($route, $this->onCloseRoutes)) !== false) {
            unset($this->onCloseRoutes[$key]);
        }
    }
}
