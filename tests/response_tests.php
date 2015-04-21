<?php

class ResponseTests extends PHPUnit_Framework_TestCase
{
    public function testIntialize()
    {
        $re = new GripControl\Response();
        $this->assertEquals($re->code, null);
        $this->assertEquals($re->reason, null);
        $this->assertEquals($re->headers, null);
        $this->assertEquals($re->body, null);
        $re = new GripControl\Response('code', 'reason', 'headers', 'body');
        $this->assertEquals($re->code, 'code');
        $this->assertEquals($re->reason, 'reason');
        $this->assertEquals($re->headers, 'headers');
        $this->assertEquals($re->body, 'body');
    }
}

?>
