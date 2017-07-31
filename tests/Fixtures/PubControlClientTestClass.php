<?php

namespace GripControl\Test\Fixtures;

class PubControlClientTestClass
{
    public $was_finish_called = false;
    public $was_publish_called = false;
    public $publish_channel = false;
    public $publish_item = false;

    public function finish()
    {
        $this->was_finish_called = true;
    }

    public function publish($channel, $item)
    {
        $this->was_publish_called = true;
        $this->publish_channel = $channel;
        $this->publish_item = $item;
    }
}
