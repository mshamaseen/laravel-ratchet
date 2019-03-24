<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 23/03/19
 * Time: 10:15 م
 */

namespace Shamaseen\Laravel\Ratchet\Traits;


use Shamaseen\Laravel\Ratchet\Exceptions\WebSocketException;
use Ratchet\ConnectionInterface;

/**
 * Trait WebSocketMessagesManager
 * @package App\WebSockets
 */
trait WebSocketMessagesManager
{
    /**
     * @param $msg
     * @param $from
     * @param $error
     * @throws WebSocketException
     */
    function error($msg,ConnectionInterface $from, $error)
    {
        echo 'Error: ';
        echo $error."\n";
        print_r($msg);
        $data = [
            'type'=>'error',
            'message'=>$error
        ];
        $this->sendToWebSocketUser($from,$data);
        throw new WebSocketException();
    }

    /**
     * @param ConnectionInterface $conn
     * @param $data
     */
    function sendToWebSocketUser(ConnectionInterface $conn,$data)
    {
        $conn->send(json_encode($data));
    }

    /**
     * @param int $user_id
     * @param array $data
     */
    function sendToUser($user_id,$data)
    {
        $resourceId = $this->userAuthSocketMapper[$user_id];
        /** @var ConnectionInterface $conn */
        $conn = $this->clients[$resourceId];
        $conn->send(json_encode($data));
    }

    /**
     * @param $data
     */
    function sendBack($data)
    {
       $this->sendToUser(\Auth::id(),$data);
    }

}