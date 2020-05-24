[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mshamaseen/laravel-ratchet/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mshamaseen/laravel-ratchet/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/mshamaseen/laravel-ratchet/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mshamaseen/laravel-ratchet/build-status/master)


# Laravel Ratchet Websocket
Use Ratchet websocket the same way you use laravel !

this package will enable you to use:

1. **Auth** class \
    Use Auth facade, which grant you access to user object.
2. **Routes** \
    Use a custom routes for websocket.
3. **Validation** \
Use laravel validation to validate requests
4. **Controllers** \
Use controllers the way you use it !.

For **front-end**, you can use [laravel-ratchet-js](https://github.com/mshamaseen/laravel-ratchet-js).
# Installation

1. You need to install and enable `zmq` for php
```bash
sudo apt install php-zmq
```

2. Run the following command in the application path:
```bash
composer require shamaseen/laravel-ratchet
```

3. Publish websocket routes and configuration. \
run vendor publish command:
```bash
php artisan vendor:publish --tag=laravel-ratchet
```

4. In `config/app.php` and add the following to the `aliases` array:

```php
'WsRoute' => \Shamaseen\Laravel\Ratchet\Facades\WsRoute::class,
```

# Usage
To run the websocket, run the following command:

```bash
php artisan run:websocket
```
Note: The default listening port is 8080, If you want to listen on another port just add `WEBSOCKET_PORT={PORT_NUMBER}` to your `.env` file.

#### Routes
After publishing the package, you will find websocket file in routes path, here you can define routes for websocket.
i.e.
```php
WsRoute::make('initializeWebsocket', 'Shamaseen\Laravel\Ratchet\Controllers\InitializeController', 'index');
```

By default, the package will check for authentication in every request, if you don't want to check for authentication for a specific request you can set a false boolean value in the 4th parameter in `WsRoute::make` function.

#### Controllers
make whatever controller you wish, but extend it from `WebSocketController`.

You will find some properties in WebscoketController which you can use and it is self-explained by PHPDoc.

* request \
here you will find all the data which is sent by front-end  side.

* receiver \
One thing to note, any properties modification in WebsocketController will be only available for the current request, if you want to make a change in a specific property and then access it in any request and any user, you should use receiver property. \
i.e If you want to add a user to a room, you should add it in room property inside receiver property and not directly to rooms property in WebsocketController.

* conn \
Ratchet connection Interface

* error($request,ConnectionInterface $from, $error)
If you want to cut the request and stop the process just call error function.

* sendToUser($user_id,$data)
To send a message to a user

If you want to deal with rooms, you can use RoomUtility trait in your controller to have access to rooms manipulation pre-defined functions. 

#### Validation
Use `$this->validate()` the same way you use it with laravel !

# Close Connection callback
Some time you need to run a method when a user close his connection, i.e when the user close his browser, close the website tab, or even get out from the current page.
To do so you can assign an `OnClose` route to be called by calling `onClose` function from inside your you controller:

``
$this->getClient()->onClose('your route here');
``

then your route will be triggered whenever the user close the connection.


# Accessing the websocket from Laravel files (out of WebSocket controllers)

### Sending data to a user 
If you want to send data to a user from outside of Websocket controller (i.e from Laravel controller or laravel Job), just use Websocket static functions, like this:
```php
\Shamaseen\Laravel\Ratchet\Externals\WebSocket::sendToUser($user_id,array $data))
```

Easy as it is.

### Check if a user is online
```php
\Shamaseen\Laravel\Ratchet\Externals\WebSocket::isOnline($user_id)
```

You can also use these static methods in ``WebSocket`` class from anywhere!
 
How easy :)

Note: The default listening port for ZMQ is 5555, If you want to listen on another port just add `ZMQ_PORT={PORT_NUMBER}` to your `.env` file.

# Server configuration
If you want to connect the websocket to a HTTPS website,
 you should use `wss` instead of `ws` when making an instance from Shama class, you can add a path for the url but you need to add these lines to nginx host configuration (suppose your url path is /wss2):
```
map $http_upgrade $connection_upgrade {
        default upgrade;
        ''      close;
    }

location /wss2 {
 
         # prevents 502 bad gateway error
         proxy_buffers 8 32k;
         proxy_buffer_size 64k;
 
         # redirect all HTTP traffic to localhost:9090;
         proxy_pass http://0.0.0.0:9090;
         proxy_set_header X-Real-IP $remote_addr;
         proxy_set_header Host $http_host;
         proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
 
         # enables WS support
         proxy_http_version 1.1;
         proxy_set_header Upgrade $http_upgrade;
         proxy_set_header Connection $connection_upgrade;
 
        proxy_read_timeout 999999999;
 
 }
```
