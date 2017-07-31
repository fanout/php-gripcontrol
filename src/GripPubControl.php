<?php

/*  GripPubControl.php
    ~~~~~~~~~
    This module implements the GripPubControl class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

namespace GripControl;

// The GripPubControl class allows consumers to easily publish HTTP response
// and HTTP stream format messages to GRIP proxies. Configuring GripPubControl
// is slightly different from configuring PubControl in that the 'uri' and
// 'iss' keys in each config entry should have a 'control_' prefix.
// GripPubControl inherits from PubControl and therefore also provides all
// of the same functionality.
class GripPubControl extends \PubControl\PubControl
{
    // Initialize with or without a configuration. A configuration can be applied
    // after initialization via the apply_grip_config method.
    public function __construct($config = null)
    {
        $this->clients = array();
        $this->pcccbhandlers = array();
        if (!is_null($config)) {
            $this->apply_grip_config($config);
        }
    }

    // Apply the specified configuration to this GripPubControl instance. The
    // configuration object can either be a hash or an array of hashes where
    // each hash corresponds to a single PubControlClient instance. Each hash
    // will be parsed and a PubControlClient will be created either using just
    // a URI or a URI and JWT authentication information.
    public function apply_grip_config($config)
    {
        if (!is_array(reset($config))) {
            $config = array($config);
        }
        foreach ($config as $entry) {
            if (!array_key_exists('control_uri', $entry)) {
                continue;
            }
            $pub = new \PubControl\PubControlClient($entry['control_uri']);
            if (array_key_exists('control_iss', $entry)) {
                $pub->set_auth_jwt(array('iss' => $entry['control_iss']), $entry['key']);
            }
            $this->clients[] = $pub;
        }
    }

    // Synchronously publish an HTTP response format message to all of the
    // configured PubControlClients with a specified channel, message, and
    // optional ID and previous ID. Note that the 'http_response' parameter can
    // be provided as either an HttpResponseFormat instance or a string (in which
    // case an HttpResponseFormat instance will automatically be created and
    // have the 'body' field set to the specified string).
    public function publish_http_response($channel, $http_response, $id = null, $prev_id = null)
    {
        if (is_string($http_response)) {
            $http_response = new HttpResponseFormat(null, null, null, $http_response);
        }
        $item = new \PubControl\Item($http_response, $id, $prev_id);
        parent::publish($channel, $item);
    }

    // Asynchronously publish an HTTP response format message to all of the
    // configured PubControlClients with a specified channel, message, and
    // optional ID, previous ID, and callback. Note that the 'http_response'
    // parameter can be provided as either an HttpResponseFormat instance or
    // a string (in which case an HttpResponseFormat instance will automatically
    // be created and have the 'body' field set to the specified string). When
    // specified, the callback method will be called after publishing is complete
    // and passed a result and error message (if an error was encountered).
    public function publish_http_response_async($channel, $http_response, $id = null, $prev_id = null, $callback = null)
    {
        if (is_string($http_response)) {
            $http_response = new HttpResponseFormat(null, null, null, $http_response);
        }
        $item = new \PubControl\Item($http_response, $id, $prev_id);
        parent::publish_async($channel, $item, $callback);
    }

    // Synchronously publish an HTTP stream format message to all of the
    // configured PubControlClients with a specified channel, message, and
    // optional ID and previous ID. Note that the 'http_stream' parameter can
    // be provided as either an HttpStreamFormat instance or a string (in which
    // case an HttStreamFormat instance will automatically be created and
    // have the 'content' field set to the specified string).
    public function publish_http_stream($channel, $http_stream, $id = null, $prev_id = null)
    {
        if (is_string($http_stream)) {
            $http_stream = new HttpStreamFormat($http_stream);
        }
        $item = new \PubControl\Item($http_stream, $id, $prev_id);
        parent::publish($channel, $item);
    }

    // Asynchronously publish an HTTP stream format message to all of the
    // configured PubControlClients with a specified channel, message, and
    // optional ID, previous ID, and callback. Note that the 'http_stream'
    // parameter can be provided as either an HttpStreamFormat instance or
    // a string (in which case an HttpStreamFormat instance will automatically
    // be created and have the 'content' field set to the specified string). When
    // specified, the callback method will be called after publishing is complete
    // and passed a result and error message (if an error was encountered).
    public function publish_http_stream_async($channel, $http_stream, $id = null, $prev_id = null, $callback = null)
    {
        if (is_string($http_stream)) {
            $http_stream = new HttpStreamFormat($http_stream);
        }
        $item = new \PubControl\Item($http_stream, $id, $prev_id);
        parent::publish_async($channel, $item, $callback);
    }
}
