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
     * @param $request
     * @param ConnectionInterface $from
     * @param $error
     * @throws WebSocketException
     */
    function error($request,ConnectionInterface $from, $error)
    {
        if(config('app.debug'))
        {
            echo 'User error: ';
            echo $error."\n";
            print_r($request);
            echo " ============================================================== \n \n \n";
        }

        $data = [
            'event'=>'error',
            'message'=>$error
        ];
        $this->sendToWebSocketUser($from,$data);
        die;
    }

    /**
     * @param ConnectionInterface $conn
     * @param $data
     */
    function sendToWebSocketUser(ConnectionInterface $conn,$data)
    {
        if(!is_array($data))
            $data = ['msg'=>$data,'event'=>'default'];

        if(!isset($data['event']))
        {
            $data['event'] = 'default';
        }

        if(isset($this->route) && !array_key_exists('sender',$data))
            $data['sender'] = $this->getSenderData();

        $conn->send(json_encode($data));
    }

    /**
     * @param int $user_id
     * @param array $data
     * @return bool
     */
    function sendToUser($user_id,$data)
    {
        if(!$this->isOnline($user_id))
        {
            return false;
        }

        $resourceId = $this->userAuthSocketMapper[$user_id];
        /** @var ConnectionInterface $conn */
        $conn = $this->clients[$resourceId]->conn;
        $this->sendToWebSocketUser($conn,$data);

        return true;
    }

    /**
     * @param $data
     */
    function sendBack($data)
    {
       $this->sendToWebSocketUser($this->conn,$data);
    }

    /**
     *
     * @return array
     */
    function getSenderData()
    {
        return [
            'id' => \Auth::id(),
            'name'=> \Auth::user()->name
        ];
    }

    function isOnline($user_id)
    {
        if(array_key_exists($user_id,$this->userAuthSocketMapper))
        {
            return true;
        }

        return false;
    }

}
