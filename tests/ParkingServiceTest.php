<?php

namespace tests;


use ParkingLot\Events\ParkingEvent;
use ParkingLot\Models\EntryBarrierModel;
use ParkingLot\Models\ExitBarrierModel;
use ParkingLot\Models\ParkingSpotModel;
use ParkingLot\ParkingSimulator;
use ParkingLot\Services\ParkingService;

class ParkingServiceTest extends \PHPUnit_Framework_TestCase
{

    /** @var  ParkingService $parkingService */
    private $parkingService;

    public function setUp()
    {
        $this->parkingService = ParkingService::getInstance();
    }


    public function test_parking_spots_if_can_find_available_place()
    {
        $parkingSpot = new ParkingSpotModel();
        $this->parkingService->addPlace($parkingSpot);

        //it should be instance of parking model
        $this->assertInstanceOf(ParkingSpotModel::class, $this->parkingService->getFreeSpot());
        $this->assertNull($this->parkingService->getTakenSpot(),NULL);

        //getFreeSpot should be null now
        $parkingSpot->parkDetected();
        $this->assertNull($this->parkingService->getFreeSpot(),NULL);
        $this->assertInstanceOf(ParkingSpotModel::class, $this->parkingService->getTakenSpot());
        $parkingSpot->unparkDetected();
    }

    public function test_if_can_add_entry_barriers()
    {
        $entry = new EntryBarrierModel();
        $this->parkingService->addEntry($entry);

        $this->parkingService->getEntryGate(0);
        $this->assertInstanceOf(EntryBarrierModel::class,$this->parkingService->getEntryGate(0));

        $this->parkingService->getEntryGate(1);
        $this->assertNull($this->parkingService->getEntryGate(1), NULL);
    }

    public function test_if_can_add_exit_barriers()
    {
        $entry = new ExitBarrierModel();
        $this->parkingService->addExit($entry);

        $this->parkingService->getExitGate(0);
        $this->assertInstanceOf(ExitBarrierModel::class,$this->parkingService->getExitGate(0));

        $this->parkingService->getExitGate(1);
        $this->assertNull($this->parkingService->getExitGate(1), NULL);
    }

    public function test_parking_places_occupancy_and_monitor_status()
    {
        $entry = new EntryBarrierModel();
        $spot1 = new ParkingSpotModel();
        $spot2 = new ParkingSpotModel();
        $exit  = new ExitBarrierModel();

        $this->parkingService->addEntry($entry);
        $this->parkingService->addPlace($spot1);
        $this->parkingService->addPlace($spot2);
        $this->parkingService->addExit($exit);

        //status total,parked,moving
        $status = $this->parkingService->getStatus();

        $this->assertEquals(0,$status['total']);
        $this->assertEquals(0,$status['parked']);
        $this->assertEquals(0,$status['moving']);

        //add entry
        $entry->entryDetected();
        $status = $this->parkingService->getStatus();
        $this->assertEquals(1,$status['total']);
        $this->assertEquals(0,$status['parked']);
        $this->assertEquals(1,$status['moving']);

        //add another entry
        $entry->entryDetected();
        $status = $this->parkingService->getStatus();
        $this->assertEquals(2,$status['total']);
        $this->assertEquals(0,$status['parked']);
        $this->assertEquals(2,$status['moving']);

        //set a park to spot 1
        $spot1->parkDetected();
        $status = $this->parkingService->getStatus();
        $this->assertEquals(2,$status['total']);
        $this->assertEquals(1,$status['parked']);
        $this->assertEquals(1,$status['moving']);

        //set a park to spot 2
        $spot2->parkDetected();
        $status = $this->parkingService->getStatus();
        $this->assertEquals(2,$status['total']);
        $this->assertEquals(2,$status['parked']);
        $this->assertEquals(0,$status['moving']);

        //set an unpark spot 1
        $spot1->unparkDetected();
        $status = $this->parkingService->getStatus();
        $this->assertEquals(2,$status['total']);
        $this->assertEquals(1,$status['parked']);
        $this->assertEquals(1,$status['moving']);

        //set an unpark spot 2
        $spot2->unparkDetected();
        $status = $this->parkingService->getStatus();
        $this->assertEquals(2,$status['total']);
        $this->assertEquals(0,$status['parked']);
        $this->assertEquals(2,$status['moving']);

        //make an exit
        $exit->exitDetected();
        $status = $this->parkingService->getStatus();
        $this->assertEquals(1,$status['total']);
        $this->assertEquals(0,$status['parked']);
        $this->assertEquals(1,$status['moving']);

        //make another exit
        $exit->exitDetected();
        $status = $this->parkingService->getStatus();
        $this->assertEquals(0,$status['total']);
        $this->assertEquals(0,$status['parked']);
        $this->assertEquals(0,$status['moving']);
    }

    public function test_it_will_not_allow_parking_if_its_full()
    {
        $this->parkingService->init();
        $entry = new EntryBarrierModel();
        $this->parkingService->addEntry($entry);
        $this->parkingService->addPlace(new ParkingSpotModel()); // we have one place

        $this->assertTrue($this->parkingService->on(ParkingEvent::MSG_ARRIVAL));

        //make an entry
        $entry->entryDetected();
        //now its full
        $this->assertFalse($this->parkingService->on(ParkingEvent::MSG_ARRIVAL));

    }
}
