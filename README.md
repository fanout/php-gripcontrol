php-gripcontrol
================

Author: Konstantin Bokarius <kon@fanout.io>

A GRIP library for PHP.

License
-------

php-gripcontrol is offered under the MIT license. See the LICENSE file.

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

Usage
-----

Examples for how to publish HTTP response and HTTP stream messages to GRIP proxy endpoints via the GripPubControl class.

```PHP
<?php

require 'vendor/autoload.php';

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

```

Long polling example via response _headers_ using the WEBrick gem. The client connects to a GRIP proxy over HTTP and the proxy forwards the request to the origin. The origin subscribes the client to a channel and instructs it to long poll via the response _headers_. Note that with the recent versions of Apache it's not possible to send a 304 response containing custom headers, in which case the response body should be used instead (next usage example below).

```PHP

```

Long polling example via response _body_ using the WEBrick gem. The client connects to a GRIP proxy over HTTP and the proxy forwards the request to the origin. The origin subscribes the client to a channel and instructs it to long poll via the response _body_.

```PHP

```

WebSocket over HTTP example using the WEBrick gem. In this case, a client connects to a GRIP proxy via WebSockets and the GRIP proxy communicates with the origin via HTTP.

```PHP

```

Parse a GRIP URI to extract the URI, ISS, and key values. The values will be returned in a hash containing 'control_uri', 'control_iss', and 'key' keys.

```PHP
$config = GripControl::parse_grip_uri(
    'http://api.fanout.io/realm/<myrealm>?iss=<myrealm>' .
    '&key=base64:<myrealmkey>');
```
