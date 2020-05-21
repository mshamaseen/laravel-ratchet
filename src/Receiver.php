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
use Shamaseen\Laravel\Ratchet\Requests\WsRequest;
use Shamaseen\Laravel\Ratchet\Traits\WebSocketMessagesManager;
use ZMQ;
use ZMQContext;

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
//
//    function externalRequest($data)
//    {
//        $context = new ZMQContext();
//        $socket = $context->getSocket(ZMQ::SOCKET_REP, 'my pusher');
//        $socket->connect("tcp://localhost:".env('ZMQ_PORT',5555));
//        $socket->send('true');
//
////        $context = new ZMQContext();
////        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
////        $socket->connect("tcp://localhost:5555");
////        $socket->send('this is a data');
//
////        $this->sendBack(["received"]);
//    }

    /**
     * @description this function is used to manipulate receiver instance from external php script using zmq
     * @param string $data
     * @throws WebSocketException
     * @throws \ZMQSocketException
     */
    public function externalRequest($data)
    {
        $data = json_decode($data,true);

        $method = $data['method'];

        if(method_exists($this,$method))
        {
            $result = call_user_func_array(array($this, $method),$data['args']);
            if($result !== null)
            {
                $context = new ZMQContext();
                $socket = $context->getSocket(ZMQ::SOCKET_REP,'my pusher');
                $socket->connect("tcp://localhost:".env('ZMQ_PORT',5555));
                $socket->send($result ? 1 : 0);
            }
        }
        else{
            throw new WebSocketException();
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients[$conn->resourceId] = new Client();
        $this->clients[$conn->resourceId]->conn = $conn;
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $msg = json_decode($msg,true);
        $this->callRoute($from,$msg);
    }

    function callRoute(ConnectionInterface $from, $msg)
    {
        try {
            $this->checkForRequiredInMessage($msg, $from);

            $this->resetSession($msg['session']);

            $this->resetAuth($msg,$from);

            $route = $this->routes[$msg['route']];

            $class = $route->controller;
            $method = $route->method;
            $controller = new $class;

            $this->cloneProperties($this, $controller);

            $controller->conn = $from;
            $controller->receiver = $this;

            $controller->request = new WsRequest($msg);
            $controller->route = $route;

            if (!method_exists($controller, $method)) {
                $this->error($msg, $from, 'Method '.$method.' doesnt\'t exist !');
            }

            $controller->$method();

            \Session::save();
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
        $client = $this->clients[$conn->resourceId];
        $routesToCall = $client->onCloseRoutes;
        foreach ($routesToCall as $route)
        {
            $msg = ['session'=>$client->session,'route'=>$route];
            $this->callRoute($conn,$msg);
        }

        unset($this->clients[$conn->resourceId]);
        unset($this->userAuthSocketMapper[array_search($conn->resourceId,$this->userAuthSocketMapper)]);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $exception
     * @return null
     */
    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
        echo "An error has occurred: {$exception->getMessage()}\n";
        echo "In {$exception->getFile()} line {$exception->getLine()}\n";
        echo $exception->getTraceAsString();

        $conn->close();
        return null;
    }

    /**
     * @param $msg
     * @param ConnectionInterface $from
     * @throws WebSocketException
     */
    function checkForRequiredInMessage($msg, $from)
    {
        if (!isset($msg['route']) || !isset($msg['session'])) {
            $this->error($msg, $from, 'Either the route is missing in the Request, Or you forget to add the session id ! please refer to the document in github for more details');
        }

        if (!isset($this->routes[$msg['route']])) {
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

    /**
     * Read the session data from the handler.
     *
     * @param $session_id
     * @return array
     */
    protected function readFromHandler($session_id)
    {
        if ($data = \Session::getHandler()->read($session_id)) {
            $data = @unserialize($data);

            if ($data !== false && ! is_null($data) && is_array($data)) {
                return $data;
            }
        }

        return [];
    }

    function getUserId($session_array)
    {
        return $session_array['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'];
    }

    function getUserModel()
    {
        return \Config::get('laravel-ratchet.userModelNamespace','App\Entities\Users\User');
    }

    function resetSession($session_id)
    {
        \Session::flush();

        \Session::setId($session_id);

        \Session::start();
    }

    /**
     * @param object $msg
     * @param ConnectionInterface $from
     * @throws WebSocketException
     */
    function resetAuth($msg,$from)
    {
        $data = $this->readFromHandler($msg['session']);

        if(!empty($data))
        {
            $user_id = $this->getUserId($data);
            $user = $this->getUserModel()::find($user_id);
            if(!$user)
            {
                $this->error($msg, $from, 'There is no such user.');
            }
            \Auth::setUser($user);

            $this->clients[$from->resourceId]->id = \Auth::id();
            $this->clients[$from->resourceId]->session = $msg['session'];
            $this->userAuthSocketMapper[\Auth::id()] = $from->resourceId;
        }
        else
        {
            $this->error($msg, $from, 'Unauthenticated.');
        }
    }
}
