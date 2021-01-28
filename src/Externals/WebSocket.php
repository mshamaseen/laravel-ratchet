<?php

namespace Shamaseen\Laravel\Ratchet\Externals;

use ZMQ;
use ZMQContext;

/**
 * Class WebSocket
 *
 * @package Shamaseen\Laravel\Ratchet\Externals
 */
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
        $socket->setSockOpt(ZMQ::SOCKOPT_RCVTIMEO,5000);
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
        //if you are already inside the websocket, send it using websocket instead of zmq
        if(isset($GLOBALS['__WS_Receiver']))
        {
            /**
             * @var Receiver $receiver
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
        //if you are already inside the websocket, send it using websocket instead of zmq
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


    /**
     * Call a method from a class, this function work like Routes but without request or authenticated user.
     * @param $namespace
     * @param $method
     * @param array $arg
     * @return mixed - return the result from the function executed
     * @throws \ZMQSocketException
     */
    static function call($namespace,$method,... $arg)
    {
        //if you are already inside the websocket, send it using websocket instead of zmq
        if(isset($GLOBALS['__WS_Receiver']))
        {
            /**
             * @var Receiver $GLOBALS['__WS_Receiver']
             */
            $receiver = $GLOBALS['__WS_Receiver'];
            return $receiver->callClassMethod($namespace,$method,$arg);
        }

        return json_decode(self::socket()->send(json_encode([
            'method' => 'callClassMethod',
            'args'=>[
                $namespace,$method,$arg
            ]
        ]))->recv());
    }
}
