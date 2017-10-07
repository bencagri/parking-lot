<?php

namespace ParkingLot\Services\ServiceHandlers;

use ParkingLot\Models\ExitBarrierModel;
use ParkingLot\Services\ParkingService;
use ParkingLot\Exceptions\NoExitException;

class ExitsHandler implements HandlerInterface
{
    /** @var ParkingService $parkingService */
    private $parkingService;

    /**
     * ExitsHandler constructor.
     * @param $parkingService
     */
    public function __construct(ParkingService&$parkingService)
    {
        $this->parkingService = $parkingService;
    }


    /**
     * @param array $parameters
     * @param $key
     * @return mixed
     * @throws NoExitException
     */
    public function handle(&$parameters, $key)
    {
        foreach ($parameters as $gate => $queue) {
            if ($queue[$key] === '1') {
                /** @var ExitBarrierModel $exit */
                $exit = $this->parkingService->getExitGate($gate);
                if ($exit->exitRequested()) { //departure allowed
                    $exit->exitDetected() || $this->parkingService->fail("no exit after departure");
                } else {
                    throw new NoExitException("Departure not approved.");
                }
            }
        }
    }
}