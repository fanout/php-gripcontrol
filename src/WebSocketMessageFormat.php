<?php

/*  WebSocketMessageFormat.php
    ~~~~~~~~~
    This module implements the WebSocketMessageFormat class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

namespace GripControl;

// The WebSocketMessageFormat class is the format used to publish data to
// WebSocket clients connected to GRIP proxies.
class WebSocketMessageFormat extends \PubControl\Format
{
    public $content = null;

    // Initialize with the message content and a flag indicating whether the
    // message content should be sent as base64-encoded binary data.
    public function __construct($content)
    {
        $this->content = $content;
    }

    // The name used when publishing this format.
    public function name()
    {
        return 'ws-message';
    }

    // Exports the message in the required format depending on whether the
    // message content is binary or not.
    public function export()
    {
        $out = array();
        if (Encoding::is_binary_data($this->content)) {
            $out['content-bin'] = base64_encode($this->content);
        } else {
            $out['content'] = $this->content;
        }
        return $out;
    }
}
