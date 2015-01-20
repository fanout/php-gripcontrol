php-gripcontrol
================

Author: Konstantin Bokarius <kon@fanout.io>

A GRIP library for PHP.

License
-------

php-gripcontrol is offered under the MIT license. See the LICENSE file.

Requirements
------------

* openssl
* curl
* pthreads (required for asynchronous publishing)
* firebase/php-jwt >=1.0.0 (retreived automatically via Composer)
* fanout/php-pubcontrol >=1.0.6 (retreived automatically via Composer)

Installation
------------

Using Composer: 'composer require fanout/gripcontrol' 

Manual: ensure that php-jwt and php-pubcontrol have been included and require the following files in php-gripcontrol:

```PHP
require 'php-gripcontrol/src/encoding.php';
require 'php-gripcontrol/src/channel.php';
require 'php-gripcontrol/src/response.php';
require 'php-gripcontrol/src/websocketevent.php';
require 'php-gripcontrol/src/websocketmessageformat.php';
require 'php-gripcontrol/src/httpstreamformat.php';
require 'php-gripcontrol/src/httpresponseformat.php';
require 'php-gripcontrol/src/grippubcontrol.php';
require 'php-gripcontrol/src/gripcontrol.php';
```

Asynchronous Publishing
-----------------------

In order to make asynchronous publish calls pthreads must be installed. If pthreads is not installed then only synchronous publish calls can be made. To install pthreads recompile PHP with the following flag: '--enable-maintainer-zts'

Also note that since a callback passed to the publish_async methods is going to be executed in a separate thread, that callback and the class it belongs to are subject to the rules and limitations imposed by the pthreads extension.

See more information about pthreads here: http://php.net/manual/en/book.pthreads.php

Usage
-----

Examples for how to publish HTTP response and HTTP stream messages to GRIP proxy endpoints via the GripPubControl class.

```PHP
<?php

function callback($result, $message)
{
    if ($result)
        Print "Publish successful\r\n";
    else
        Print "Publish failed with message: {$message}\r\n";
}

# GripPubControl can be initialized with or without an endpoint configuration.
# Each endpoint can include optional JWT authentication info.
# Multiple endpoints can be included in a single configuration.

$grippub = new GripPubControl(array(
        'control_uri' => 'https://api.fanout.io/realm/<myrealm>',
        'control_iss' => '<myrealm>',
        'key' => Base64.decode64('<myrealmkey>')));

# Add new endpoints by applying an endpoint configuration:
$grippub->apply_grip_config(array(
        array('control_uri' => '<myendpoint_uri_1>'), 
        array('control_uri' => '<myendpoint_uri_2>')));

# Remove all configured endpoints:
$grippub->remove_all_clients();

# Explicitly add an endpoint as a PubControlClient instance:
$pubclient = new PubControlClient('<my_endpoint_uri>');
# Optionally set JWT auth: $pubclient->set_auth_jwt('<claim>', '<key>')
# Optionally set basic auth: $pubclient->set_auth_basic('<user>', '<password>')
$grippub->add_client($pubclient);

# Publish across all configured endpoints:
$grippub->publish_http_response('<channel>', 'Test publish!');
$grippub->publish_http_response_async('<channel>', 'Test async publish!',
        null, null, 'callback');
$grippub->publish_http_stream('<channel>', 'Test publish!');
$grippub->publish_http_stream_async('<channel>', 'Test async publish!',
        null, null, 'callback');

# Wait for all async publish calls to complete:
$grippub->finish();
?>
```

Validate the Grip-Sig request header from incoming GRIP messages. This ensures that the message was sent from a valid source and is not expired. Note that when using Fanout.io the key is the realm key, and when using Pushpin the key is configurable in Pushpin's settings.

```PHP
<?php
$is_valid = GripControl::validate_sig(headers['Grip-Sig'], '<key>');
?>
```

Long polling example via response _headers_. The client connects to a GRIP proxy over HTTP and the proxy forwards the request to the origin. The origin subscribes the client to a channel and instructs it to long poll via the response _headers_. Note that with the recent versions of Apache it's not possible to send a 304 response containing custom headers, in which case the response body should be used instead (next usage example below).

```PHP
<?php
# Validate the Grip-Sig header:
$request_headers = getallheaders();
if (!GripControl::validate_sig($request_headers['Grip-Sig'], '<key>'))
    return;

# Instruct the client to long poll via the response headers:
http_response_code(200);
header('Grip-Hold: response');
header('Grip-Channel: ' . GripControl::create_grip_channel_header('<channel>'));
?>
```

Long polling example via response _body_. The client connects to a GRIP proxy over HTTP and the proxy forwards the request to the origin. The origin subscribes the client to a channel and instructs it to long poll via the response _body_.

```PHP
<?php
# Validate the Grip-Sig header:
$request_headers = getallheaders();
if (!GripControl::validate_sig($request_headers['Grip-Sig'], '<key>'))
    return;

# Instruct the client to long poll via the response body:
http_response_code(200);
header('Content-Type: application/grip-instruct');
echo GripControl::create_hold_response('<channel>');
?>
```

WebSocket over HTTP example. In this case, a client connects to a GRIP proxy via WebSockets and the GRIP proxy communicates with the origin via HTTP.

```PHP
<?php

class PublishMessage extends Thread
{
    public function run()
    {
        # Wait and then publish a message to the subscribed channel:
        sleep(5);
        $grippub = new GripPubControl(array('control_uri' => '<myendpoint>'));
        $grippub->publish('<channel>', new Item(
                new WebSocketMessageFormat('Test WebSocket publish!!')));
    }
}

# Validate the Grip-Sig header:
$request_headers = getallheaders();
if (!GripControl::validate_sig($request_headers['Grip-Sig'], '<key>'))
    return;

# Set the headers required by the GRIP proxy:
header('Content-Type: application/websocket-events');
header('Sec-WebSocket-Extensions: grip; message-prefix=""');
http_response_code(200);
$in_events = GripControl::decode_websocket_events(
        file_get_contents("php://input"));
if ($in_events[0]->type == 'OPEN')
{
    # Open the WebSocket and subscribe it to a channel:
    $out_events = array();
    $out_events[] = new WebSocketEvent('OPEN');
    $out_events[] = new WebSocketEvent('TEXT', 'c:' .
    GripControl::websocket_control_message('subscribe',
            array('channel' => '<channel>')));
    $response = GripControl::encode_websocket_events($out_events);
    ignore_user_abort(true);
    header("Connection: close");
    header("Content-Length: " . strlen($response));
    echo $response;
    ob_flush();
    flush();
    $publish_message = new PublishMessage();
    $publish_message->start();
}
?>
```

Parse a GRIP URI to extract the URI, ISS, and key values. The values will be returned in a hash containing 'control_uri', 'control_iss', and 'key' keys.

```PHP
<?php
$config = GripControl::parse_grip_uri(
    'http://api.fanout.io/realm/<myrealm>?iss=<myrealm>' .
    '&key=base64:<myrealmkey>');
?>
```
