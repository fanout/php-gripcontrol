<?php

/*  gripcontrol.php
    ~~~~~~~~~
    This module implements the GripControl class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

class GripControl
{
    public static create_hold($mode, $channels, $response)
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
}
?>
