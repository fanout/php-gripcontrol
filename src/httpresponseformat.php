<?php

/*  httpresponseformat.php
    ~~~~~~~~~
    This module implements the HttpResponseFormat class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

class HttpResponseFormat extends Format
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

    public function name()
    {
        return 'http-response';
    }

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
