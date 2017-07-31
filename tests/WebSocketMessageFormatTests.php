<?php

namespace GripControl\Test;

use GripControl;

class WebSocketMessageFormatTests extends \PHPUnit_Framework_TestCase
{
    public function testIntialize()
    {
        $wm = new GripControl\WebSocketMessageFormat('content');
        $this->assertEquals($wm->content, 'content');
    }

    public function testName()
    {
        $wm = new GripControl\WebSocketMessageFormat('content');
        $this->assertEquals($wm->name(), 'ws-message');
    }

    public function testExport()
    {
        $wm = new GripControl\WebSocketMessageFormat('content');
        $this->assertEquals($wm->export(), array('content' => 'content'));
        $wm = new GripControl\WebSocketMessageFormat("\x04\x00\xa0\x00");
        $this->assertEquals($wm->export(), array('content-bin' => base64_encode("\x04\x00\xa0\x00")));
    }
}
