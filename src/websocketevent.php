<?php

/*  websocketevent.php
    ~~~~~~~~~
    This module implements the WebSocketEvent class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

class WebSocketEvent
{
    public $type = null;
    public $content = null;

    public function __construct($type, $content=null)
    {
        $this->type = $type;
        $this->content = $content;
    }
}
?>
