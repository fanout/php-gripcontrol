<?php

/*  httpstreamformat.php
    ~~~~~~~~~
    This module implements the HttpStreamFormat class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

class HttpStreamFormat extends Format
{
    public $content = null;
    public $close = null;

    public function __construct($content=null, $close=false)
    {
        $this->content = $content;
        $this->close = $close;
        if (!$this->close && is_null($this->content))
            throw new RuntimeException('Content not set');
    }

    public function name()
    {
        return 'http-stream';
    }

    public function export()
    {
        $out = array();
        if ($this->close)
            $out['action'] = 'close';
        else
        {
            if (Encoding::is_binary_data($this->content))
                $out['content-bin'] = base64_encode($this->content);
            else
                $out['content'] = $this->content;
        }
        return $out;
    }
}
?>
