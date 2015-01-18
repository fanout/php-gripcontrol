<?php

/*  websocketmessageformat.php
    ~~~~~~~~~
    This module implements the WebSocketMessageFormat class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

class WebSocketMessageFormat extends Format
{
    public $content = null;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function name()
    {
        return 'ws-message';
    }

    public function export()
    {        
        $out = array();
        if (Encoding::is_binary_data($this->content))
            $out['content-bin'] = base64_encode($this->content);
        else
            $out['content'] = $this->content;
        return $out;
    }
}
?>
