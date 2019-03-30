<?php
/** @noinspection PhpUndefinedFieldInspection */

/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 23/03/19
 * Time: 07:40 م
 */

namespace Shamaseen\Laravel\Ratchet;

use Illuminate\Validation\ValidationException;
use Shamaseen\Laravel\Ratchet\Exceptions\WebSocketException;
use Shamaseen\Laravel\Ratchet\Facades\WsRoute;
use Shamaseen\Laravel\Ratchet\Objects\Clients\Client;
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
    public $rooms = [];

    /**
     * WebSocket constructor.
     */
    public function __construct()
    {
        $this->clients = [];
        /**
         * The key will be auth id, the value will be resourceId
         */
        $this->userAuthSocketMapper = [];

        $this->mainRoutes();
        include base_path() . '/routes/websocket.php';
        $this->routes = WsRoute::getRoutes();
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {

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
    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $msg = json_decode($msg);

            $this->checkForRequiredInMessage($msg, $from);

            \Session::setId($msg->session);

            \Session::start();

            $route = $this->routes[$msg->route];
            if ($route->auth && !\Auth::check()) {
                $this->error($msg, $from, 'Unauthenticated.');
            } else {
                $this->clients[$from->resourceId]->id = \Auth::id();
                $this->userAuthSocketMapper[\Auth::id()] = $from->resourceId;
            }

            $class = $route->controller;
            $method = $route->method;
            $controller = new $class;

            $this->cloneProperties($this, $controller);

            $controller->conn = $from;
            $controller->receiver = $this;
            $controller->request = $msg;
            $controller->route = $route;

            if (!method_exists($controller, $method)) {
                $this->error($msg, $from, 'Method doesnt\'t exist !');
            }

            $controller->$method();
        } catch (WebSocketException $exception) {

        } catch (ValidationException $exception) {
            $this->sendToWebSocketUser($from, [
                'message' => $exception->getMessage(),
                'errors' => $exception->errors()
            ]);
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        unset($this->clients[$conn->resourceId]);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $exception
     */
    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
        echo "An error has occurred: {$exception->getMessage()}\n";
        echo "In {$exception->getFile()} line {$exception->getLine()}\n";

        $conn->close();
        echo 'end';
        die;
    }

    /**
     * @param $msg
     * @param ConnectionInterface $from
     * @throws WebSocketException
     */
    function checkForRequiredInMessage($msg, $from)
    {
        if (!isset($msg->route) || !isset($msg->session)) {
            $this->error($msg, $from, 'You can\'t send a request without the route and the session id !');
        }

        if (!isset($this->routes[$msg->route])) {
            $this->error($msg, $from, 'No such route !');
        }
    }

    /**
     * @param Receiver $clonedObject
     * @param $clone
     */
    function cloneProperties($clonedObject, $clone)
    {
        foreach (get_object_vars($clonedObject) as $key => $value) {
            $clone->$key = $value;
        }
    }

    function mainRoutes()
    {
        WsRoute::make('initializeWebsocket', 'Shamaseen\Laravel\Ratchet\Controllers\InitializeController', 'index');
        WsRoute::make('room-enter', 'Shamaseen\Laravel\Ratchet\Controllers\RoomController', 'enterRoom');
        WsRoute::make('room-exit', 'Shamaseen\Laravel\Ratchet\Controllers\RoomController', 'exitRoom');
        WsRoute::make('send-to-user', 'Shamaseen\Laravel\Ratchet\Controllers\ChatController', 'sendMessageToUser');
        WsRoute::make('send-to-room', 'Shamaseen\Laravel\Ratchet\Controllers\ChatController', 'sendMessageToRoom');
    }
}