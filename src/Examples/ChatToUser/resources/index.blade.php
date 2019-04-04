<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 04/04/19
 * Time: 12:47 م
 */
?>


<html>
<head>
    <script
            src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous"></script>

    <script src="/packages/laravel-ratchet-js/src/index.min.js"></script>
</head>
<body style="text-align: center">
<p style="white-space: pre">
    This example will send a message to an authenticated user in laravel auth.

    Just copy paste this file into your resources route, and make a route to it so that you can access it.
    and run <strong>php artisan websocket:run </strong> in you termenal (be sure to be in laravel project path once you run it)
    Don't forget to update the script path, you maybe using another path than mine.

    if something wrong happened, please check the javascript console.
</p>
<div>
    <div>
        <label>user id (should be already authenticated and in the same page):</label>
        <input id="user_id" name="user_id" type="number" value="">
    </div>
    <div>
        <label>Message:</label>
        <textarea name="message" id="message_input">
        </textarea>
    </div>

    <div>
        <button onclick="send()">Send</button>
    </div>

    <ul id="chat-box">

    </ul>
</div>

<input type="hidden" value="{{Session::getId()}}" id="session">
<script>
    var shama = new Shama('ws://localhost:8080');
    shama.session = $('#session').val();
    shama.addListeners('receivedMessage',receivedChat);
    function send()
    {
        let userId = $("#user_id").val();
        let messageInput = $("#message_input");
        console.log({
            route:'send-to-user',
            user_id:userId,
            message:messageInput.val(),
        });
        shama.send({
            route:'send-to-user',
            user_id:userId,
            message:messageInput.val(),
        });
        receivedChat({
            route:'send-to-user',
            user_id:userId,
            message:messageInput.val(),
        });
        messageInput.val('');
    }

    function receivedChat(data) {
        let html = '<li>'+data.message+'</li>';
        $("#chat-box").append(html);
    }
</script>
</body>
</html>
