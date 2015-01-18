<?php

/*  channel.php
    ~~~~~~~~~
    This module implements the Channel class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

class Channel
{
    public $name = null;
    public $prev_id = null;

    public function __construct($name, $prev_id=null)
    {
        $this->name = $name;
        $this->prev_id = $prev_id;
    }
}
?>
