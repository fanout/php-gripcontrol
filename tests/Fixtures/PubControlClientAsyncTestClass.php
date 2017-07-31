<?php

namespace GripControl\Test\Fixtures;

class PubControlClientAsyncTestClass
{
    public $publish_channel = null;
    public $publish_item = null;
    public $publish_cb = null;

    public function publish_async($channel, $item, $callback = null)
    {
        $this->publish_channel = $channel;
        $this->publish_item = $item;
        $this->publish_cb = $callback;
    }
}
