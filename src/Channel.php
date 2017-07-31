<?php

/*  Channel.php
    ~~~~~~~~~
    This module implements the Channel class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

namespace GripControl;

// The Channel class is used to represent a channel in for a GRIP proxy and
// tracks the previous ID of the last message.
class Channel
{
    public $name = null;
    public $prev_id = null;

    // Initialize with the channel name and an optional previous ID.
    public function __construct($name, $prev_id = null)
    {
        $this->name = $name;
        $this->prev_id = $prev_id;
    }
}
