<?php
namespace ParkingLot\Models;
use ParkingLot\Events\EventsInterface;


/**
 * Class ParkingSpot
 * @package ParkingLot\Models
 *
 *          The model of a parking spot
 */
class ParkingSpotModel extends AbstractModel
{
    /** @var bool  */
    protected $isTaken = false;

    /**
     * @return bool
     *
     * Signals park event
     */
    public function parkDetected() {
        $this->isTaken = true;
        return $this->announce(EventsInterface::MSG_PARK);
    }

    /**
     * @return bool
     *
     * Signals unpark event
     */
    public function unparkDetected() {
        $this->isTaken = false;
        return $this->announce(EventsInterface::MSG_UNPARK);
    }

    /**
     * @return bool
     *
     * the current status
     */
    public function isEmpty() {
        return !$this->isTaken;
    }

    public function init() {
        $this->isTaken = false;
    }

    public function fail($message = '')
    {
        parent::fail($message ?: 'Parking spot failure');
    }

    public function on($message)
    {
        return true;
    }
}