<?php

/*  httpstreamformat.php
    ~~~~~~~~~
    This module implements the HttpStreamFormat class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

namespace GripControl;

// The HttpStreamFormat class is the format used to publish messages to
// HTTP stream clients connected to a GRIP proxy.
class HttpStreamFormat extends \PubControl\Format
{
    public $content = null;
    public $close = null;

    // Initialize with either the message content or a boolean indicating that
    // the streaming connection should be closed. If neither the content nor
    // the boolean flag is set then an error will be raised.
    public function __construct($content=null, $close=false)
    {
        $this->content = $content;
        $this->close = $close;
        if (!$this->close && is_null($this->content))
            throw new \RuntimeException('Content not set');
    }

    // The name used when publishing this format.
    public function name()
    {
        return 'http-stream';
    }

    // Exports the message in the required format depending on whether the
    // message content is binary or not, or whether the connection should
    // be closed.
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
