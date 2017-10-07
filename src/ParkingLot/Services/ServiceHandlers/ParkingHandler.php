<?php

namespace ParkingLot\Services\ServiceHandlers;


use ParkingLot\Models\ParkingSpotModel;
use ParkingLot\Services\ParkingService;
use ParkingLot\Exceptions\ParkingFailedException;

class ParkingHandler implements HandlerInterface
{
    /** @var ParkingService $parkingService */
    private $parkingService;

    /**
     * ParkingHandler constructor.
     * @param ParkingService $parkingService
     */
    public function __construct(ParkingService&$parkingService)
    {
        $this->parkingService = $parkingService;
    }


    /**
     * @param array $parameters
     * @param $key
     * @return mixed
     * @throws ParkingFailedException
     */
    public function handle(&$parameters, $key)
    {
        /** @var ParkingSpotModel $spot */
        if ($parameters[$key] === '1') {
            $spot = $this->parkingService->getFreeSpot();
            $spot->parkDetected() || $this->parkingService->fail("park failed");

        } elseif ($parameters[$key] === '2') {
            $spot = $this->parkingService->getTakenSpot();
            $spot->unparkDetected() || $this->parkingService->fail("unpark failed");
        }
    }
}