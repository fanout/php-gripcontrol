<?php

/*  response.php
    ~~~~~~~~~
    This module implements the Response class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

class Response
{
    public $code = null;
    public $reason = null;
    public $headers = null;
    public $body = null;

    public function __construct($code=null, $reason=null,
            $headers=null, $body=null)
    {
        $this->code = $code;
        $this->reason = $reason;
        $this->headers = $headers;
        $this->body = $body;
    }
}
?>
