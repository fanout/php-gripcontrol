<?php

class WebSocketMessageFormatTests extends PHPUnit_Framework_TestCase
{
    public function testIntialize()
    {
        $wm = new WebSocketMessageFormat('content');
        $this->assertEquals($wm->content, 'content');
    }

    public function testName()
    {
        $wm = new WebSocketMessageFormat('content');
        $this->assertEquals($wm->name(), 'ws-message');
    }

    public function testExport()
    {
        $wm = new WebSocketMessageFormat('content');
        $this->assertEquals($wm->export(), array('content' => 'content'));
        $wm = new WebSocketMessageFormat("\x04\x00\xa0\x00");
        $this->assertEquals($wm->export(), array('content-bin' =>
                base64_encode("\x04\x00\xa0\x00")));
    }
}

?>
