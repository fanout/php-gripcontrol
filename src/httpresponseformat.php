<?php

/*  httpresponseformat.php
    ~~~~~~~~~
    This module implements the HttpResponseFormat class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

namespace GripControl;

// The HttpResponseFormat class is the format used to publish messages to
// HTTP response clients connected to a GRIP proxy.
class HttpResponseFormat extends \PubControl\Format
{
    public $code = null;
    public $reason = null;
    public $headers = null;
    public $body = null;

    // Initialize with the message code, reason, headers, and body to send
    // to the client when the message is published.
    public function __construct($code=null, $reason=null,
            $headers=null, $body=null)
    {
        $this->code = $code;
        $this->reason = $reason;
        $this->headers = $headers;
        $this->body = $body;
    }

    // The name used when publishing this format.
    public function name()
    {
        return 'http-response';
    }

    // Export the message into the required format and include only the fields
    // that are set. The body is exported as base64 if the text is encoded as
    // binary.
    public function export()
    {
        $out = array();
        if (!is_null($this->code))
            $out['code'] = $this->code;
        if (!is_null($this->reason))
            $out['reason'] = $this->reason;
        if (!is_null($this->headers))
            $out['headers'] = $this->headers;
        if (!is_null($this->body))
        {
            if (Encoding::is_binary_data($this->body))
                $out['body-bin'] = base64_encode($this->body);
            else
                $out['body'] = $this->body;
        }
        return $out;
    }
}
?>
