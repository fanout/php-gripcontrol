<?php

class HttpStreamFormatTests extends PHPUnit_Framework_TestCase
{
    public function testIntialize()
    {
        $hf = new HttpStreamFormat('content');
        $this->assertEquals($hf->content, 'content');
        $this->assertEquals($hf->close, false);
        $hf = new HttpStreamFormat('content', true);
        $this->assertEquals($hf->content, 'content');
        $this->assertEquals($hf->close, true);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testIntializeException()
    {
        $hf = new HttpStreamFormat();
    }

    public function testName()
    {
        $hf = new HttpStreamFormat('content');
        $this->assertEquals($hf->name(), 'http-stream');
    }

    public function testExport()
    {
        $hf = new HttpStreamFormat('content');
        $this->assertEquals($hf->export(), array('content' => 'content'));
        $hf = new HttpStreamFormat("\x04\x00\xa0\x00");
        $this->assertEquals($hf->export(), array('content-bin' =>
                base64_encode("\x04\x00\xa0\x00")));
        $hf = new HttpStreamFormat('content', true);
        $this->assertEquals($hf->export(), array('action' => 'close'));
    }
}

?>
