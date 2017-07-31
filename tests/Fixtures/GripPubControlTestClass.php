<?php

namespace GripControl\Test\Fixtures;

use GripControl;

class GripPubControlTestClass extends GripControl\GripPubControl
{
    public function getClients()
    {
        return $this->clients;
    }

    public function getPcccbHandlers()
    {
        return $this->pcccbhandlers;
    }
}
