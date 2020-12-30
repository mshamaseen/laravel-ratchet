<?php

namespace Shamaseen\Laravel\Ratchet\Commands;

use React\EventLoop\Factory;
use React\Socket\Server;
use React\ZMQ\Context;
use Shamaseen\Laravel\Ratchet\Receiver;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use ZMQ;


/**
 * Class ChatService
 * @package App\Console\Commands
 */
class WebSocketService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:websocket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'start websocket';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @throws
     */
    public function handle()
    {
        $loop   = Factory::create();
        $pusher = new Receiver;

        // Listen for the web server to make a ZeroMQ push after an ajax request
        $context = new Context($loop);
        $pull = $context->getSocket(ZMQ::SOCKET_REP,'my pusher');
//        $pull = $context->getSocket(ZMQ::SOCKET_PULL,'my pusher');
        $pull->bind('tcp://127.0.0.1:'.config('laravel-ratchet.ZMQ_PORT')); // Binding to 127.0.0.1 means the only client that can connect is itself
        $pull->on('message', array($pusher, 'externalRequest'));

        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new Server(config('laravel-ratchet.WEBSOCKET_URL').':'.config('laravel-ratchet.WEBSOCKET_PORT'), $loop); // Binding to 0.0.0.0 means remotes can connect
        new IoServer(
            new HttpServer(
                new WsServer(
                    $pusher
                )
            ),
            $webSock
        );

        $this->info('Websocket is now running at port '.config('laravel-ratchet.WEBSOCKET_PORT'));
        $loop->run();
    }
}
