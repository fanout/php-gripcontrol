<?php

namespace GripControl\Test\Fixtures;

use GripControl;

class GripPubControlTestClassNoAsync extends GripControl\GripPubControl
{
    public function is_async_supported()
    {
        return false;
    }
}
