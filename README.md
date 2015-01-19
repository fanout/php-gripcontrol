ruby-gripcontrol
================

Author: Konstantin Bokarius <kon@fanout.io>

A GRIP library for PHP.

License
-------

php-gripcontrol is offered under the MIT license. See the LICENSE file.

Installation
------------

Usage
-----

Examples for how to publish HTTP response and HTTP stream messages to GRIP proxy endpoints via the GripPubControl class.

```PHP

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

WebSocket example using the WEBrick gem and WEBrick WebSocket gem extension. A client connects to a GRIP proxy via WebSockets and the proxy forward the request to the origin. The origin accepts the connection over a WebSocket and responds with a control message indicating that the client should be subscribed to a channel. Note that in order for the GRIP proxy to properly interpret the control messages, the origin must provide a 'grip' extension in the 'Sec-WebSocket-Extensions' header. This is accomplished in the WEBrick WebSocket gem extension by adding the following line to lib/webrick/websocket/server.rb and rebuilding the gem: res['Sec-WebSocket-Extensions'] = 'grip; message-prefix=""'

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
