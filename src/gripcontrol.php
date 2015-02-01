<?php

/*  gripcontrol.php
    ~~~~~~~~~
    This module implements the GripControl class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

class GripControl
{
    public static function create_hold($mode, $channels, $response,
            $timeout=null)
    {
        $hold = array();
        $hold['mode'] = $mode;
        if (is_a($channels, 'Channel'))
            $channels = array($channels);
        elseif (is_string($channels))
            $channels = array(new Channel($channels));
        if (count($channels) == 0)
            throw new RuntimeException('channels length is 0');
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
        $hold['channels'] = $ichannels;
        if (!is_null($timeout))
        {
            $hold['timeout'] = $timeout;
        }
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
        $instruct = array();
        $instruct['hold'] = $hold;
        if (!is_null($iresponse))
            $instruct['response'] = $iresponse;
        return json_encode($instruct);
    }

    public static function parse_grip_uri($uri)
    {
        $uri = parse_url($uri);
        $params = array();
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

    public static function create_grip_channel_header($channels)
    {
        if (is_a($channels, 'Channel'))
            $channels = array($channels);
        elseif (is_string($channels))
            $channels = array(new Channel($channels));
        if (count($channels) == 0)
            throw new RuntimeException('channels length is 0');
        $parts = array();
        foreach ($channels as $channel)
        {
            $s = $channel->name;
            if (!is_null($channel->prev_id))
                $s .= "; prev-id={$channel->prev_id}";
            $parts[] = $s;
        }
        return implode(',', $parts);
    }

    public static function create_hold_response($channels, $response=null,
            $timeout=null)
    {
        return self::create_hold('response', $channels, $response, $timeout);
    }

    public static function create_hold_stream($channels, $response=null)
    {
        return self::create_hold('stream', $channels, $response);
    }

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
                Print $at . ":::" . $clen . "\r\n";
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

    public static function websocket_control_message($type, $args=null)
    {
        $out = array();
        if (!is_null($args))
            $out = $args;
        $out['type'] = $type;
        return json_encode($out);
    }
}
?>
