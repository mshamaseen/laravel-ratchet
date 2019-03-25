<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 23/03/19
 * Time: 08:15 م
 */

namespace Shamaseen\Laravel\Ratchet\Routes;

/**
 * Class Routes
 * @package App\WebSockets\Routes
 */
class Routes
{
    public $routes = [

    ];

    function mainRoutes()
    {
        $this->make('initializeWebsocket','Shamaseen\Laravel\Ratchet\Controllers\InitializeController','index');
    }

    /**
     * @param $routeName
     * @param $controller
     * @param $method
     * @param bool $authenticated
     */
    function make($routeName, $controller, $method,$authenticated = true)
    {
        $this->routes[$routeName] = (object) [
            'controller'=>$controller,
            'method'=>$method,
            'auth'=> $authenticated
        ];
    }

    function getRoutes()
    {
        return $this->routes;
    }
}