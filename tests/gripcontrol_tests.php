<?php

class GripControlTestClass extends GripControl\GripControl
{
    public static function callParseChannels($channels)
    {
        return self::parse_channels($channels);
    }

    public static function callGetHoldChannels($channels)
    {
        return self::get_hold_channels($channels);
    }

    public static function callGetHoldResponse($response)
    {
        return self::get_hold_response($response);
    }
}

class GripControlTests extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testCreateHoldException()
    {
        GripControl\GripControl::create_hold('mode', array(), 'response');
    }

    public function testCreateHold()
    {
        $hold = json_decode(GripControl\GripControl::create_hold('mode', 'channel',
                new GripControl\Response('code', 'reason', 'headers', 'body')));
        $this->assertEquals($hold->hold->mode, 'mode');
        $this->assertEquals($hold->hold->channels[0]->name,
                'channel');
        $this->assertFalse(array_key_exists('timeout', $hold->hold));
        $this->assertEquals($hold->response->code, 'code');
        $this->assertEquals($hold->response->reason, 'reason');
        $this->assertEquals($hold->response->headers, 'headers');
        $this->assertEquals($hold->response->body, 'body');
        $hold = json_decode(GripControl\GripControl::create_hold('mode', 'channel',
                'body', 'timeout'));
        $this->assertEquals($hold->hold->mode, 'mode');
        $this->assertEquals($hold->hold->timeout, 'timeout');
        $this->assertEquals($hold->hold->channels[0]->name,
                'channel');
        $this->assertFalse(array_key_exists('code', $hold->response));
        $this->assertFalse(array_key_exists('reason', $hold->response));
        $this->assertFalse(array_key_exists('headers', $hold->response));
        $this->assertEquals($hold->response->body, 'body');
    }

    public function testParseGripUri()
    {  
        $uri = 'http://api.fanout.io/realm/realm?iss=realm' .
                '&key=base64:geag121321=';
        $config = GripControl\GripControl::parse_grip_uri($uri);
        $this->assertEquals($config['control_uri'],
                'http://api.fanout.io/realm/realm');
        $this->assertEquals($config['control_iss'], 'realm');
        $this->assertEquals($config['key'], base64_decode('geag121321='));
        $uri = 'https://api.fanout.io/realm/realm?iss=realm' .
                '&key=base64:geag121321=';
        $config = GripControl\GripControl::parse_grip_uri($uri);
        $this->assertEquals($config['control_uri'],
                'https://api.fanout.io/realm/realm');
        $config = GripControl\GripControl::parse_grip_uri(
                'http://api.fanout.io/realm/realm');
        $this->assertEquals($config['control_uri'],
                'http://api.fanout.io/realm/realm');
        $this->assertEquals(array_key_exists('control_iss', $config), false);
        $this->assertEquals(array_key_exists('key', $config), false);
        $uri = 'http://api.fanout.io/realm/realm?iss=realm' .
                '&key=base64:geag121321=&param1=value1&param2=value2';
        $config = GripControl\GripControl::parse_grip_uri($uri);
        $this->assertEquals($config['control_uri'],
                'http://api.fanout.io/realm/realm?' .
                'param1=value1&param2=value2');
        $this->assertEquals($config['control_iss'], 'realm');
        $this->assertEquals($config['key'], base64_decode('geag121321='));
        $config = GripControl\GripControl::parse_grip_uri(
                'http://api.fanout.io:8080/realm/realm/');
        $this->assertEquals($config['control_uri'],
                'http://api.fanout.io:8080/realm/realm');
        $uri = 'http://api.fanout.io/realm/realm?iss=realm' .
                '&key=geag121321=';
        $config = GripControl\GripControl::parse_grip_uri($uri);
        $this->assertEquals($config['key'], 'geag121321=');
    }

    public function testValidateSig()
    {
        $token = JWT::encode(array('iss' => 'realm', 'exp' => time() + 3600),
                'key');
        assert(GripControl\GripControl::validate_sig($token, 'key'));
        $token = JWT::encode(array('iss' => 'realm', 'exp' => time() - 3600),
                'key');
        $this->assertEquals(GripControl\GripControl::validate_sig($token, 'key'), false);
        $token = JWT::encode(array('iss' => 'realm', 'exp' => time() + 3600),
                'key');
        $this->assertEquals(GripControl\GripControl::validate_sig($token, 'wrong_key'), false);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateGripChannelHeaderException()
    {
        GripControl\GripControl::create_grip_channel_header(array());
    }

    public function testCreateGripChannelHeader()
    {
        $header = GripControl\GripControl::create_grip_channel_header('channel');
        $this->assertEquals($header, 'channel');
        $header = GripControl\GripControl::create_grip_channel_header(new GripControl\Channel('channel'));
        $this->assertEquals($header, 'channel');
        $header = GripControl\GripControl::create_grip_channel_header(new GripControl\Channel('channel',
                'prev-id'));
        $this->assertEquals($header, 'channel; prev-id=prev-id');
        $header = GripControl\GripControl::create_grip_channel_header(array(
                new GripControl\Channel('channel1',
                'prev-id1'), new GripControl\Channel('channel2', 'prev-id2')));
        $this->assertEquals($header,
                'channel1; prev-id=prev-id1, channel2; prev-id=prev-id2');
    }

    public function testCreateHoldResponse()
    {
        $hold = json_decode(GripControl\GripControl::create_hold_response('channel',
                new GripControl\Response('code', 'reason', 'headers', 'body')), true);
        $this->assertEquals($hold['hold']['mode'], 'response');
        $this->assertEquals(array_key_exists('timeout', $hold['hold']), false);
        $this->assertEquals($hold['hold']['channels'],
                array(array('name' => 'channel')));
        $this->assertEquals($hold['response'], array('code' => 'code',
                'reason' => 'reason', 'headers' => 'headers', 'body' => 'body'));
        $hold = json_decode(GripControl\GripControl::create_hold_response('channel', null,
                'timeout'), true);
        $this->assertFalse(array_key_exists('response', $hold));
        $this->assertEquals($hold['hold']['mode'], 'response');
        $this->assertEquals($hold['hold']['timeout'], 'timeout');
    }

    public function testCreateHoldStream()
    {
        $hold = json_decode(GripControl\GripControl::create_hold_stream('channel', new GripControl\Response(
                'code', 'reason', 'headers', 'body')), true);
        $this->assertEquals($hold['hold']['mode'], 'stream');
        $this->assertEquals(array_key_exists('timeout', $hold['hold']), false);
        $this->assertEquals($hold['hold']['channels'], array(array(
                'name' => 'channel')));
        $this->assertEquals($hold['response'], array('code' => 'code',
                'reason' => 'reason', 'headers' => 'headers', 'body' => 'body'));
        $hold = json_decode(GripControl\GripControl::create_hold_stream('channel', null), true);
        $this->assertFalse(array_key_exists('response', $hold));
        $this->assertEquals($hold['hold']['mode'], 'stream');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testDecodeWebSocketEventsException1()
    {
        GripControl\GripControl::decode_websocket_events("TEXT 5");
    }

    /**
     * @expectedException RuntimeException
     */
    public function testDecodeWebSocketEventsException2()
    {
        GripControl\GripControl::decode_websocket_events("OPEN\r\nTEXT");
    }

    public function testDecodeWebSocketEvents()
    {
        $events = GripControl\GripControl::decode_websocket_events("OPEN\r\nTEXT 5\r\nHello" . 
                "\r\nTEXT 0\r\n\r\nCLOSE\r\nTEXT\r\nCLOSE\r\n");
        $this->assertEquals(count($events), 6);
        $this->assertEquals($events[0]->type, 'OPEN');
        $this->assertEquals($events[0]->content, null);
        $this->assertEquals($events[1]->type, 'TEXT');
        $this->assertEquals($events[1]->content, 'Hello');
        $this->assertEquals($events[2]->type, 'TEXT');
        $this->assertEquals($events[2]->content, '');
        $this->assertEquals($events[3]->type, 'CLOSE');
        $this->assertEquals($events[3]->content, null);
        $this->assertEquals($events[4]->type, 'TEXT');
        $this->assertEquals($events[4]->content, null);
        $this->assertEquals($events[5]->type, 'CLOSE');
        $this->assertEquals($events[5]->content, null);
        $events = GripControl\GripControl::decode_websocket_events("OPEN\r\n");
        $this->assertEquals(count($events), 1);
        $this->assertEquals($events[0]->type, 'OPEN');
        $this->assertEquals($events[0]->content, null);
        $events = GripControl\GripControl::decode_websocket_events("TEXT 5\r\nHello\r\n");
        $this->assertEquals(count($events), 1);
        $this->assertEquals($events[0]->type, 'TEXT');
        $this->assertEquals($events[0]->content, 'Hello');
    }

    public function testEncodeWebSocketEvents()
    {  
        $events = GripControl\GripControl::encode_websocket_events(array(
                new GripControl\WebSocketEvent("TEXT", "Hello"), 
                new GripControl\WebSocketEvent("TEXT", ""),
                new GripControl\WebSocketEvent("TEXT", null)));
        $this->assertEquals($events, "TEXT 5\r\nHello\r\nTEXT 0\r\n\r\nTEXT\r\n");
        $events = GripControl\GripControl::encode_websocket_events(array(
                new GripControl\WebSocketEvent("OPEN")));
        $this->assertEquals($events, "OPEN\r\n");
    }

    public function testWebSocketControlMessage()
    {
        $message = GripControl\GripControl::websocket_control_message('type');
        $this->assertEquals($message, '{"type":"type"}');
        $message = json_decode(GripControl\GripControl::websocket_control_message(
                'type', array('arg1' => 'val1',
                'arg2' => 'val2')), true);
        $this->assertEquals($message['type'], 'type');
        $this->assertEquals($message['arg1'], 'val1');
        $this->assertEquals($message['arg2'], 'val2');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testParseChannelsException()
    {
        GripControlTestClass::callParsechannels(array());
    }

    public function testParseChannels()
    {
        $channels = GripControlTestClass::callParsechannels('channel');
        $this->assertEquals($channels[0]->name, 'channel');
        $this->assertEquals($channels[0]->prev_id, null);
        $channels = GripControlTestClass::callParsechannels(new GripControl\Channel('channel'));
        $this->assertEquals($channels[0]->name, 'channel');
        $this->assertEquals($channels[0]->prev_id, null);
        $channels = GripControlTestClass::callParsechannels(
                new GripControl\Channel('channel', 'prev-id'));
        $this->assertEquals($channels[0]->name, 'channel');
        $this->assertEquals($channels[0]->prev_id, 'prev-id');
        $channels = GripControlTestClass::callParsechannels(array(
                new GripControl\Channel('channel1', 'prev-id'),
                new GripControl\Channel('channel2')));
        $this->assertEquals($channels[0]->name, 'channel1');
        $this->assertEquals($channels[0]->prev_id, 'prev-id');
        $this->assertEquals($channels[1]->name, 'channel2');
        $this->assertEquals($channels[1]->prev_id, null);
    }

    public function testGetHoldChannels()
    {
        $hold_channels = GripControlTestClass::callGetHoldChannels(
                array(new GripControl\Channel('channel')));
        $this->assertEquals($hold_channels[0], array('name' => 'channel'));
        $hold_channels = GripControlTestClass::callGetHoldChannels(array(
                new GripControl\Channel('channel', 'prev-id')));
        $this->assertEquals($hold_channels[0], array('name' => 'channel', 'prev-id' =>
                'prev-id'));
        $hold_channels = GripControlTestClass::callGetHoldChannels(array(
                new GripControl\Channel('channel1', 'prev-id1'),
                new GripControl\Channel('channel2', 'prev-id2')));
        $this->assertEquals($hold_channels[0], array('name' => 'channel1', 'prev-id' =>
                'prev-id1'));
        $this->assertEquals($hold_channels[1], array('name' => 'channel2', 'prev-id' =>
                'prev-id2'));
    }

    public function testGetHoldResponse()
    {
        $response = GripControlTestClass::callGetHoldResponse(null);
        $this->assertEquals($response, null);
        $response = GripControlTestClass::callGetHoldResponse('body');
        $this->assertEquals($response['body'], 'body');
        $this->assertEquals(array_key_exists('code', $response), false);
        $this->assertEquals(array_key_exists('reason', $response), false);
        $this->assertEquals(array_key_exists('headers', $response), false);
        // Verify non-UTF8 data passed as the body is exported as content-bin
        $response = GripControlTestClass::callGetHoldResponse("\x04\x00\xa0\x00");
        $this->assertEquals($response['body-bin'], base64_encode("\x04\x00\xa0\x00"));
        $response = GripControlTestClass::callGetHoldResponse(new GripControl\Response('code', 'reason',
                array('header1' => 'val1'), "body\u2713"));
        $this->assertEquals($response['code'], 'code');
        $this->assertEquals($response['reason'], 'reason');
        $this->assertEquals($response['headers'], array('header1' => 'val1'));
        $this->assertEquals($response['body'], "body\u2713");
        $response = GripControlTestClass::callGetHoldResponse(new GripControl\Response(
                null, null, array(), null));
        $this->assertEquals(array_key_exists('headers', $response), false);
        $this->assertEquals(array_key_exists('body', $response), false);
        $this->assertEquals(array_key_exists('reason', $response), false);
        $this->assertEquals(array_key_exists('headers', $response), false);
    }
}

?>
