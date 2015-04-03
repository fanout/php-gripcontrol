<?php

class EncodingTests extends PHPUnit_Framework_TestCase
{
    public function testIsBinaryData()
    {
        $this->assertFalse(Encoding::is_binary_data('text'));
        $this->assertTrue(Encoding::is_binary_data("\x04\x00\xa0\x00"));
    }
}

?>
