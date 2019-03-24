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
        $this->route('initializeWebsocket','App\WebSockets\Controllers\InitializeController','index');
    }


    function map()
    {

    }

    /**
     * @param $routeName
     * @param $controller
     * @param $method
     */
    function route($routeName, $controller, $method)
    {
        $this->routes[$routeName] = (object) [
            'controller'=>$controller,
            'method'=>$method
        ];
    }
}