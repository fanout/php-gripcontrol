<?php

/*  grippubcontrol.php
    ~~~~~~~~~
    This module implements the GripPubControl class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

class GripPubControl extends PubControl
{
    public function __construct($config=null)
    {
        $this->clients = array();
        $this->pcccbhandlers = array();
        if (!is_null($config))
            $this->apply_grip_config($config);
    }

    public function apply_grip_config($config)
    {
        if (!is_array(reset($config)))
            $config = array($config);
        foreach ($config as $entry)
        {
            if (!array_key_exists('control_uri', $entry))
                continue;    
            $pub = new PubControlClient($entry['control_uri']);
            if (array_key_exists('control_iss', $entry))
                $pub->set_auth_jwt(array('iss' => $entry['control_iss']), 
                        $entry['key']);
            $this->clients[] = $pub;
        }
    }

    public function publish_http_response($channel, $http_response,
            $id=null, $prev_id=null)
    {
        if (is_string($http_response))
            $http_response = new HttpResponseFormat(null, null, null,
                    $http_response);
        $item = new Item($http_response, $id, $prev_id);
        parent::publish($channel, $item);
    }

    public function publish_http_response_async($channel, $http_response,
            $id=null, $prev_id=null, $callback=null)
    {
        if (is_string($http_response))
            $http_response = new HttpResponseFormat(null, null, null,
                    $http_response);
        $item = new Item($http_response, $id, $prev_id);
        parent::publish_async($channel, $item, $callback);
    }

    public function publish_http_stream($channel, $http_stream,
            $id=null, $prev_id=null)
    {
        if (is_string($http_stream))
            $http_stream = new HttpStreamFormat($http_stream);
        $item = new Item($http_stream, $id, $prev_id);
        parent::publish($channel, $item);
    }

    public function publish_http_stream_async($channel, $http_stream,
            $id=null, $prev_id=null, $callback=null)
    {
        if (is_string($http_stream))
            $http_stream = new HttpStreamFormat($http_stream);
        $item = new Item($http_stream, $id, $prev_id);
        parent::publish_async($channel, $item, $callback);
    }
}
?>
