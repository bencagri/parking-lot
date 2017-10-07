<?php

namespace tests\Models;

use ParkingLot\Models\ExitBarrierModel;
use PHPUnit_Framework_TestCase;

class ExitModelTest extends PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct();
    }


    public function test_open_barrier()
    {

        $barrier = new ExitBarrierModel();

        $barrier->open();

        $this->assertTrue($barrier->isOpened());

    }


    public function test_close_barrier()
    {

        $barrier = new ExitBarrierModel();

        $barrier->close();

        $this->assertFalse($barrier->isOpened());

    }

}
