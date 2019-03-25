<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 24/03/19
 * Time: 07:43 م
 */

namespace Shamaseen\Laravel\Ratchet\Controllers;


use Shamaseen\Laravel\Ratchet\Objects\Clients\Client;
use Shamaseen\Laravel\Ratchet\Traits\WebSocketMessagesManager;

/**
 * Class WebSocketController
 * @package App\WebSockets\Controllers
 */
class WebSocketController
{
    use WebSocketMessagesManager;

    /**
     * @var Client[]
     */
    public $clients;
    private $routes;
    public $userAuthSocketMapper;
    public $conn;

    public function __construct()
    {

    }
}