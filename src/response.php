<?php

/*  response.php
    ~~~~~~~~~
    This module implements the Response class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

// The Response class is used to represent a set of HTTP response data.
// Populated instances of this class are serialized to JSON and passed
// to the GRIP proxy in the body. The GRIP proxy then parses the message
// and deserialized the JSON into an HTTP response that is passed back 
// to the client.
class Response
{
    public $code = null;
    public $reason = null;
    public $headers = null;
    public $body = null;

    // Initialize with an HTTP response code, reason, headers, and body.
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
