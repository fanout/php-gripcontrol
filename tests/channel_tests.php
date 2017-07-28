<?php

class ChannelTests extends PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $ch = new GripControl\Channel('name');
        $this->assertEquals($ch->name, 'name');
        $this->assertEquals($ch->prev_id, null);
        $ch = new GripControl\Channel('name', 'prev-id');
        $this->assertEquals($ch->name, 'name');
        $this->assertEquals($ch->prev_id, 'prev-id');
    }
}
