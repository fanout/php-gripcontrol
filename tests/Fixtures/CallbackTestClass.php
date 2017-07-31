<?php

namespace GripControl\Test\Fixtures;

class CallbackTestClass extends \Stackable
{
    public $was_callback_called = false;
    public $result = null;
    public $message = null;

    public function callback($result, $message)
    {
        $this->result = $result;
        $this->message = $message;
        $this->was_callback_called = true;
    }

    public function run()
    {
    }
}
