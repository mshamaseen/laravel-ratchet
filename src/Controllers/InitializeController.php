<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 24/03/19
 * Time: 04:21 م
 */

namespace Shamaseen\Laravel\Ratchet\Controllers;


use Shamaseen\Laravel\Ratchet\Controllers\WebSocketController;

/**
 * Class InitializeController
 * @package App\WebSockets\Controllers
 */
class InitializeController extends WebSocketController
{

    function index()
    {
        echo 'Initializing ..';
        $this->sendBack(['message'=>'Initialized']);
    }
}