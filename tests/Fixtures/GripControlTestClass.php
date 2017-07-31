<?php

namespace GripControl\Test\Fixtures;

use GripControl;

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
