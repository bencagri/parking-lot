<?php

namespace tests;

use ParkingLot\ParkingSimulator;
use PHPUnit\Framework\TestCase;

class SimulatorTest extends TestCase
{

    function test_simulate_with_default_parameters(){

        $parameters['places'] = 10;
        $parameters['entries'] = 2;
        $parameters['arrivals'][0] = '100111000';
        $parameters['arrivals'][1] = '011111000';
        $parameters['parkings'] = '001011201';
        $parameters['exit_que'][0] = '000001111';
        $parameters['exits'] = 1;

        $simulator = new ParkingSimulator();

        $simulator->setDefaultParameters($parameters);

        $expected = "T:123578765\nP:001123223\nR:122455542";

        $results = $simulator->simulate();

        $this->assertEquals($expected,$results);
    }

}
