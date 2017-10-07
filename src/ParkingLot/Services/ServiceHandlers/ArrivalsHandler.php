<?php

namespace ParkingLot\Services\ServiceHandlers;


use ParkingLot\Models\EntryBarrierModel;
use ParkingLot\Services\ParkingService;
use ParkingLot\Exceptions\ParkingIsFullException;

class ArrivalsHandler implements HandlerInterface
{

    /** @var ParkingService $parkingService */
    private $parkingService;

    /**
     * ArrivalsHandler constructor.
     * @param ParkingService $parkingService
     */
    public function __construct(ParkingService &$parkingService)
    {
        $this->parkingService = $parkingService;
    }

    /**
     * @param $parameters
     * @param $key
     * @return mixed|void
     * @throws ParkingIsFullException
     * @throws \Exception
     */
    public function handle(&$parameters,$key)
    {
        foreach ($parameters as $gate => $queue) {
            /** @var EntryBarrierModel $entry */
            if ($queue[$key] === '1') {
                $entry = $this->parkingService->getEntryGate($gate);
                if ($entry->entryRequested()) { //entry allowed
                    $entry->entryDetected() || $this->parkingService->fail(" no entry after arrival");
                } else {
                    throw new ParkingIsFullException("No entry! Sorry, the parking is full.");
                }
            }
        }

    }
}