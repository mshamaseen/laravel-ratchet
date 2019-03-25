<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 23/03/19
 * Time: 07:40 م
 */

namespace Shamaseen\Laravel\Ratchet;


use function Composer\Autoload\includeFile;
use Shamaseen\Laravel\Ratchet\Exceptions\WebSocketException;
use Shamaseen\Laravel\Ratchet\Facades\WsRoute;
use Shamaseen\Laravel\Ratchet\Objects\Clients\Client;
use Shamaseen\Laravel\Ratchet\Routes\Routes;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Shamaseen\Laravel\Ratchet\Traits\WebSocketMessagesManager;

/**
 * Class WebSocket
 * @package App\WebSockets
 */
class Receiver implements MessageComponentInterface
{
    use WebSocketMessagesManager;

    /**
     * @var Client[]
     */
    public $clients;
    private $routes;
    public $userAuthSocketMapper;

    /**
     * WebSocket constructor.
     */
    public function __construct()
    {
        $this->clients = [];
        $this->userAuthSocketMapper = [];
        WsRoute::mainRoutes();

        include base_path().'/routes/websocket.php';

        $this->routes = WsRoute::getRoutes();
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn) {

        $this->clients[$conn->resourceId] = new Client();
        $this->clients[$conn->resourceId]->conn = $conn;
//        $this->clients[$conn->resourceId]->resourceId = $conn->resourceId;

        echo "New connection! ({$conn->resourceId})\n";
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        try
        {
            $msg = json_decode($msg);

            $this->checkForRequiredInMessage($msg,$from);

            \Session::setId($msg->session);

            \Session::start();

            if($this->routes[$msg->route]->auth && !\Auth::check())
                $this->error($msg,$from,'Unauthenticated.');
            else
                $this->userAuthSocketMapper[\Auth::id()] = $from->resourceId;

            $class = $this->routes[$msg->route]->controller;
            $method = $this->routes[$msg->route]->method;
            $controller = new $class;

            $this->cloneProperties($this,$controller);

            $controller->conn = $from;

            if(!method_exists($controller,$method))
            {
                $this->error($msg,$from,'Method doesnt\'t exist !');
            }

            $controller->$method($msg);
        }
        catch (WebSocketException $exception)
        {

        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        unset($this->clients[$conn]);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
        echo 'end';
        die;
    }

    /**
     * @param $msg
     * @param $from
     * @throws WebSocketException
     */
    function checkForRequiredInMessage($msg,$from)
    {
        if(!isset($msg->route) || !isset($msg->session))
        {
            $this->error($msg,$from,'You can\'t send a request without the route and the session id !');
        }

        if(!isset($this->routes[$msg->route]))
            $this->error($msg,$from,'No such route !');
    }

    /**
     * @param $clonedObject
     * @param $clone
     */
    function cloneProperties($clonedObject, $clone)
    {
        foreach (get_object_vars($clonedObject) as $key => $value) {
            $clone->$key = $value;
        }
    }
}