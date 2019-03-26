<?php
/**
 * Created by PhpStorm.
 * User: hammo
 * Date: 11/13/2018
 * Time: 11:22 AM
 */

namespace Shamaseen\Laravel\Ratchet\Objects\Clients;


use Ratchet\ConnectionInterface;

/**
 * this class represent ratchet client
 * Class Client
 * @package App\WebSockets\Clients
 */
class Client
{
    /**
     * this id is the laravel auth id
     * @var null $id
     */
    public $id = null;
    public $name = null;
    public $hash = null;

    /** @var ConnectionInterface $conn */
    public $conn = null;

    /**
     * @var array
     */
    public $rooms = [];

}
