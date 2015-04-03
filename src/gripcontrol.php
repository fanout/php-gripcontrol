<?php

/*  gripcontrol.php
    ~~~~~~~~~
    This module implements the GripControl class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

// The GripControl class provides functionality that is used in conjunction
// with GRIP proxies. This includes facilitating the creation of hold
// instructions for HTTP long-polling and HTTP streaming, parsing GRIP URIs
// into config objects, validating the GRIP-SIG header coming from GRIP
// proxies, creating GRIP channel headers, and also WebSocket-over-HTTP
// features such as encoding/decoding web socket events and generating
// control messages.
class GripControl
{

    // Create GRIP hold instructions for the specified mode, channels, response
    // and optional timeout value. The channel parameter can be specified as
    // either a string representing the channel name, a Channel instance or an
    // array of Channel instances. The response parameter can be specified as
    // either a string representing the response body or a Response instance.
    public static function create_hold($mode, $channels, $response,
            $timeout=null)
    {
        $channels = self::parse_channels($channels);
        $ichannels = self::get_hold_channels($channels);
        $hold = array();
        $hold['mode'] = $mode;
        $hold['channels'] = $ichannels;
        if (!is_null($timeout))
        {
            $hold['timeout'] = $timeout;
        }
        $iresponse = self::get_hold_response($response);
        $instruct = array();
        $instruct['hold'] = $hold;
        if (!is_null($iresponse))
            $instruct['response'] = $iresponse;
        return json_encode($instruct);
    }

    // Parse the specified GRIP URI into a config object that can then be passed
    // to the GripPubControl class. The URI can include 'iss' and 'key' JWT
    // authentication query parameters as well as any other required query string
    // parameters. The JWT 'key' query parameter can be provided as-is or in base64
    // encoded format.
    public static function parse_grip_uri($uri)
    {
        $uri = parse_url($uri);
        $params = array();
        if (array_key_exists('query', $uri))
            parse_str($uri['query'], $params);
        $iss = null;
        $key = null;
        if (array_key_exists('iss', $params))
        {
            $iss = $params['iss'];
            unset($params['iss']);
        }
        if (array_key_exists('key', $params))
        {
            $key = $params['key'];
            unset($params['key']);
        }
        if (!is_null($key) && substr($key, 0, strlen('base64:'))
                === 'base64:')
            $key = base64_decode(substr($key, 7));
        $qs = http_build_query($params);
        $path = $uri['path'];
        if (substr($path, strlen($path) - 1) === '/')
            $path = substr($path, 0, strlen($path) - 1);
        $port = '';
        if (array_key_exists('port', $uri) && $uri['port'] != '' &&
                $uri['port'] != '80')
            $port = ':' . $uri['port'];
        $control_uri = $uri['scheme'] . '://' . $uri['host'] . $port . $path;
        if (!is_null($qs) && $qs != '')
            $control_uri .= '?' . $qs;
        $out = array('control_uri' => $control_uri);
        if (!is_null($iss))
            $out['control_iss'] = $iss;
        if (!is_null($key))
            $out['key'] = $key;
        return $out;
    }

    // Validate the specified JWT token and key. This method is used to validate
    // the GRIP-SIG header coming from GRIP proxies such as Pushpin or Fanout.io.
    // Note that the token expiration is also verified.
    public static function validate_sig($token, $key)
    {
        try
        {
            JWT::decode($token, $key, true);
            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    // Create a GRIP channel header for the specified channels. The channels
    // parameter can be specified as a string representing the channel name,
    // a Channel instance, or an array of Channel instances. The returned GRIP
    // channel header is used when sending instructions to GRIP proxies via
    // HTTP headers.
    public static function create_grip_channel_header($channels)
    {
        $channels = self::parse_channels($channels);
        $parts = array();
        foreach ($channels as $channel)
        {
            $s = $channel->name;
            if (!is_null($channel->prev_id))
                $s .= "; prev-id={$channel->prev_id}";
            $parts[] = $s;
        }
        return implode(', ', $parts);
    }

    // A convenience method for creating GRIP hold response instructions for HTTP
    // long-polling. This method simply passes the specified parameters to the
    // create_hold method with 'response' as the hold mode.
    public static function create_hold_response($channels, $response=null,
            $timeout=null)
    {
        return self::create_hold('response', $channels, $response, $timeout);
    }

    // A convenience method for creating GRIP hold stream instructions for HTTP
    // streaming. This method simply passes the specified parameters to the
    // create_hold method with 'stream' as the hold mode.
    public static function create_hold_stream($channels, $response=null)
    {
        return self::create_hold('stream', $channels, $response);
    }

    // Decode the specified HTTP request body into an array of WebSocketEvent
    // instances when using the WebSocket-over-HTTP protocol. A RuntimeError
    // is raised if the format is invalid.
    public static function decode_websocket_events($body)
    {
        $out = array();
        $start = 0;
        while ($start < strlen($body))
        {
            $at = strpos($body, "\r\n", $start);
            if ($at === false)
                throw new RuntimeException('bad format');
            $typeline = substr($body, $start, $at - $start);
            $start = $at + 2;
            $at = strpos($typeline, ' ');
            $e = null;
            if (!($at === false))
            {
                $etype = substr($typeline, 0, $at);
                $clen = intval('0x' . substr($typeline, $at + 1), 16);
                $content = substr($body, $start, $clen);
                $start += $clen + 2;
                $e = new WebSocketEvent($etype, $content);
            }
            else
                $e = new WebSocketEvent($typeline);
            $out[] = $e;
        }
        return $out;
    }

    // Encode the specified array of WebSocketEvent instances. The returned string
    // value should then be passed to a GRIP proxy in the body of an HTTP response
    // when using the WebSocket-over-HTTP protocol.
    public static function encode_websocket_events($events)
    {
        $out = '';
        foreach ($events as $event)
        {
            if (!is_null($event->content))
            {
                $content_length = dechex(strlen($event->content));
                $out .= "{$event->type} {$content_length}\r\n" .
                        "{$event->content}\r\n";
            }
            else
                $out .= "{$event->type}\r\n";
        }
        return $out;
    }

    // Generate a WebSocket control message with the specified type and optional
    // arguments. WebSocket control messages are passed to GRIP proxies and
    // example usage includes subscribing/unsubscribing a WebSocket connection
    // to/from a channel.
    public static function websocket_control_message($type, $args=null)
    {
        $out = array();
        if (!is_null($args))
            $out = $args;
        $out['type'] = $type;
        return json_encode($out);
    }

    // An internal method used to parse the specified parameter into an array
    // of Channel instances. The specified parameter can either be a string, a
    // Channel instance, or an array of Channel instances.
    protected static function parse_channels($channels)
    {
        if (is_a($channels, 'Channel'))
            $channels = array($channels);
        elseif (is_string($channels))
            $channels = array(new Channel($channels));
        if (count($channels) == 0)
            throw new RuntimeException('channels length is 0');
        return $channels;
    }

    // An internal method for getting an array of hashes representing the
    // specified channels parameter. The resulting array is used for creating
    // GRIP proxy hold instructions.
    protected static function get_hold_channels($channels)
    {
        $ichannels = array();
        foreach ($channels as $channel)
        {
            if (is_string($channel))
                $channel = new Channel($channel);
            $ichannel = array();
            $ichannel['name'] = $channel->name;
            if (!is_null($channel->prev_id))
                $ichannel['prev-id'] = $channel->prev_id;
            $ichannels[] = $ichannel;
        }
        return $ichannels;
    }

    // An internal method for getting a hash representing the specified
    // response parameter. The resulting hash is used for creating GRIP
    // proxy hold instructions.
    protected static function get_hold_response($response)
    {
        $iresponse = null;
        if (!is_null($response))
        {
            if (is_string($response))
                $response = new Response(null, null, null, $response);
            $iresponse = array();
            if (!is_null($response->code))
                $iresponse['code'] = $response->code;
            if (!is_null($response->reason))
                $iresponse['reason'] = $response->reason;
            if (!is_null($response->headers) && count($response->headers) > 0)
                $iresponse['headers'] = $response->headers;
            if (!is_null($response->body))
            {
                if (Encoding::is_binary_data($response->body))
                    $iresponse['body-bin'] = base64_encode($response->body);
                else
                    $iresponse['body'] = $response->body;
            }
        }
        return $iresponse;
    }
}

?>
