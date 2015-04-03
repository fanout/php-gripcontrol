<?php

class WebSocketEventTests extends PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $we = new WebSocketEvent('type');
        $this->assertEquals($we->type, 'type');
        $this->assertEquals($we->content, null);
        $we = new WebSocketEvent('type', 'content');
        $this->assertEquals($we->type, 'type');
        $this->assertEquals($we->content, 'content');
    }
}

?>
