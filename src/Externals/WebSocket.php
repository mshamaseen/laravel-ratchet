<?php

namespace Shamaseen\Laravel\Ratchet\Externals;

use Shamaseen\Laravel\Ratchet\Receiver;
use ZMQ;
use ZMQContext;

class WebSocket
{

    /**
     * @return \ZMQSocket
     * @throws \ZMQSocketException
     */
    private static function socket()
    {
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_REQ, 'my pusher');
        $socket->setSockOpt(ZMQ::SOCKOPT_RCVTIMEO,1000);
        $socket->connect("tcp://localhost:".config('laravel-ratchet.ZMQ_PORT'));
        return  $socket;
    }

    /**
     * @param $user_id
     * @param $data
     * @return bool
     * @throws \ZMQSocketException
     */
    static function sendToUser($user_id, $data)
    {
        if(isset($GLOBALS['__WS_Receiver']))
        {
            /**
             * @var Receiver $GLOBALS['__WS_Receiver']
             */
            $receiver = $GLOBALS['__WS_Receiver'];
            return $receiver->sendToUser($user_id,$data);
        }

        return !!self::socket()->send(json_encode([
            'method' => 'sendToUser',
            'args'=>[
                $user_id,$data
            ]
        ]))->recv();
    }

    /**
     *
     * @param $user_id
     * @return string
     * @throws \ZMQSocketException
     */
    static function isOnline($user_id)
    {
        if(isset($GLOBALS['__WS_Receiver']))
        {
            /**
             * @var Receiver $GLOBALS['__WS_Receiver']
             */
            $receiver = $GLOBALS['__WS_Receiver'];
            return $receiver->isOnline($user_id);
        }

        return !!self::socket()->send(json_encode([
            'method' => 'isOnline',
            'args'=>[
                $user_id
            ]
        ]))->recv();
    }
}
