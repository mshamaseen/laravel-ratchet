<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 23/03/19
 * Time: 10:57 م
 */

namespace Shamaseen\Laravel\Ratchet\Exceptions;


use Exception;

/**
 * Class WebSocketException
 * @package App\Exceptions
 */
class WebSocketException extends Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return null;
    }
}