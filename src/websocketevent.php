<?php

/*  websocketevent.php
    ~~~~~~~~~
    This module implements the WebSocketEvent class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

namespace GripControl;

// The WebSocketEvent class represents WebSocket event information that is
// used with the GRIP WebSocket-over-HTTP protocol. It includes information
// about the type of event as well as an optional content field.
class WebSocketEvent
{
    public $type = null;
    public $content = null;

    // Initialize with a specified event type and optional content information.
    public function __construct($type, $content=null)
    {
        $this->type = $type;
        $this->content = $content;
    }
}
?>
