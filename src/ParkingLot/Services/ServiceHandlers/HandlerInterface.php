<?php

namespace ParkingLot\Services\ServiceHandlers;


interface HandlerInterface
{

    /**
     * @param $parameters
     * @param $key
     * @return mixed
     */
    public function handle(&$parameters, $key);
}