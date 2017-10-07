<?php

namespace tests\Models;

use ParkingLot\Models\EntryBarrierModel;
use PHPUnit_Framework_TestCase;

class EntryModelTest extends PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        parent::__construct();
    }


    public function test_open_barrier()
    {

        $barrier = new EntryBarrierModel();

        $barrier->open();

        $this->assertTrue($barrier->isOpened());

    }


    public function test_close_barrier()
    {

        $barrier = new EntryBarrierModel();

        $barrier->close();

        $this->assertFalse($barrier->isOpened());

    }

}
