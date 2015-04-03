<?php

class HttpResponseFormatTests extends PHPUnit_Framework_TestCase
{
    public function testIntialize()
    {
        $hf = new HttpResponseFormat();
        $this->assertEquals($hf->code, null);
        $this->assertEquals($hf->reason, null);
        $this->assertEquals($hf->headers, null);
        $this->assertEquals($hf->body, null);
        $hf = new HttpResponseFormat('code', 'reason', 'headers', 'body');
        $this->assertEquals($hf->code, 'code');
        $this->assertEquals($hf->reason, 'reason');
        $this->assertEquals($hf->headers, 'headers');
        $this->assertEquals($hf->body, 'body');
    }

    public function testName()
    {
        $hf = new HttpResponseFormat();
        $this->assertEquals($hf->name(), 'http-response');
    }

    public function testExport()
    {
        $hf = new HttpResponseFormat('code', 'reason', 'headers', 'body');
        $this->assertEquals($hf->export(), array('code' => 'code',
                'reason' => 'reason', 'headers' => 'headers',
                'body' => 'body'));
        $hf = new HttpResponseFormat(null, null, null, "\x04\x00\xa0\x00");
        $this->assertEquals($hf->export(), array('body-bin' =>
                base64_encode("\x04\x00\xa0\x00")));
        $hf = new HttpResponseFormat();
        $this->assertEquals($hf->export(), array());
    }
}

?>
