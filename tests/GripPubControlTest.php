<?php

namespace GripControl\Test;

use GripControl;
use GripControl\Test\Fixtures\GripPubControlTestClass;
use GripControl\Test\Fixtures\PubControlClientTestClass;
use GripControl\Test\Fixtures\PubControlClientAsyncTestClass;
use GripControl\Test\Fixtures\GripPubControlTestClassNoAsync;
use GripControl\Test\Fixtures\CallbackTestClass;
use PubControl;

class GripPubControlTest extends \PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $pc = new GripPubControlTestClass();
        $this->assertEquals(count($pc->getClients()), 0);
        $this->assertEquals(count($pc->getPcccbHandlers()), 0);
        $pc = new GripPubControlTestClass(array(
            array(
                'control_uri' => 'uri',
                'control_iss' => 'iss',
                'key' => 'key=='
            ),
            array(
                'control_uri' => 'uri2',
                'control_iss' => 'iss2',
                'key' => 'key==2'
            )
        ));
        $this->assertEquals(count($pc->getClients()), 2);
        $this->assertEquals(count($pc->getPcccbHandlers()), 0);
        $this->assertEquals($pc->getClients()[0]->uri, 'uri');
        $this->assertEquals($pc->getClients()[0]->auth_jwt_claim, array('iss' => 'iss'));
        $this->assertEquals($pc->getClients()[0]->auth_jwt_key, 'key==');
        $this->assertEquals($pc->getClients()[1]->uri, 'uri2');
        $this->assertEquals($pc->getClients()[1]->auth_jwt_claim, array('iss' => 'iss2'));
        $this->assertEquals($pc->getClients()[1]->auth_jwt_key, 'key==2');
    }

    public function testGripApplyConfig()
    {
        $pc = new GripPubControlTestClass();
        $pc->apply_grip_config(array(
            array(
                'control_uri' => 'uri',
                'control_iss' => 'iss',
                'key' => 'key=='
            ),
            array(
                'control_uri' => 'uri2',
                'control_iss' => 'iss2',
                'key' => 'key==2'
            )
        ));
        $this->assertEquals(count($pc->getClients()), 2);
        $this->assertEquals($pc->getClients()[0]->uri, 'uri');
        $this->assertEquals($pc->getClients()[0]->auth_jwt_claim, array('iss' => 'iss'));
        $this->assertEquals($pc->getClients()[0]->auth_jwt_key, 'key==');
        $this->assertEquals($pc->getClients()[1]->uri, 'uri2');
        $this->assertEquals($pc->getClients()[1]->auth_jwt_claim, array('iss' => 'iss2'));
        $this->assertEquals($pc->getClients()[1]->auth_jwt_key, 'key==2');
        $pc->apply_grip_config(array(
            'control_uri' => 'uri3',
            'control_iss' => 'iss3',
            'key' => 'key==3'
        ));
        $this->assertEquals(count($pc->getClients()), 3);
        $this->assertEquals($pc->getClients()[2]->uri, 'uri3');
        $this->assertEquals($pc->getClients()[2]->auth_jwt_claim, array('iss' => 'iss3'));
        $this->assertEquals($pc->getClients()[2]->auth_jwt_key, 'key==3');
    }

    public function testPublishHttpResponse1()
    {
        $pc = new GripControl\GripPubControl();
        $pcc1 = new PubControlClientTestClass();
        $pcc2 = new PubControlClientTestClass();
        $pc->add_client($pcc1);
        $pc->add_client($pcc2);
        $pc->publish_http_response('chan', 'data');
        $this->assertTrue($pcc1->was_publish_called);
        $this->assertEquals($pcc1->publish_channel, 'chan');
        $this->assertEquals(
            $pcc1->publish_item->export(),
            (new PubControl\Item(new GripControl\HttpResponseFormat(
                null,
                null,
                null,
                'data'
            )))->export()
        );
    }

    public function testPublishHttpResponse2()
    {
        $pc = new GripControl\GripPubControl();
        $pcc1 = new PubControlClientTestClass();
        $pcc2 = new PubControlClientTestClass();
        $pc->add_client($pcc1);
        $pc->add_client($pcc2);
        $response = new GripControl\HttpResponseFormat('code', 'reason', 'headers', 'data');
        $pc->publish_http_response('chan', $response, 'id', 'prev-id');
        $this->assertTrue($pcc1->was_publish_called);
        $this->assertEquals($pcc1->publish_channel, 'chan');
        $this->assertEquals($pcc1->publish_item->export(), (new PubControl\Item($response, 'id', 'prev-id'))->export());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testPublishAsyncException()
    {
        $pc = new GripPubControlTestClassNoAsync();
        $pc->publish_async('chan', 'item', 'callback');
    }

    public function testPublishHttpResponsehAsync()
    {
        $pc = new GripControl\GripPubControl();
        $callback = new CallbackTestClass();
        $pcc1 = new PubControlClientAsyncTestClass('uri');
        $pcc2 = new PubControlClientAsyncTestClass('uri');
        $pcc3 = new PubControlClientAsyncTestClass('uri');
        $pc->add_client($pcc1);
        $pc->add_client($pcc2);
        $pc->add_client($pcc3);
        $pc->publish_http_response_async('chan', 'item', null, null, array($callback, "callback"));
        $this->assertEquals($pcc1->publish_channel, 'chan');
        $this->assertEquals(
            $pcc1->publish_item->export(),
            (new PubControl\Item(new GripControl\HttpResponseFormat(
                null,
                null,
                null,
                'item'
            )))->export()
        );
        $this->assertEquals($pcc2->publish_channel, 'chan');
        $this->assertEquals(
            $pcc2->publish_item->export(),
            (new PubControl\Item(new GripControl\HttpResponseFormat(
                null,
                null,
                null,
                'item'
            )))->export()
        );
        $this->assertEquals($pcc3->publish_channel, 'chan');
        $this->assertEquals(
            $pcc3->publish_item->export(),
            (new PubControl\Item(new GripControl\HttpResponseFormat(
                null,
                null,
                null,
                'item'
            )))->export()
        );
        call_user_func($pcc1->publish_cb, false, 'message');
        call_user_func($pcc2->publish_cb, false, 'message');
        $this->assertTrue(is_null($callback->result));
        call_user_func($pcc3->publish_cb, false, 'message');
        $this->assertFalse($callback->result);
        $this->assertEquals($callback->message, 'message');
    }

    public function testPublishHttpStream1()
    {
        $pc = new GripControl\GripPubControl();
        $pcc1 = new PubControlClientTestClass();
        $pcc2 = new PubControlClientTestClass();
        $pc->add_client($pcc1);
        $pc->add_client($pcc2);
        $pc->publish_http_stream('chan', 'content');
        $this->assertTrue($pcc1->was_publish_called);
        $this->assertEquals($pcc1->publish_channel, 'chan');
        $this->assertEquals(
            $pcc1->publish_item->export(),
            (new PubControl\Item(new GripControl\HttpStreamFormat('content')))->export()
        );
    }

    public function testPublishHttpStream2()
    {
        $pc = new GripControl\GripPubControl();
        $pcc1 = new PubControlClientTestClass();
        $pcc2 = new PubControlClientTestClass();
        $pc->add_client($pcc1);
        $pc->add_client($pcc2);
        $stream = new GripControl\HttpStreamFormat('content', true);
        $pc->publish_http_stream('chan', $stream, 'id', 'prev-id');
        $this->assertTrue($pcc1->was_publish_called);
        $this->assertEquals($pcc1->publish_channel, 'chan');
        $this->assertEquals($pcc1->publish_item->export(), (new PubControl\Item($stream, 'id', 'prev-id'))->export());
    }

    public function testPublishHttpStreamhAsync()
    {
        $pc = new GripControl\GripPubControl();
        $callback = new CallbackTestClass();
        $pcc1 = new PubControlClientAsyncTestClass('uri');
        $pcc2 = new PubControlClientAsyncTestClass('uri');
        $pcc3 = new PubControlClientAsyncTestClass('uri');
        $pc->add_client($pcc1);
        $pc->add_client($pcc2);
        $pc->add_client($pcc3);
        $pc->publish_http_stream_async('chan', 'item', null, null, array($callback, "callback"));
        $this->assertEquals($pcc1->publish_channel, 'chan');
        $this->assertEquals(
            $pcc1->publish_item->export(),
            (new PubControl\Item(new GripControl\HttpStreamFormat('item')))->export()
        );
        $this->assertEquals($pcc2->publish_channel, 'chan');
        $this->assertEquals(
            $pcc2->publish_item->export(),
            (new PubControl\Item(new GripControl\HttpStreamFormat('item')))->export()
        );
        $this->assertEquals($pcc3->publish_channel, 'chan');
        $this->assertEquals(
            $pcc3->publish_item->export(),
            (new PubControl\Item(new GripControl\HttpStreamFormat('item')))->export()
        );
        call_user_func($pcc1->publish_cb, false, 'message');
        call_user_func($pcc2->publish_cb, false, 'message');
        $this->assertTrue(is_null($callback->result));
        call_user_func($pcc3->publish_cb, false, 'message');
        $this->assertFalse($callback->result);
        $this->assertEquals($callback->message, 'message');
    }
}
