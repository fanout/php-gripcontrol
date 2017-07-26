<?php

class HttpStreamFormatTests extends PHPUnit_Framework_TestCase
{
    public function testIntialize()
    {
        $hf = new GripControl\HttpStreamFormat('content');
        $this->assertEquals($hf->content, 'content');
        $this->assertEquals($hf->close, false);
        $hf = new GripControl\HttpStreamFormat('content', true);
        $this->assertEquals($hf->content, 'content');
        $this->assertEquals($hf->close, true);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testIntializeException()
    {
        $hf = new GripControl\HttpStreamFormat();
    }

    public function testName()
    {
        $hf = new GripControl\HttpStreamFormat('content');
        $this->assertEquals($hf->name(), 'http-stream');
    }

    public function testExport()
    {
        $hf = new GripControl\HttpStreamFormat('content');
        $this->assertEquals($hf->export(), array('content' => 'content'));
        $hf = new GripControl\HttpStreamFormat("\x04\x00\xa0\x00");
        $this->assertEquals($hf->export(), array('content-bin' => base64_encode("\x04\x00\xa0\x00")));
        $hf = new GripControl\HttpStreamFormat('content', true);
        $this->assertEquals($hf->export(), array('action' => 'close'));
    }
}
